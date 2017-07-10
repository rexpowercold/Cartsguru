<?php
/**
* Copyright © 2017 Carts Guru Ltd. All rights reserved.
* See COPYING.txt for license details.
 */
namespace Cartsguru\Cartsguru\Plugin;

use Magento\Framework\Exception\LocalizedException;

class GuestShippingInformationManagement
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_checkoutSession;
    protected $_helper;

    /**
     * Plugin constructor.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Cartsguru\Cartsguru\Helper\Data $helper
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_cartsguruHelper = $helper;
    }

    public function afterSaveAddressInformation(
        \Magento\Checkout\Model\GuestShippingInformationManagement\Interceptor $interceptor,
        $cartId) {
      $this->_cartsguruHelper->sendCart($this->_checkoutSession->getQuote());
      return $cartId;
    }
}
