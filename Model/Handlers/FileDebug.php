<?php
/**
 * @package Mac_AsynLogger
 * @author Russell Albin <russell@russellalbin.com>
 */

declare(strict_types=1);

namespace Mac\AsyncLogger\Model\Handlers;

use Magento\Framework\Logger\Handler\Base;

/**
 * Class to define our logging level and filename
 */
class FileDebug extends Base
{
    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = \Monolog\Logger::DEBUG;

    /**
     * Our custom log debug file name
     *
     * @var string
     */
    protected $fileName = '/var/log/mac_debug.log';
}
