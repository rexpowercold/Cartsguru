<?php
/**
* Copyright © 2017 Carts Guru Ltd. All rights reserved.
* See COPYING.txt for license details.
*/
namespace Cartsguru\Cartsguru\Helper;

/**
* Cartsguru Cartsguru helper
*/
class Catalog extends \Magento\Framework\App\Helper\AbstractHelper
{

  protected $_storeManager;
  protected $_productCollectionFactory;

  /*
  * @param \Magento\Store\Model\StoreManagerInterface $storeManager
  * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
  */

  public function __construct(
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    )
    {
      $this->_storeManager = $storeManager;
      $this->_productCollectionFactory = $productCollectionFactory;

    }

    /**
    * The fields to be put into the feed.
    * @var array
    */
    protected $_requiredFields = array(
      array(
        'magento'   => 'id',
        'feed'      => 'id',
        'type'      => 'id',
      ),
      array(
        'magento'   => 'availability_google',
        'feed'      => 'availability',
        'type'      => 'computed',
      ),
      // condition here
      array(
        'magento'   => 'description',
        'feed'      => 'description',
        'type'      => 'product_attribute',
      ),
      array(
        'magento'   => 'image_url',
        'feed'      => 'image_link',
        'type'      => 'computed',
      ),
      array(
        'magento'   => 'product_link',
        'feed'      => 'link',
        'type'      => 'computed',
      ),
      array(
        'magento'   => 'name',
        'feed'      => 'title',
        'type'      => 'product_attribute',
      ),
      array(
        'magento'   => 'manufacturer',
        'feed'      => 'brand',
        'type'      => 'product_attribute',
      ),
      array(
        'magento'   => 'price',
        'feed'      => 'price',
        'type'      => 'computed',
      )
    );

    /*
    * Generate XML product feed
    */
    public function generateFeed($store, $offset, $limit)
    {
      // setup attribute mapping
      $this->_attributes = array();

      foreach ($this->_requiredFields as $requiredField) {
        $this->_attributes[$requiredField['feed']] = $requiredField;
      }

      $result = array(
        'url' => $this->_storeManager->getStore()->getBaseUrl(),
        'store_name' => $this->_storeManager->getStore()->getName(),
        'total' => $this->_productCollectionFactory->create()->addStoreFilter()->addFieldToFilter('status', '1')->getSize()
      );

      $productCollection = $this->_productCollectionFactory->create()->addStoreFilter()->addFieldToFilter('status', '1')->addAttributeToSelect('*')->setPageSize($limit)->setCurPage($offset/$limit > 0 ? $offset/$limit : 0);

      $products = array();

      foreach ($productCollection as $product) {
        $products[] = $this->processProduct($product, $store);
      }

      $result['products'] = $products;

      return $result;
    }

    /*
    * Process each product in a loop
    */
    public function processProduct($product, $store)
    {

      $product_data = array();
      $attributes = $this->_attributes;

      // Prepare attributes
      foreach ($attributes as $attribute) {
        if ($attribute['type'] == 'id') {
          $value = $product->getId();
        } elseif ($attribute['type'] == 'product_attribute') {
          // if this is a normal product attribute, retrieve it's frontend representation
          if ($product->getData($attribute['magento']) === null) {
            $value = '';
          } else {
            /** @var $attributeObj Mage_Catalog_Model_Resource_Eav_Attribute */
            $attributeObj = $product->getResource()->getAttribute($attribute['magento']);
            $value = $attributeObj->getFrontend()->getValue($product);
          }
        } elseif ($attribute['type'] == 'computed') {
          // if this is a computed attribute, handle it depending on its code
          switch ($attribute['magento']) {
            case 'price':
            $value = $product->getFinalPrice() . ' '. $store->getCurrentCurrency()->getCode();
            break;

            case 'product_link':
            $value = $product->getProductUrl();
            break;

            case 'image_url':
            $value = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getData('image');
            break;

            case 'availability_google':
            $value = $product->isSaleable() ? 'in stock' : 'out of stock';
            break;

            default:
            $value = '';
          }
        }
        $product_data[$attribute['feed']] = $value;
      }

      $price = floatval($product_data['price']);
      // Price is required
      if (empty($price)) {
        return;
      }

      // If manufacturer not set use mpn === sku
      if ($product_data['brand'] === '') {
        unset($product_data['brand']);
        $product_data['mpn'] = $product_data['id'];
      }

      // All products are new
      $product_data['condition'] = 'new';
      return $product_data;
    }
  }
