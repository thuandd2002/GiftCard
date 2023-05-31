<?php
namespace Mageplaza\GiftCard\Model\ResourceModel\GiftCardHistory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'history_id';
    protected $_eventPrefix = 'mageplaza_giftcard_gift_card_history_collection';
    protected $_eventObject = 'gift_card_history_collection';

    /**
     * Define the resource model & the model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mageplaza\GiftCard\Model\GiftCardHistory', 'Mageplaza\GiftCard\Model\ResourceModel\GiftCard');
    }
}
