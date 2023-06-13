<?php
namespace Mageplaza\GiftCard\Model\Total;


class Fee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
   
    protected $_priceCurrency;
    protected $_productRepository;
    protected $_giftCard;
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Mageplaza\GiftCard\Model\ResourceModel\GiftCard\CollectionFactory $giftCard
    ){
        $this->_priceCurrency = $priceCurrency;
        $this->_productRepository = $productCollectionFactory;
        $this->_giftCard = $giftCard;
        
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
            $code = $quote->getCouponCode();
            $collection = $this->_giftCard->create();  
            $collection->addFieldToFilter('code',['eq'=>$code]);
            // if(isset($collection)){
                // $balance = $collection->getData();
                // foreach ($balance as $value) {
                //     $baseDiscount = $value['balance'];
                // }
                $baseDiscount = 10;
                $discount =  $this->_priceCurrency->convert($baseDiscount);
                $total->addTotalAmount('customdiscount', -$discount);
                $total->addBaseTotalAmount('customdiscount', -$baseDiscount);
                $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseDiscount);
                $quote->setCustomDiscount(-$discount);
                return $this;
            // }
    }
}
