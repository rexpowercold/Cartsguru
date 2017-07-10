<?php
/**
* Copyright © 2017 Carts Guru Ltd. All rights reserved.
* See COPYING.txt for license details.
 */
namespace Cartsguru\Cartsguru\Controller\Adminhtml\Admin;

class Index extends \Cartsguru\Cartsguru\Controller\Adminhtml\Cartsguru
{

    protected $_resultPageFactory;
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
        $this->_resultPageFactory = $resultPageFactory;
        $this->_cartsguru_helper = $helper;
        parent::__construct($context, $coreRegistry);
    }

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('Carts Guru'));

        $authkey = $this->getRequest()->getParam('authkey');
        $siteid = $this->getRequest()->getParam('siteid');
        // Check if we have post data
        if ($authkey && $siteid) {
          $this->_cartsguru_helper->setStoreConfig('authkey', $authkey);
          $this->_cartsguru_helper->setStoreConfig('siteid', $siteid);
          $result = $this->_cartsguru_helper->registerPlugin();
          if ($result !== false) {
            $this->_cartsguru_helper->setStoreConfig('apiSuccess', 1);
            $this->messageManager->addSuccess(__('Successfully connected'));
            if ($result->isNew) {
              $this->_cartsguru_helper->sendHistory();
            }
          } else {
            $this->messageManager->addError(__('Connection error'));
          }
        }
        return $resultPage;
    }
}
