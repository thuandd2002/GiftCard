<?php
namespace Mageplaza\GiftCard\Model\ResourceModel\GiftCard;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'giftcard_id';
    protected $_eventPrefix = 'mageplaza_giftcard_gift_card_collection';
    protected $_eventObject = 'gift_card_collection';

    /**
     * Define the resource model & the model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mageplaza\GiftCard\Model\GiftCard', 'Mageplaza\GiftCard\Model\ResourceModel\GiftCard');
    }
}
