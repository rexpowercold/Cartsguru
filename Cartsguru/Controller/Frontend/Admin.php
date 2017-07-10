<?php
/**
* Copyright © 2017 Carts Guru Ltd. All rights reserved.
* See COPYING.txt for license details.
 */
namespace Cartsguru\Cartsguru\Controller\Frontend;

class Admin extends \Magento\Framework\App\Action\Action
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
    \Cartsguru\Cartsguru\Helper\Data $helper
  ) {
    $this->_customerSession = $customerSession;
    $this->_checkoutSession = $checkoutSession;
    $this->_productMetadata = $productMetadata;
    $this->_storeManager = $storeManager;
    $this->_helper = $helper;
    parent::__construct($context);
  }

  public function execute()
  {
    echo print_r($this->_helper->sendHistory()); die;
    $params = $this->getRequest()->getParams();
    $auth_key = $this->_helper->getStoreConfig('authkey');
    // Stop if no enoguth params
    if (!isset($params['admin_action']) || !isset($params['auth_key']) || $auth_key !== $params['auth_key']) {
      die;
    }
    // Toggle features action
    if ($params['admin_action'] === 'toggleFeatures' && isset($params['admin_data'])) {
      $data = json_decode($params['admin_data'], true);
      if (is_array($data)) {
        // Enable facebook
        if ($data['facebook'] && $data['catalogId'] && $data['pixel']) {
          // Save facebook pixel
          $this->_helper->setStoreConfig('feature_facebook', true);
          $this->_helper->setStoreConfig('facebook_pixel', $data['pixel']);
          $this->_helper->setStoreConfig('facebook_catalogid', $data['catalogId']);
          // return catalogUrl
          header('Content-Type: application/json; charset=utf-8');
          echo json_encode(array(
            'catalogUrl' => $this->_storeManager->getStore()->getBaseUrl() . 'cartsguru/frontend/catalog'
          ));
        } elseif ($data['facebook'] == false) {
          $this->_helper->setStoreConfig('feature_facebook', false);
        }
      }
    }
    // Get config
    if ($params['admin_action'] === 'displayConfig') {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(array(
        'CARTSG_SITE_ID' => $this->_helper->getStoreConfig('siteid'),
        'CARTSG_FEATURE_FB' => $this->_helper->getStoreConfig('feature_facebook'),
        'CARTSG_FB_PIXEL' => $this->_helper->getStoreConfig('facebook_pixel'),
        'CARTSG_FB_CATALOGID' => $this->_helper->getStoreConfig('facebook_catalogid'),
        'PLUGIN_VERSION'=> (string) $this->_helper::CARTSGURU_VERSION
      ));
    }
    die;
  }
}
