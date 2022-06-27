<?php
/**
 * @package Mac_AsyncLogger
 * @author Russell Albin <russell@russellalbin.com>
 */

declare(strict_types=1);

namespace Mac\AsyncLogger\Model\Queue\Handler;

use Magento\Framework\Logger\Monolog;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Handler class for logging the message
 */
class Handler
{
    /** @var Monolog  */
    protected Monolog $logger;

    /** @var Json  */
    protected Json $jsonSerializer;

    /**
     * @param Monolog $logger
     * @param Json $jsonSerializer
     */
    public function __construct(
        Monolog $logger,
        Json $jsonSerializer
    ) {
        $this->logger = $logger;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Grabs data from message queue and processes it
     *
     * @param string $data
     * @return void
     */
    public function execute($data)
    {
        $data = $this->jsonSerializer->unserialize($data);
        $message = $data['message'] ?? '';
        $level = $data['level'] ?? '';
        $context = $data['context'] ?? [];

        if (!empty($message) && !empty($level) && !empty($context)) {
            $this->logger->$level($message, $context);
        } else {
            $this->logger->debug(__('Some required data was missing from message queue payload'));
        }
    }
}
