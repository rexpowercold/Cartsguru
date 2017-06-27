<?php
namespace Cartsguru\Cartsguru\Block;
use Magento\Framework\View\Element\Template;

class Register extends Template
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
    \Magento\Directory\Block\Data $directoryBlock,
    array $data = []
  ) {
    $this->_helper = $dataHelper;
    $this->_directoryBlock = $directoryBlock;
    parent::__construct($context, $data);
    $this->_isScopePrivate = true;
  }

  public function getCountries()
  {
      $country = $this->_directoryBlock->getCountryHtmlSelect();
      return $country;
  }
}
