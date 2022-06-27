<?php
/**
 * @package Mac_AsynLogger
 * @author Russell Albin <russell@russellalbin.com>
 */
declare(strict_types=1);

namespace Mac\AsyncLogger\Plugin;

use Mac\AsyncLogger\Model\AsyncLogger;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Webapi\Rest\Response;
use Magento\Webapi\Controller\Rest;

/**
 * Class used to log information before and after a REST request.
 * Also helps with logging start and end times for the request
 */
class RestLog
{
   /**
    * AsyncLogger variable
    *
    * @var AsyncLogger
    */
    protected AsyncLogger $asyncLogger;
    /**
     * TimezoneInterface variable
     *
     * @var TimezoneInterface
     */
    protected TimezoneInterface $date;
    /**
     * Json variable
     *
     * @var Json
     */
    protected Json $json;

    /**
     * Start time variable
     *
     * @var string
     */
    protected $startTime;

    /**
     * Constructor function
     *
     * @param AsyncLogger $asyncLogger
     * @param TimezoneInterface $date
     * @param Json $json
     */
    public function __construct(
        AsyncLogger $asyncLogger,
        TimezoneInterface $date,
        Json $json
    ) {
        $this->asyncLogger = $asyncLogger;
        $this->date = $date;
        $this->json = $json;
    }

    /**
     * Plugin dispatch before function
     *
     * @param Rest $subject
     * @param RequestInterface $request
     * @return array
     */
    public function beforeDispatch(Rest $subject, RequestInterface $request): array
    {
        if ($this->asyncLogger->isDebuggingActive()) {
            $this->startTime = microtime(true);
            $message['requestTime'] = $this->date->date()->format('Y-m-d H:i:s');
            $message['method'] = $request->getMethod();
            $message['requestUri'] = $request->getRequestUri();
            $message['params'] = $request->getParams();
            if (!empty($request->getContent())) {
                $message['requestBody'] = $this->json->unserialize($request->getContent());
            }
            $headerArray = $request->getHeaders()->toArray();
            if (!empty($headerArray) && is_array($headerArray)) {
                foreach ($request->getHeaders()->toArray() as $key => $value) {
                    $message['headers'][$key] = $value;
                }
            }
            $this->asyncLogger->debug($message);
        }
        return [$request];
    }

    /**
     * Plugin sendResponse after function
     *
     * @param Response $subject
     * @return void
     */
    public function afterSendResponse(Response $subject): void
    {
        if ($this->asyncLogger->isDebuggingActive()) {
            $endTime = microtime(true);
            $execTime = ($endTime - $this->startTime);
            $message['responseTime'] = $this->date->date()->format('Y-m-d H:i:s');
            $message['statusCode'] = $subject->getStatusCode();
            if (!empty($subject->getBody())) {
                $message['responseBody'] = $this->json->unserialize($subject->getBody());
            }
            $message['executionTime'] = $execTime;
            $this->asyncLogger->debug($message);
        }
    }
}
