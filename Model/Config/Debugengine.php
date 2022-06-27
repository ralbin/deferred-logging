<?php
/**
 * @package Mac_AsyncLogger
 * @author Russell Albin <russell@russellalbin.com>
 */

declare(strict_types=1);

namespace Mac\AsyncLogger\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for element with Debug engine
 */
class Debugengine implements OptionSourceInterface
{
    /**
     * Value which equal Direct for Debug engine dropdown.
     */
    public const DIRECT = 'direct';

    /**
     * Value which equal RabbitMQ for Debug engine dropdown.
     */
    public const RABBITMQ = 'rabbitmq';

    /**
     * ToOptionArray function
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::DIRECT, 'label' => __('Direct')],
            ['value' => self::RABBITMQ, 'label' => __('RabbitMQ')]
        ];
    }
}
