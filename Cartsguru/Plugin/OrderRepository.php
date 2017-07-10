<?php
/**
* Copyright © 2017 Carts Guru Ltd. All rights reserved.
* See COPYING.txt for license details.
 */
namespace Cartsguru\Cartsguru\Plugin;

use Magento\Framework\Exception\LocalizedException;

class OrderRepository
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;
    protected $_helper;
    protected $request;

    /**
     * Plugin constructor.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Cartsguru\Cartsguru\Helper\Data $helper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_cartsguruHelper = $helper;
        $this->customerRepository = $customerRepository;
        $this->_cart = $cart;
    }

    public function afterSave(
        \Magento\Sales\Model\OrderRepository\Interceptor $interceptor,
        $order) {
      $this->_cartsguruHelper->sendOrder($order);
      return $order;
    }
}
