<?php

namespace Cartsguru\Cartsguru\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Cartsguru\Cartsguru\Helper\Data as DataHelper;

class PaymentInformationManagement
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
        DataHelper $helper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->quote = $checkoutSession->getQuote();
        $this->request = $request;
        $this->_cart = $cart;
        $this->customerRepository = $customerRepository;
    }

    // public function afterSaveAddressInformation(
    //     \Magento\Checkout\Model\GuestShippingInformationManagement\Interceptor $interceptor,
    //     $cartId) {
    //   $customer = $this->quote->getCustomer();
    //   $address = $this->quote->getShippingAddress();
      //$billingAddress = $addressInformation->getBillingAddress();
      // echo get_class($this->_cart->getQuote()) . "\n";
      // echo print_r(get_class_methods($this->_cart->getQuote())) . "\n";
      // echo get_class($this->_cart->getCheckoutSession()) . "\n";
      //echo print_r(get_class_methods($this->_cart->getCheckoutSession())) . "\n";
      // echo get_class($this->_cart->getCustomerSession()) . "\n";
       //echo print_r(get_class_methods($this->_cart->getCustomerSession())) . "\n";
       //print_r($this->_cart->getCheckoutSession()->getData()) . "\n";
       //echo "getCustomerId " . $this->_cart->getCustomerSession()->getCustomerId() . "\n";
       //echo "getCustomerGroupId " .$this->_cart->getCustomerSession()->getCustomerGroupId() . "\n";
       // echo "getCustomerData " . print_r($this->_cart->getCustomerSession()->getCustomerData()) . "\n";
       //echo "getCustomerDataObject " . print_r($this->_cart->getCustomerSession()->getCustomerDataObject()) . "\n";
       //die;
      //$helper->sendCart($shippingAddress, $this->_cart);
      // return null;
    // }
}
