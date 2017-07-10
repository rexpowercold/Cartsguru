<?php
/**
* Copyright © 2017 Carts Guru Ltd. All rights reserved.
* See COPYING.txt for license details.
 */
namespace Cartsguru\Cartsguru\Plugin;

use Magento\Framework\Exception\LocalizedException;

class AddToCart
{
  protected $_customerSession;
  protected $_storeManager;
  protected $_productRepository;

  public function __construct(
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
  ) {
    $this->_customerSession = $customerSession;
    $this->_storeManager = $storeManager;
    $this->_productRepository = $productRepository;
  }

  public function afterAddProduct(\Magento\Checkout\Model\Cart $cart) {
    $productId = $cart->getCheckoutSession()->getLastAddedProductId();
    if ($productId) {
      $storeId = $this->_storeManager->getStore()->getId();
      $product = $this->_productRepository->getById($productId, false, $storeId);
      if ($product) {
        $this->_customerSession->setCartsGuruAddToCart(array(
          'id' => $productId,
          'price' => $product->getFinalPrice()
        ));
      }
    }
    return $cart;
  }
}
