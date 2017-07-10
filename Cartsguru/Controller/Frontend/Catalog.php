<?php
/**
* Copyright © 2017 Carts Guru Ltd. All rights reserved.
* See COPYING.txt for license details.
 */
namespace Cartsguru\Cartsguru\Controller\Frontend;

class Catalog extends \Magento\Framework\App\Action\Action
{
  protected $_helper;

  /**
  * @param \Magento\Backend\App\Action\Context $context
  * @param \Magento\Framework\Registry $coreRegistry
  * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
  */
  public function __construct(
    \Magento\Backend\App\Action\Context $context,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Checkout\Model\Session $checkoutSession,
    \Magento\Framework\App\ProductMetadataInterface $productMetadata,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
    \Cartsguru\Cartsguru\Helper\Catalog $catalogHelper,
    \Cartsguru\Cartsguru\Helper\Data $helper
  ) {
    $this->_customerSession = $customerSession;
    $this->_checkoutSession = $checkoutSession;
    $this->_productMetadata = $productMetadata;
    $this->_productCollection = $productCollection;
    $this->_storeManager = $storeManager;
    $this->_helper = $helper;
    $this->_catalogHelper = $catalogHelper;
    parent::__construct($context);
  }

  /**
  * Index action
  *
  * @return \Magento\Framework\Controller\ResultInterface
  */
  public function execute()
  {
        $params = $this->getRequest()->getParams();
        $auth_key = $this->_helper->getStoreConfig('auth');
        // Stop if not authenticated
        if (!isset($params['auth_key']) || $auth_key !== $params['auth_key']) {
            die;
        }
        // Get input values
        $offset = isset($params['catalog_offset']) ? $params['catalog_offset'] : 0;
        $limit = isset($params['catalog_limit']) ? $params['catalog_limit'] : 50;

        $store = $this->_storeManager->getStore();

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($this->_catalogHelper->generateFeed($store, $offset, $limit)));

  }
}
