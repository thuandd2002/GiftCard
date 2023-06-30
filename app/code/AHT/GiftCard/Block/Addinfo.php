<?php
namespace AHT\GiftCard\Block;

class AddInfo extends \Magento\Framework\View\Element\Template
{
    protected $_resource;
    protected $customerSession;
    protected $_helperData;
    protected $_giftCardHistory;
    public function __construct(
        \AHT\GiftCard\Helper\Data $helperData,
        \AHT\GiftCard\Model\ResourceModel\GiftCardHistory\CollectionFactory $giftCardHistory,
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
        $second_table_name = $this->_resource->getTableName('giftcard_code'); 
        $third_table_name = $this->_resource->getTableName('customer_entity'); 
        $collection->join(array('giftcard_code' => $second_table_name),
        'main_table.giftcard_id = giftcard_code.giftcard_id')->join(array('customer_entity'=>$third_table_name),'main_table.customer_id=customer_entity.entity_id');
        $collection->addFieldToFilter('customer_id',['eq'=>$id]);
        $collection-> setOrder('history_id','DESC');
        return $collection->setPageSize(8);
    }

    public function checkEnableConfig()
    {
        $config = $this->_helperData->getGeneralConfig('enable');
        return $config;
    }
    
    public function checkAllowConfig(){
        $config = $this->_helperData->getGeneralConfig('allow');
        return $config;
    }
}

