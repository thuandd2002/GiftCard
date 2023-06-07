<?php
namespace Mageplaza\GiftCard\Model\ResourceModel\GiftCardHistory;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'history_id';
    protected $_eventPrefix = 'mageplaza_giftcard_gift_card_history_collection';
    protected $_eventObject = 'gift_card_history_collection';
    protected function _construct()
    {
        $this->_init('Mageplaza\GiftCard\Model\GiftCardHistory', 'Mageplaza\GiftCard\Model\ResourceModel\GiftCardHistory');
    }
    public function a()
    {
        $this->sales_order_table = "main_table";
        $this->sales_order_payment_table = $this->getTable("sales_order_payment");
        $second_table_name = $this->getTableName('giftcard_code'); 
        $third_table_name = $this->getTableName('customer_entity'); 
        $this->getSelect()->$this->join(array('giftcard_code' => $second_table_name),
        'main_table.giftcard_id = giftcard_code.giftcard_id')->join(array('customer_entity'=>$third_table_name),'main_table.customer_id=customer_entity.entity_id');
    }
}
