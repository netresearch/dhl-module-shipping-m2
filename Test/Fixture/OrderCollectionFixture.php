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
 * @category  DHL
 * @package   Dhl\Shipping\Test\Fixture
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Test\Fixture;

use Dhl\Shipping\Model\Shipping\Carrier;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class OrderCollectionFixture
 * @package Dhl\Shipping\Test\Fixture
 */
final class OrderCollectionFixture
{
    private static $orderIncrementIds = [
        '0000000001',
        '0000000002',
        '0000000003',
        '0000000004',
        '0000000005',
    ];

    private static $allowedOrderIncrementIds = [
        '0000000001',
        '0000000004',
        '0000000005'
    ];

    private static $orderData = [
        '0000000001' => [
            'status' => Order::STATE_PROCESSING,
        ],
        '0000000002' => [
            'state' => Order::STATE_CANCELED,
            'status' => Order::STATE_CANCELED,
        ],
        '0000000003' => [
            'shipping_method' => 'bar_foo',
        ],
        '0000000004' => [],
        '0000000005' => [],
    ];

    private static $customerData = [
        'email' => 'foo@example.org',
        'firstname' => 'Foo',
        'lastname' => 'Bariton',
        'company' => '',
        'street' => 'Nonnenstr. 11d',
        'city' => 'Leipzig',
        'postcode' => '04229',
        'country' => 'DE',
        'phone' => '03412354897',
    ];
    /**
     * @var string[]
     */
    private static $productData = [
        'FX-24-MB03' => [
            'name' => 'Crown Summit Fixture Backpack',
            'qty' => 1,
            'weight' => 0.95,
            'unit_price' => 38,
        ],
        'FX-24-WG03' => [
            'name' => 'Clamber Fixture Watch',
            'qty' => 2,
            'weight' => 0.43,
            'unit_price' => 54,
        ],
    ];

    /**
     * @param string $addressType
     * @return OrderAddress
     */
    private function getOrderAddress($addressType)
    {
        $address = Bootstrap::getObjectManager()
                            ->create(
                                OrderAddress::class,
                                [
                                    'data' => [
                                        'address_type' => $addressType,
                                        'email' => self::$customerData['email'],
                                        'firstname' => self::$customerData['firstname'],
                                        'lastname' => self::$customerData['lastname'],
                                        'company' => self::$customerData['company'],
                                        'street' => self::$customerData['street'],
                                        'city' => self::$customerData['city'],
                                        'postcode' => self::$customerData['postcode'],
                                        'country_id' => self::$customerData['country'],
                                        'telephone' => self::$customerData['phone'],
                                    ]
                                ]
                            );

        return $address;
    }

    /**
     * @param string $orderIncrementId
     * @param string $quoteId
     * @param string $storeId
     * @param string $currency
     * @return \Magento\Sales\Model\Order
     */
    private function getOrder(
        $orderIncrementId,
        $quoteId,
        $storeId = '1',
        $currency = 'EUR'
    ) {
        $orderDate = date('Y-m-d H:i:s');
        $shippingCost = 7.95;
        $subTotal = 0;

        $orderData = array_merge(
            [
                'increment_id' => $orderIncrementId,
                'quote_id' => $quoteId,
                'store_id' => $storeId,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
                'shipping_amount' => $shippingCost,
                'base_shipping_amount' => $shippingCost,
                'base_currency_code' => $currency,
                'store_currency_code' => $currency,
                'customer_email' => self::$customerData['email'],
                'customer_firstname' => self::$customerData['firstname'],
                'customer_lastname' => self::$customerData['lastname'],
                'shipping_method' => Carrier::CODE . '_foo',
                'state' => Order::STATE_NEW,
                'status' => Order::STATE_NEW,
            ],
            self::$orderData[$orderIncrementId]
        );
        /** @var \Magento\Sales\Model\Order $order */
        $order = Bootstrap::getObjectManager()
                          ->create(
                              \Magento\Sales\Model\Order::class,
                              [
                                  'data' => $orderData
                              ]
                          );

        foreach (self::$productData as $sku => $productData) {
            $orderItemQty = $productData['qty'];
            $orderItemUnitPrice = $productData['unit_price'];
            $orderItem = Bootstrap::getObjectManager()
                                  ->create(
                                      \Magento\Sales\Model\Order\Item::class,
                                      [
                                          'data' => [
                                              'created_at' => $orderDate,
                                              'store_id' => $storeId,
                                              'is_virtual' => false,
                                              'sku' => $sku,
                                              'name' => $productData['name'],
                                              'product_id' => $productData['entity_id'],
                                              'qty' => $orderItemQty,
                                              'price' => $orderItemUnitPrice,
                                              'base_price' => $orderItemUnitPrice,
                                              'row_total' => $orderItemQty * $orderItemUnitPrice,
                                              'base_row_total' => $orderItemQty * $orderItemUnitPrice,
                                              'product_type' => 'simple',
                                              'price_incl_tax' => $orderItemUnitPrice,
                                              'base_price_incl_tax' => $orderItemUnitPrice,
                                              'row_total_incl_tax' => $orderItemQty * $orderItemUnitPrice,
                                              'base_row_total_incl_tax' => $orderItemQty * $orderItemUnitPrice,
                                              'qty_ordered' => $productData['qty'],
                                              'qty_shipped' => 0,
                                              'qty_to_ship' => 0,
                                              'qty_refunded' => 0,
                                              'qty_canceled' => 0,
                                          ]
                                      ]
                                  );
            $order->addItem($orderItem);
            $subTotal += ($orderItemQty * $orderItemUnitPrice);
        }

        $order->setTotalItemCount(count($order->getItems()));
        $order->setSubtotal($subTotal);
        $order->setBaseSubtotal($subTotal);
        $order->setGrandTotal($subTotal + $shippingCost);
        $order->setBaseGrandTotal($subTotal + $shippingCost);

        return $order;
    }

    // -------------------------------- Public property accessors ---------------------------------------//

    public static function getOrderIncrementIds()
    {
        return self::$orderIncrementIds;
    }

    public static function getAutoCreateOrderIncrementIds()
    {
        return self::$allowedOrderIncrementIds;
    }

    // --------------------------------- Create and Rollback functions ----------------------------------//

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function createOrders()
    {
        $orderIncrementIds = $this->getOrderIncrementIds();

        // save products
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()
                                      ->get(ProductRepositoryInterface::class);
        foreach (self::$productData as $sku => &$productData) {
            $product = Bootstrap::getObjectManager()
                                ->create(
                                    \Magento\Catalog\Model\Product::class,
                                    [
                                        'data' => [
                                            'attribute_set_id' => '4',
                                            'type_id' => 'simple',
                                            'sku' => $sku,
                                            'name' => $productData['name'],
                                            'price' => $productData['unit_price'],
                                        ]
                                    ]
                                );
            $product = $productRepository->save($product);
            $productData['entity_id'] = $product->getId();
        }

        // save order
        $orderRepository = Bootstrap::getObjectManager()
                                    ->get(OrderRepository::class);
        foreach ($orderIncrementIds as $orderIncrementId) {
            $order = $this->getOrder(
                $orderIncrementId,
                null
            );
            $orderBillingAddress = $this->getOrderAddress(OrderAddress::TYPE_BILLING);
            $orderShippingAddress = $this->getOrderAddress(OrderAddress::TYPE_SHIPPING);
            $payment = Bootstrap::getObjectManager()
                                ->create(
                                    \Magento\Sales\Model\Order\Payment::class,
                                    [
                                        'data' => [
                                            'method' => 'checkmo',
                                        ]
                                    ]
                                );
            $order->setBillingAddress($orderBillingAddress);
            $order->setShippingAddress($orderShippingAddress);
            $order->setPayment($payment);

            $orderRepository->save($order);
        }
        return $order;
    }

    public function rollbackOrders()
    {
        $orderIncrementIds = $this->getOrderIncrementIds();

        /** @var OrderRepository $orderRepository */
        $orderRepository = Bootstrap::getObjectManager()
                                    ->get(OrderRepository::class);
        /** @var ProductRepositoryInterface|ProductRepository $productRepository */
        $productRepository = Bootstrap::getObjectManager()
                                      ->get(ProductRepositoryInterface::class);

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()
                                          ->create(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->addFilter(
            'increment_id',
            $orderIncrementIds,
            'in'
        );
        $searchResult = $orderRepository->getList($searchCriteriaBuilder->create());
        foreach ($searchResult as $order) {
            $orderRepository->delete($order);
        }

        $skus = array_keys(self::$productData);
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()
                                          ->create(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->addFilter(
            'sku',
            $skus,
            'in'
        );
        $searchResult = $productRepository->getList($searchCriteriaBuilder->create());
        foreach ($searchResult->getItems() as $product) {
            $productRepository->delete($product);
        }

        $productRepository->cleanCache();
    }

    // ------------------------- STATIC ENTRYPOINTS ------------------------- //

    /**
     * Create fixtures:
     * - products
     * - order with order items
     */
    public static function createOrdersFixture()
    {
        /** @var OrderCollectionFixture $self */
        $self = Bootstrap::getObjectManager()
                         ->create(static::class);
        $self->createOrders();
    }

    /**
     * Rollback fixtures:
     * - orders
     * - products
     */
    public static function createOrdersRollback()
    {
        /** @var OrderCollectionFixture $self */
        $self = Bootstrap::getObjectManager()
                         ->create(static::class);
        $self->rollbackOrders();
    }
}
