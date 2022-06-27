<?php
/**
 * @package Mac_AsyncLogger
 * @author Russell Albin <russell@russellalbin.com>
 */

declare(strict_types=1);

namespace Mac\AsyncLogger\Model;

use Mac\AsyncLogger\Model\Config\Debugengine;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Class used to define what types of events we want to log
 */
class AsyncLogger implements LoggerInterface
{
    /** @var PublisherInterface  */
    protected PublisherInterface $publisher;

    /** @var Json  */
    protected Json $jsonSerializer;

    /** @var Cleanser  */
    protected Cleanser $cleanser;

    /** @var ScopeConfigInterface  */
    protected ScopeConfigInterface $config;

    /** @var StoreManagerInterface  */
    protected StoreManagerInterface $storeManager;

    /** @var LoggerInterface  */
    protected LoggerInterface $logger;

    /**
     * Constructor function
     *
     * @param PublisherInterface $publisher
     * @param Json $jsonSerializer
     * @param Cleanser $cleanser
     * @param ScopeConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        PublisherInterface $publisher,
        Json $jsonSerializer,
        Cleanser $cleanser,
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->publisher = $publisher;
        $this->jsonSerializer = $jsonSerializer;
        $this->cleanser = $cleanser;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function emergency($message, array $context = [])
    {
        if (!$this->checkActiveDebugLevel(LogLevel::EMERGENCY)) {
            return false;
        }
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function alert($message, array $context = [])
    {
        if (!$this->checkActiveDebugLevel(LogLevel::ALERT)) {
            return false;
        }
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function critical($message, array $context = [])
    {
        if (!$this->checkActiveDebugLevel(LogLevel::CRITICAL)) {
            return false;
        }
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function error($message, array $context = [])
    {
        if (!$this->checkActiveDebugLevel(LogLevel::ERROR)) {
            return false;
        }
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function warning($message, array $context = [])
    {
        if (!$this->checkActiveDebugLevel(LogLevel::WARNING)) {
            return false;
        }
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function notice($message, array $context = [])
    {
        if (!$this->checkActiveDebugLevel(LogLevel::NOTICE)) {
            return false;
        }
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function info($message, array $context = [])
    {
        if (!$this->checkActiveDebugLevel(LogLevel::INFO)) {
            return false;
        }
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function debug($message, array $context = [])
    {
        if (!$this->checkActiveDebugLevel(LogLevel::DEBUG)) {
            return false;
        }
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = [])
    {
        if (!$this->isDebuggingActive()) {
            return false;
        }
        $store = $this->storeManager->getStore();
        $context['storecode'] = $store->getCode();
        $context['storeId'] = $store->getId();
        $message = $this->cleanser->checkForSensitiveData($message);
        $data = [
            'message' => $message,
            'level' => $level,
            'context' => $context
        ];

        //to check debugger engine : if not RabbitMQ then it will generate direct log
        //in case of failure of rabbitmq will generate direct log
        if ($this->getDebuggerEngine() == Debugengine::RABBITMQ) {
            try {
                $jsonData = $this->jsonSerializer->serialize($data);
                $this->publisher->publish('asynclogger', $jsonData);
            } catch (\Exception $e) {
                unset($data['level']);
                $jsonData = $this->jsonSerializer->serialize($data);
                $this->logger->$level($jsonData);
            }
        } else {
            unset($data['level']);
            $jsonData = $this->jsonSerializer->serialize($data);
            $this->logger->$level($jsonData);
        }
    }
    /**
     * Check active status of logging function
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException | bool
     */
    public function isDebuggingActive()
    {
        /**
         * @TODO Ensure this actually works as anticipated, perhaps do we need to pass in $store as an argument instead
         */
        $store = $this->storeManager->getStore();
        if (!$this->config->isSetFlag(
            'logger/general/debug',
            ScopeInterface::SCOPE_WEBSITE,
            $store->getWebsiteId()
        )
        ) {
            return false;
        }
        return true;
    }
    /**
     * Check active log level function
     *
     * @param string $level
     * @return bool
     */
    protected function checkActiveDebugLevel($level)
    {
        /**
         * @TODO Ensure this actually works as anticipated, perhaps do we need to pass in $store as an argument instead
         */
        $store = $this->storeManager->getStore();
        $debugLevel = $this->config->getValue(
            'logger/general/debuglevel',
            ScopeInterface::SCOPE_WEBSITE,
            $store->getWebsiteId()
        );
        if (!empty($debugLevel)) {
            $levelArr = explode(',', $debugLevel);
            if (in_array($level, $levelArr)) {
                return true;
            }
            return false;
        }
        return false;
    }
    /**
     * Get debug engine function
     *
     * @return string
     */
    protected function getDebuggerEngine()
    {
        /**
         * @TODO Ensure this actually works as anticipated, perhaps do we need to pass in $store as an argument instead
         */
        $store = $this->storeManager->getStore();
        $debugEngine = $this->config->getValue(
            'logger/general/debugengine',
            ScopeInterface::SCOPE_WEBSITE,
            $store->getWebsiteId()
        );
        return $debugEngine;
    }
}
