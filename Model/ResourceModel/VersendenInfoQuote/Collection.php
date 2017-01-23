<?php
/**
 * Dhl Versenden
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
 * @package   Dhl\Versenden
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Model\ResourceModel\VersendenInfoQuote;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use \Dhl\Versenden\Model;
use \Dhl\Versenden\Setup\InstallSchema;

/**
 * Dhl Versenden Info Resource Model
 *
 * @category Dhl
 * @package  Dhl\Versenden
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = InstallSchema::TABLE_VERSENDEN_INFO_QUOTE_PK;

    /**
     * Resource collection initialization
     */
    protected function _construct()
    {
        $this->_init(Model\VersendenInfoQuote::class, Model\ResourceModel\VersendenInfoQuote::class);

        parent::_construct();
    }
}
