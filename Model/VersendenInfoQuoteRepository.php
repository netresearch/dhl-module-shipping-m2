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
namespace Dhl\Versenden\Model;

use \Dhl\Versenden\Api\Data\VersendenInfoQuoteInterface;
use \Dhl\Versenden\Api\VersendenInfoQuoteRepositoryInterface;
use \Dhl\Versenden\Model\ResourceModel\Metadata;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;

/**
 * Repository class for @see VersendenInfoQuoteInterface
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VersendenInfoQuoteRepository implements VersendenInfoQuoteRepositoryInterface
{
    /**
     * @var Metadata
     */
    private $metadata;

    /**
     * @var VersendenInfoQuoteInterface[]
     */
    private $registry = [];

    /**
     * @param Metadata $metadata
     */
    public function __construct(Metadata $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * Loads a specified versenden info order extension attribute.
     *
     * @param int $id
     *
     * @return VersendenInfoQuoteInterface
     * @throws NoSuchEntityException | InputException
     */
    public function get($id)
    {
        if (!$id) {
            throw new InputException(__('Id required'));
        }

        if (!isset($this->registry[$id])) {
            /** @var VersendenInfoQuoteInterface $entity */
            $entity = $this->metadata->getNewInstance()->load($id);
            if (!$entity->getQuoteAddressId()) {
                throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
            }

            $this->registry[$id] = $entity;
        }

        return $this->registry[$id];
    }

    /**
     * Deletes a specified versenden info order extension attribute.
     *
     * @param VersendenInfoQuoteInterface $entity
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(VersendenInfoQuoteInterface $entity)
    {
        try {
            $this->metadata->getMapper()->delete($entity);

            unset($this->registry[$entity->getQuoteAddressId()]);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete order address'), $e);
        }

        return true;
    }

    /**
     * Deletes order address by Id.
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteById($id)
    {
        $entity = $this->get($id);

        return $this->delete($entity);
    }

    /**
     * Performs persist operations for a specified versenden info order extension attribute.
     *
     * @param VersendenInfoQuoteInterface $entity
     *
     * @return VersendenInfoQuoteInterface
     * @throws CouldNotSaveException
     */
    public function save(VersendenInfoQuoteInterface $entity)
    {
        try {
            $this->metadata->getMapper()->save($entity);
            $this->registry[$entity->getQuoteAddressId()] = $entity;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save versenden info order extension attribute'), $e);
        }

        return $this->registry[$entity->getQuoteAddressId()];
    }

    /**
     * Creates new order address instance.
     *
     * @return VersendenInfoQuoteInterface
     */
    public function create()
    {
        return $this->metadata->getNewInstance();
    }
}
