<?php

namespace Cartsguru\Cartsguru\Controller\Adminhtml\Admin;

class Index extends \Cartsguru\Cartsguru\Controller\Adminhtml\Cartsguru
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
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Cartsguru\Cartsguru\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_cartsguru_helper = $helper;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('Carts Guru'));

        $authkey = $this->getRequest()->getParam('authkey');
        $siteid = $this->getRequest()->getParam('siteid');

        // Check if we have post data
        if ($authkey && $siteid) {
          $this->_cartsguru_helper->setStoreConfig('authkey', $authkey);
          $this->_cartsguru_helper->setStoreConfig('siteid', $siteid);
          $result = $this->_cartsguru_helper->registerPlugin();
          if ($result !== false) {
            $this->messageManager->addSuccess(__('Successfully connected'));
          } else {
            $this->messageManager->addError(__('Connection error'));
          }
        }
        return $resultPage;
    }
}
