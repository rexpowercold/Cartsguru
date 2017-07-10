<?php
/**
* Copyright © 2017 Carts Guru Ltd. All rights reserved.
* See COPYING.txt for license details.
 */
namespace Cartsguru\Cartsguru\Block;
use Magento\Framework\View\Element\Template;

class Pixel extends Template
{

  protected $_customerSession;
  protected $_helper;

  /**
  * @param Template\Context $context
  * @param Session $customerSession
  * @param DataHelper $dataHelper
  * @param array $data
  *
  */
  public function __construct(
    Template\Context $context,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Checkout\Model\Session $checkoutSession,
    \Magento\Sales\Model\OrderFactory $orderFactory,
    \Magento\Framework\Registry $registry,
    \Cartsguru\Cartsguru\Helper\Data $dataHelper,
    array $data = []
  ) {
    $this->_helper = $dataHelper;
    $this->_customerSession = $customerSession;
    $this->_checkoutSession = $checkoutSession;
    $this->_orderFactory = $orderFactory;
    $this->_registry = $registry;
    parent::__construct($context, $data);
    $this->_isScopePrivate = true;
  }

  public function getProduct()
  {
    return $this->_registry->registry('product');
  }

  /**
  * Get Last order Id
  */
  public function getLastOrder()
  {
    return $this->_orderFactory->create()->loadByIncrementId($this->_checkoutSession->getLastRealOrderId());
    // $this->_orderCollectionFactory->getOrders()->load($this->_customerSession->getCustomer()->getLastOrderId());
  }

  /**
  * Get Currency
  */
  public function getCurrency()
  {
    return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
  }

  /**
  * Check if Facebook is enabled
  */
  public function isFacebookEnabled()
  {
    return $this->_helper->getStoreConfig("feature_facebook");
  }

  /**
  * Get FB pixel from config
  */
  public function getPixel()
  {
    return $this->_helper->getStoreConfig("facebook_pixel");
  }

  /**
  * Get CatalogId from config
  */
  public function getCatalogId()
  {
    return $this->_helper->getStoreConfig("facebook_catalogid");
  }

  /**
  * Get the product added to cart that we saved in session
  */
  public function getAddToCartProduct()
  {
    $productData = $this->_customerSession->getCartsGuruAddToCart();
    if ($productData) {
      $this->_customerSession->unsCartsGuruAddToCart();
      return $productData;
    }
    return false;
  }
  /**
  * Get the tracking URL
  */
  public function getTrackingURL()
  {
    return '/cartsguru/frontend/index';
  }
}
