<?php
/**
* Copyright © 2017 Carts Guru Ltd. All rights reserved.
* See COPYING.txt for license details.
 */
 namespace Cartsguru\Cartsguru\Block;
 use Magento\Framework\View\Element\Template;

 class Cartsguru extends Template
 {
     /**
      * @var DataHelper
      */
     protected $_helper;

     /**
      * @param Template\Context $context
      * @param DataHelper $dataHelper
      * @param array $data
      */
     public function __construct(
         Template\Context $context,
         \Cartsguru\Cartsguru\Helper\Data $dataHelper,
         array $data = []
     ) {
         $this->_helper = $dataHelper;
         parent::__construct($context, $data);
         $this->_isScopePrivate = true;
     }

     /**
      * Returns auth key
      *
      * @return array
      */
     public function getAuthkey()
     {
         return $this->_helper->getStoreConfig('authkey');
     }
     /**
     * Returns site id
     *
     * @return array
     */
     public function getSiteId()
     {
       return $this->_helper->getStoreConfig('siteid');
     }


 }
