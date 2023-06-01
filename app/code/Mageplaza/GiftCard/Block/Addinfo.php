<?php
namespace Mageplaza\GiftCard\Block;

class AddInfo extends \Magento\Framework\View\Element\Template
{
    protected $_resource;
    protected $customerSession;
    protected $_helperData;
    protected $_giftCardHistory;
    public function __construct(
        \Mageplaza\GiftCard\Helper\Data $helperData,
        \Mageplaza\GiftCard\Model\ResourceModel\GiftCardHistory\CollectionFactory $giftCardHistory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\ResourceConnection $Resource,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->_resource = $Resource;
        $this->_helperData = $helperData;
        $this->_giftCardHistory = $giftCardHistory;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getGiftCardByIdCustumer(){
        $id = $this->customerSession->getCustomer()->getId();
        $collection = $this->_giftCardHistory->create();
        // $history = $historyColection->addFieldToSelect('*')->addFieldToFilter('customer_id', ['eq' => $id])->getCollection();
        $second_table_name = $this->_resource->getTableName('giftcard_code'); 
        $collection->join(array('giftcard_code' => $second_table_name),
        'main_table.giftcard_id = giftcard_code.giftcard_id');
        // return $history;
        return $collection;
    }

    public function checkEnableConfig()
    {
        $config = $this->_helperData->getGeneralConfig('enable');
        return $config;
    }
    
    public function checkAllowConfig(){
        $config = $this->_helperData->getGeneralConfig('used');
        return $config;
    }
}

