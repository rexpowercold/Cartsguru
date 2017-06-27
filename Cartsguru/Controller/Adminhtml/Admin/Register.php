<?php

namespace Cartsguru\Cartsguru\Controller\Adminhtml\Admin;

class Register extends \Cartsguru\Cartsguru\Controller\Adminhtml\Cartsguru
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
        \Magento\Framework\App\Request\Http $request,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Cartsguru\Cartsguru\Helper\Data $helper
    ) {
        $this->request = $request;
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
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */

        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('Carts Guru'));

        if ($this->request->isPost()) {
          $data = $this->request->getPost()->toArray();
          $fields = array(
              'country' => $data['country_id'],
              //'state' => $data['state'],
              'website' => $data['website'],
              'phoneNumber' => $data['phonenumber'],
              //user creation
              'email'  => $data['email'],
              'lastname' => $data['lastname'],
              'firstname' => $data['firstname'],
              'password' => $data['password'],

              'plugin' => 'magento2',
              'pluginVersion' => \Cartsguru\Cartsguru\Helper\Data::CARTSGURU_VERSION
          );
          $result = $this->_cartsguru_helper->registerNewCustomer($fields);
          if ($result !== false) {
            $this->messageManager->addSuccess(__('Successfully connected'));
            return $this->_redirect('cartsguru/admin/index');
          } else {
            $this->messageManager->addError(__('Connection error'));
          }
        }
        return $resultPage;
    }
}
