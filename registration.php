<?php
/**
 * @package Mac_AsynLogger
 * @author Russell Albin <russell@russellalbin.com>
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE, 'Mac_AsyncLogger', __DIR__);
