<?php

namespace Cartsguru\Cartsguru\Controller\Frontend;

class Index extends \Magento\Framework\App\Action\Action
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
        \Cartsguru\Cartsguru\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_cartsguru_helper = $helper;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
      // Get request params
      $params = $this->getRequest()->getParams();
      // Stop if no email
      if (!isset($params['email'])) {
          return;
      }
      // Post the data
      $quote = $this->_checkoutSession->getQuote();
      $address = $quote->getBillingAddress();
      if ($address) {
        if (isset($params['email'])) {
            $address->setEmail($params['email']);
        }
        if (isset($params['firstname'])) {
            $address->setFirstname($params['firstname']);
        }
        if (isset($params['lastname'])) {
            $address->setLastname($params['lastname']);
        }
        if (isset($params['telephone'])) {
            $address->setTelephone($params['telephone']);
        }
        if (isset($params['country'])) {
            $address->setCountryId($params['country']);
        }
        $this->_cartsguru_helper->sendCart($quote);
      }
    }
}
