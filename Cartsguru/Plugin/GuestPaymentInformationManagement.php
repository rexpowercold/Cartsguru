<?php

namespace Cartsguru\Cartsguru\Plugin;

use Magento\Framework\Exception\LocalizedException;

class GuestPaymentInformationManagement
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

    public function afterSavePaymentInformation (
      \Magento\Checkout\Model\GuestShippingInformationManagement\Interceptor $interceptor,
      $cartId,
      $email,
      \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
      \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
      $this->_cartsguruHelper->sendCart($this->_checkoutSession->getQuote());
      return null;
    }
}
