<?php
/**
 * Dhl Shipping
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @category  Dhl
 * @package   Dhl\Shipping\Util
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Util;

use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Magento\Framework\Logger\Monolog;

/**
 * Class Logger
 * @package Dhl\Shipping\Util
 */
class Logger extends Monolog
{
    /**
     * @var ModuleConfigInterface
     */
    private $config;

    /**
     * Logger constructor.
     *
     * @param ModuleConfigInterface               $config
     * @param string                              $name
     * @param \Monolog\Handler\HandlerInterface[] $handlers
     * @param callable[]                          $processors
     */
    public function __construct(
        ModuleConfigInterface $config,
        $name,
        $handlers = [],
        $processors = []
    ) {
        $this->config = $config;

        parent::__construct($name, $handlers, $processors);
    }

    /**
     * Log message if logging is enabled via module config.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return bool
     */
    public function log($level, $message, array $context = [])
    {
        if ($this->config->isLoggingEnabled($level)) {
            return parent::log($level, $message, $context);
        }

        return false;
    }
}
