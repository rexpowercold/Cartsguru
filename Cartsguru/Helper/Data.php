<?php
/**
* Copyright © 2017 Carts Guru Ltd. All rights reserved.
* See COPYING.txt for license details.
*/
namespace Cartsguru\Cartsguru\Helper;

/**
* Cartsguru Cartsguru helper
*/
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
  /**
  * Path to store config if extension is enabled
  *
  * @var string
  */
  const XML_PATH_ENABLED = 'cartsguru/basic/enabled';
  const CONFIG_BASE_PATH = 'cartsguru/cartsguru_group/';
  const CARTSGURU_VERSION = '1.0';
  //const API_BASE_URL = 'https://api.carts.guru/';
  const API_BASE_URL = 'http://api.cartninja.io/';

  protected $_scopeConfig;
  protected $_resourceConfig;
  protected $_urlBuilder;

  public $_storeManager;


  /*
  * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
  */

  public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Magento\Framework\UrlInterface $urlBuilder,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Framework\App\Request\Http $request,
    \Magento\Framework\App\Config\ConfigResource\ConfigInterface $resourceConfig,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Customer\Model\CustomerFactory $customerFactory,
    \Magento\Checkout\Model\Session $checkoutSession,
    \Magento\Catalog\Model\Product $productModel,
    \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
    \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
    \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
    \Magento\Catalog\Helper\Image $imageHelper
    )
    {
      $this->_scopeConfig = $scopeConfig;
      $this->_resourceConfig = $resourceConfig;
      $this->_urlBuilder = $urlBuilder;
      $this->_storeManager = $storeManager;
      $this->_request = $request;
      $this->_customerSession = $customerSession;
      $this->_customerFactory = $customerFactory;
      $this->_groupRepository = $groupRepository;
      $this->_checkoutSession = $checkoutSession;
      $this->_productModel = $productModel;
      $this->_orderCollectionFactory = $orderCollectionFactory;
      $this->_categoryRepository = $categoryRepository;
      $this->_imageHelper = $imageHelper;

    }

    // Save config in store
    public function setStoreConfig($key, $value)
    {
      $this->_resourceConfig->saveConfig(
        self::CONFIG_BASE_PATH . $key,
        $value,
        \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        \Magento\Store\Model\Store::DEFAULT_STORE_ID
      );
    }
    //Check is store config
    public function isStoreConfigured()
    {
      return $this->getStoreConfig('authkey') && $this->getStoreConfig('siteid');
    }
    //Check browser language
    public function getBrowserLanguage()
    {
      if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        foreach (explode(",", strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'])) as $accept) {
          if (preg_match("!([a-z-]+)(;q=([0-9\\.]+))?!", trim($accept), $found)) {
            $langs[] = $found[1];
            $quality[] = (isset($found[3]) ? (float) $found[3] : 1.0);
          }
        }
        // Order the codes by quality
        array_multisort($quality, SORT_NUMERIC, SORT_DESC, $langs);
        // get list of stores and use the store code for the key
        // $stores = $this->_storeManager->getStore(false, true);??
        // iterate through languages found in the accept-language header
        foreach ($langs as $lang) {
          $lang = substr($lang, 0, 2);
          return $lang;
        }
      }
      return null;
    }

    // Get customer group name
    public function getCustomerGroupName($email)
    {
      $groupName = 'not logged in';
      if ($customer = $this->_customerSession->getCustomer()) {
        $groupId = $customer->getGroupId();
        $groupName = $this->_groupRepository->getById($customer->getGroupId());
      } elseif ($email && $email !== '') {
        $customer = $this->_customerFactory;
        $customer->setWebsiteId($this->getStoreConfig()->getWebsiteId());
        $customer->loadByEmail($email);
        if ($customer) {
          $groupId = $customer->getGroupId();
          $groupName = $this->_groupRepository->getById($customer->getGroupId());
        }
      }
      return strtolower($groupName->getCode());
    }

    // Check if customer has orders
    public function isNewCustomer($email)
    {
      if ($email && $email !== '') {
        // How many orders current customer have by email
        $orders = $this->_orderCollectionFactory->create()->addFieldToFilter('customer_email', $email);
        return $orders->count() === 0;
      }
      return false;
    }


    // Get store config
    public function getStoreConfig($key, $store = null)
    {
      return $this->_scopeConfig->getValue(self::CONFIG_BASE_PATH . $key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    // Register plugin
    public function registerPlugin() {
      $requestUrl = 'sites/' . $this->getStoreConfig('siteid') . '/register-plugin';

      $fields = array(
        'plugin'                => 'magento2',
        'pluginVersion'         => self::CARTSGURU_VERSION,
        //'adminUrl'              => Mage::app()->getStore($store)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) . 'cartsguru/admin?cartsguru_admin_action='
      );

      $response = $this->doPostRequest($requestUrl, $fields);
      if (!$response || $response->getStatusCode() != 200) {
        return false;
      }

      return json_decode($response->getBody());
    }

    // Register user
    public function registerNewCustomer($fields) {
      $fields['adminUrl'] = $this->_urlBuilder->getUrl('cartsguru/admin');
      $response = $this->doPostRequest('customers', $fields);
      if (!$response || $response->getStatusCode() != 200) {
        return false;
      }

      return json_decode($response->getBody());
    }

    // Send cart to the API
    public function sendCart($quote) {
      if (!$this->isStoreConfigured()) {
        return;
      }
      $cartData = $this->getCartData($quote);
      print_r($cartData); die;
      $this->doPostRequest('carts', $cartData);
    }
     /**
     * If value is empty return ''
     * @param $value
     * @return string
     */
    protected function notEmpty($value)
    {
        return ($value)? $value : '';
    }

    // Process and normalize cart data
    public function getCartData($quote) {
      //Customer data
      $address = $quote->getBillingAddress();
      $lastname = $address->getLastname();
      $firstname = $address->getFirstname();
      $email = $address->getEmail();
      $phone = $address->getTelephone();
      $country = $address->getCountryId();
      $gender = $this->genderMapping($quote->getCustomerGender());


      $custom = array(
          'language' => $this->getBrowserLanguage(),
          'customerGroup' => $this->getCustomerGroupName($email),
          'isNewCustomer' => $this->isNewCustomer($email)
      );

      //Recover link
      $recoverUrl = $this->_storeManager->getStore()->getBaseUrl() . 'cartsguru/frontend/recover?cart_id=' . $quote->getId() ;
      // $recoverUrl = ($quote->getData('cartsguru_token')) ? $this->getBaseUrl() . 'cartsguru/recovercart?cart_id=' . $quote->getId() . '&cart_token=' . $quote->getData('cartsguru_token') : '';


      //Items details
      $items = $this->getItemsData($quote);

      //Check is valid
      if (!$email || sizeof($items) == 0) {
        return;
      }

      return array(
        'siteId'        => $this->getStoreConfig('siteid'),         // SiteId is part of plugin configuration
        'id'            => $quote->getId(),                                 // Order reference, the same display to the buyer
        'creationDate'  => $this->formatDate($quote->getCreatedAt()),       // Date of the order as string in json format
        'totalET'       => (float)$quote->getSubtotal(),                    // Amount excluded taxes and excluded shipping
        'totalATI'      => $this->getTotalATI($items),                      // Amount included taxes and excluded shipping
        'currency'      => $quote->getQuoteCurrencyCode(),                  // Currency as USD, EUR
        'ip'            => $quote->getRemoteIp(),                           // User IP
        'accountId'     => $email,                                          // Account id of the buyer
        'civility'      => $gender,                                         // Use string in this list : 'mister','madam','miss'
        'lastname'      => $this->notEmpty($lastname),                      // Lastname of the buyer
        'firstname'     => $this->notEmpty($firstname),                     // Firstname of the buyer
        'email'         => $this->notEmpty($email),                         // Email of the buyer
        'phoneNumber'   => $this->notEmpty($phone),                         // Landline phone number of buyer (internationnal format)
        'countryCode'   => $this->notEmpty($country),                       // Country code of the buyer
        'recoverUrl'    => $recoverUrl,                                     // Direct link to recover the cart
        'items'         => $items,                                          // Details
        'custom'        => $custom                                          // Custom fields array
      );
    }

    function getProudctImageUrl($product) {
      $currentStore = $this->_storeManager->getStore();
      return $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getData('small_image');
    }

    /**
     * This method build items from order or quote
     * @param $obj order or quote
     * @return array
     */
    public function getItemsData($quote)
    {
        $items = array();
        if ($quote->getItems()) {
          foreach ($quote->getItems() as $item) {
              $product = $item->getProduct();
              $categoryNames = $this->getCatNames($product);
              $productData = array(
                  'url'       => $product->getProductUrl(),           // URL of product sheet
                  'imageUrl'  => $this->getProudctImageUrl($product), // URL of product image
                  'universe'  => $this->notEmpty($categoryNames[1]),  // Main category
                  'category'  => $this->notEmpty(end($categoryNames)) // Child category
              );

              $quantity = (int)$item->getQtyOrdered() > 0 ?  (int)$item->getQtyOrdered() : (int)$item->getQty();

              $items[] = array(
                  'id'        => $product->getId(),                          // SKU or product id
                  'label'     => $product->getName(),                        // Designation
                  'quantity'  => $quantity,                               // Count
                  'totalET'   => (float)$item->getPrice()*$quantity,         // Subtotal of item, taxe excluded
                  'totalATI'  => (float)$item->getPriceInclTax()*$quantity, // Subtotal of item, taxe included
                  'url'       => $productData['url'],
                  'imageUrl'  => $productData['imageUrl'],
                  'universe'  => $productData['universe'],
                  'category'  => $productData['category']
              );
          }
        }
        return $items;
    }

    /**
     * Get category names
     * @param $item
     * @return array
     */
    public function getCatNames($product)
    {
        $categoryNames = array();
        $categoryIds = $product->getCategoryIds();

        if ($categoryIds) {
          foreach ($categoryIds as $categoryId) {
              $category = $this->_categoryRepository->get($categoryId);
              $ids = explode('/', $category->getPath());
              foreach ($ids as $id) {
                  $category = $this->_categoryRepository->get($id);
                  $categoryNames[] = $category->getName();
              }
          }

          if (empty($categoryNames)) {
              $categoryNames = array(
                  0 => $this->notEmpty(null),
                  1 => $this->notEmpty(null)
              );
          }
        }
        return $categoryNames;
    }

    /**
    * This method send order data by api
    * @param $order
    */
    public function sendOrder($order)
        {
            $store = $this->getStore($order->getStoreId());

            //Check is well configured
            if (!$this->isStoreConfigured()) {
                return;
            }

            //Get data, stop if none
            $orderData = $this->getOrderData($order);
            if (empty($orderData)) {
                return;
            }

            //Push data to api
            $this->doPostRequest('/orders', $orderData);
        }

             /**
             * Map int of geder to string
             * @param $gender
             * @return string
             */
            public function genderMapping($gender)
            {
                switch ((int)$gender) {
                    case 1:
                        return 'mister';
                    case 2:
                        return 'madam';
                    default:
                        return '';
                }
            }


         /**
         * This method return order data in cartsguru format
         * @param $order
         * @return array
         */
        public function getOrderData($order)
        {
            //Order must have a status
            if (!$order->getStatus()) {
                return null;
            }

            //Customer data
            $gender = $this->genderMapping($order->getCustomerGender());
            $email = $order->getCustomerEmail();

            //Address
            $address = $order->getBillingAddress();

            //Items details
            $items = $this->getItemsData($quote);

            // Custom fields
            $custom = array(
                'language' => $this->getBrowserLanguage(),
                'customerGroup' => $this->getCustomerGroupName($email),
                'isNewCustomer' => $this->isNewCustomer($email)
            );
            // We do this to include the discounts in the totalET
            $totalET = number_format((float)($order->getGrandTotal() - $order->getShippingAmount() - $order->getTaxAmount()), 2);

            return array(
                'siteId'        => $this->getStoreConfig('siteid'),                         // SiteId is part of plugin configuration
                'id'            => $order->getIncrementId(),                                        // Order reference, the same display to the buyer
                'creationDate'  => $this->formatDate($order->getCreatedAt()),                       // Date of the order as string in json format
                'cartId'        => $order->getQuoteId(),                                            // Cart identifier, source of the order
                'totalET'       => $totalET,                                                        // Amount excluded taxes and excluded shipping
                'totalATI'      => (float)$order->getGrandTotal(),                                  // Paid amount
                'currency'      => $order->getOrderCurrencyCode(),                                  // Currency as USD, EUR
                'paymentMethod' => $order->getPayment()->getMethodInstance()->getTitle(),           // Payment method label
                'state'         => $order->getStatus(),                                             // raw order status
                'accountId'     => $email,                                                          // Account id of the buyer
                'ip'            => $order->getRemoteIp(),                                           // User IP
                'civility'      => $this->notEmpty($gender),                                        // Use string in this list : 'mister','madam','miss'
                'lastname'      => $this->notEmpty($address->getLastname()),                        // Lastname of the buyer
                'firstname'     => $this->notEmpty($address->getFirstname()),                       // Firstname of the buyer
                'email'         => $this->notEmpty($email),                                         // Email of the buye
                'phoneNumber'   => $this->notEmpty($address->getTelephone()),                       // Landline phone number of buyer (internationnal format)
                'countryCode'   => $this->notEmpty($address->getCountryId()),                       // Country code of buyer
                'items'         => $items,                                                          // Details
                'custom'        => $custom                                                          // Custom fields array
            );
        }

    /**
    * This method format date in json format
    * @param $date
    * @return bool|string
    */
    protected function formatDate($date)
    {
      return date('Y-m-d\TH:i:sP', strtotime($date));
    }

    /**
    * This method calculate total taxes included, shipping excluded
    * @param $obj order or quote
    * @return float
    */
    public function getTotalATI($items)
    {
      $totalATI = (float)0;

      foreach ($items as $item) {
        $totalATI += $item['totalATI'];
      }

      return $totalATI;
    }

    //Send data on api path
    private function doPostRequest($apiPath, $fields)
    {
      $client = new \Zend\Http\Client(self::API_BASE_URL . $apiPath);
      $headers = array(
        'x-plugin-version' => self::CARTSGURU_VERSION,
        'Accept' => 'application/json',
        'Content-Type' => 'application/json'
      );
      // Set auth key if present
      if ($this->getStoreConfig('authkey')){
        $headers['x-auth-key'] = $this->getStoreConfig('authkey');
      }

      $client
      ->setHeaders($headers)
      ->setOptions([
        'adapter'   => 'Zend\Http\Client\Adapter\Curl',
        'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
        'maxredirects' => 0,
        'timeout' => 30
      ])
      ->setMethod('POST')
      ->setRawBody(\Zend\Json\Json::encode($fields));

      $response = $client->send();

      return $response;
    }
  }
