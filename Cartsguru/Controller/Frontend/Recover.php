<?php

namespace Cartsguru\Cartsguru\Controller\Frontend;

class Recover extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    protected $_cartsguru_helper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Cartsguru\Cartsguru\Helper\Data $helper
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerFactory = $customerFactory;
        $this->_cart = $cart;
        $this->_quoteFactory = $quoteFactory;
        $this->_cartsguru_helper = $helper;
        parent::__construct($context);
    }

    private function redirectToCart()
    {
        // $url = $this->getUrl('checkout/cart');
        $url = $this->_urlBuilder->getUrl('checkout/cart');

        //Keep params except cart_id & cart_token
        $queryParams = array();
        $params = $this->getRequest()->getParams();
        foreach ($params as $key => $value) {
            if ($key === 'cart_id') {
                continue;
        // foreach ($params as $key => $value) {
        //     if ($key === 'cart_token' || $key === 'cart_id') {
        //         continue;
            }
            $queryParams[] = $key . '=' . $value;
        }

        //Concats query
        if (!empty($queryParams)) {
            $url .= strpos($url, '?') !== false ? '&' : '?';
            $url .= implode('&', $queryParams);
        }

        $this->getResponse()->setRedirect($url)->sendResponse();
    }

    public function execute()
    {
        // Get request params
        $params = $this->getRequest()->getParams();

        // Stop if no enoguth params
        if (!isset($params['cart_id'])) {
            return $this->redirectToCart();
        // if (!isset($params['cart_id']) || !isset($params['cart_token'])) {
        //     return $this->redirectToCart();
        }

        // Load quote by id
        // $quote = Mage::getModel('sales/quote')->load($params['cart_id']);
        $quote = $this->_quoteFactory->create()->load($params['cart_id']);

        // Stop if quote does not exist
        if (!$quote->getId()) {
            return $this->redirectToCart();
        }

        // Check quote token
        // $token = $quote->getData('cartsguru_token');
        // if (!$token || $token != $params['cart_token']) {
        //     return $this->redirectToCart();
        // }

        // Auto log customer if we can
        if ($quote->getCustomerId()) {
            //Gest customer
            $this->_customerFactory->create()->load($quote->getCustomerId());

            $this->_customerSession->setCustomerAsLoggedIn($customer);
        } else {
            // Get current cart
            $cart = $this->_cart;


            foreach ($cart->getQuote()->getAllVisibleItems() as $item) {
                $found = false;
                foreach ($quote->getAllItems() as $quoteItem) {
                    if ($quoteItem->compare($item)) {
                        //  $quoteItem->setQty($item->getQty());
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $newItem = clone $item;
                    $quote->addItem($newItem);
                    if ($quote->getHasChildren()) {
                        foreach ($item->getChildren() as $child) {
                            $newChild = clone $child;
                            $newChild->setParentItem($newItem);
                            $quote->addItem($newChild);
                        }
                    }
                }
            }

            $quote->save();
            $cart->setQuote($quote);
            // $cart->init();
            $cart->save();
        }

        // Redirect to checkout
        return $this->redirectToCart();
    }

}
