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
 * @package   Dhl\Shipping\Plugin
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Plugin;

use Dhl\Shipping\Cron\AutoCreate;
use Dhl\Shipping\Util\Logger;
use Psr\Log\LoggerInterface;

/**
 * Class AutoCreatePlugin
 *
 * Handles logging of errors for automatic/batch shipment creation
 *
 * @package Dhl\Shipping\Plugin
 *
 */
class AutoCreatePlugin
{
    /** @var  LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log resulting
     * @param AutoCreate $subject
     * @param mixed[] $result
     * @return mixed[]
     */
    public function afterRun(AutoCreate $subject, $result)
    {
        foreach ($subject->getErrors() as $error) {
            $this->logger->log(
                Logger::ERROR,
                $error->render()
            );
        }

        return $result;
    }
}
