<?php
/**
 * @package Mac_AsyncLogger
 * @author Russell Albin <russell@russellalbin.com>
 */

declare(strict_types=1);

namespace Mac\AsyncLogger\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;
use Psr\Log\LogLevel;

/**
 * Source model for element with Debug level vairants
 */
class Debuglevel implements OptionSourceInterface
{

    /**
     * ToOptionArray function
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => LogLevel::DEBUG, 'label' => __('DEBUG')],
            ['value' => LogLevel::CRITICAL, 'label' => __('CRITICAL')],
            ['value' => LogLevel::ALERT, 'label' => __('ALERT')],
            ['value' => LogLevel::EMERGENCY, 'label' => __('EMERGENCY')],
            ['value' => LogLevel::ERROR, 'label' => __('ERROR')],
            ['value' => LogLevel::INFO, 'label' => __('INFO')],
            ['value' => LogLevel::NOTICE, 'label' => __('NOTICE')],
            ['value' => LogLevel::WARNING, 'label' => __('WARNING')]
        ];
    }
}
