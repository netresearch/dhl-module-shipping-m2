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
 * PHP version 7
 *
 * @category  Dhl
 * @package   Dhl\Shipping\Webservice
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Webservice;

use \Dhl\Shipping\Model\Config\ModuleConfigInterface;
use \Dhl\Shipping\Webservice\Client\HttpClientInterface;
use \Magento\Framework\Logger\Monolog;

/**
 * Logger
 *
 * @category Dhl
 * @package  Dhl\Shipping\Webservice
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Logger extends Monolog implements WebserviceLoggerInterface
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

    /**
     * @param HttpClientInterface $httpClient
     * @param array               $context
     */
    public function wsDebug(HttpClientInterface $httpClient, array $context = [])
    {
        $this->wsLog(self::DEBUG, $httpClient, $context);
    }

    /**
     * @param HttpClientInterface $httpClient
     * @param array               $context
     */
    public function wsWarning(HttpClientInterface $httpClient, array $context = [])
    {
        $this->wsLog(self::WARNING, $httpClient, $context);
    }

    /**
     * @param HttpClientInterface $httpClient
     * @param array               $context
     */
    public function wsError(HttpClientInterface $httpClient, array $context = [])
    {
        $this->wsLog(self::ERROR, $httpClient, $context);
    }

    /**
     * @param                     $level
     * @param HttpClientInterface $httpClient
     * @param array               $context
     */
    private function wsLog($level, HttpClientInterface $httpClient, array $context = [])
    {
        $this->log($level, $httpClient->getLastRequest(), $context);
        $this->log($level, $httpClient->getLastResponseHeaders(), $context);
        $this->log($level, $httpClient->getLastResponse(), $context);
    }
}
