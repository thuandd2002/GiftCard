<?php
namespace AHT\GiftCard\Model\ResourceModel\GiftCard;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'giftcard_id';
    protected $_eventPrefix = 'aht_giftcard_gift_card_collection';
    protected $_eventObject = 'gift_card_collection';

    /**
     * Define the resource model & the model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('AHT\GiftCard\Model\GiftCard', 'AHT\GiftCard\Model\ResourceModel\GiftCard');
    }
}
