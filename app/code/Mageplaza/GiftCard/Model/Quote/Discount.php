<?php

namespace Mageplaza\GiftCard\Model\Quote;

class Discount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    protected $priceCurrency;
    protected $_giftCard;
    protected $_checkoutSession;
    protected $cart;
    public function __construct(
        \Mageplaza\GiftCard\Model\ResourceModel\GiftCard\CollectionFactory $giftCard,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $cart,
    ) {
        $this->setCode('testdiscount');
        $this->cart = $cart;
        $this->_giftCard = $giftCard;
        $this->priceCurrency = $priceCurrency;
        $this->_checkoutSession = $checkoutSession;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $label = 'My Custom Discount';
        $code =  $this->_checkoutSession->getCodeCustom();
        $TotalAmount = 0;
        if(isset($code)){
            $giftCard =  $this->_giftCard->create();
            $items = $giftCard->addFieldToFilter('code',['eq'=>$code])->getData();
            if (!empty($items)) {
                foreach ($items as $item){
                    $TotalAmount = $this->priceCurrency->convert($item['balance']);
                }
            }else{
                $TotalAmount = $total->getSubtotal();   
                $TotalAmount = 0;
            }
        }     
        $discountAmount = "-" . $TotalAmount;
        $appliedCartDiscount = 0;
        if ($total->getDiscountDescription()) {
            $appliedCartDiscount = $total->getDiscountAmount();
            $discountAmount = $total->getDiscountAmount() + $discountAmount;
            $label = $total->getDiscountDescription() . ', ' . $label;
        }
        $total->setDiscountDescription($label);
        $total->setDiscountAmount($discountAmount);
        $total->setBaseDiscountAmount($discountAmount);
        $total->setSubtotalWithDiscount($total->getSubtotal() + $discountAmount);
        $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() + $discountAmount);

        if (isset($appliedCartDiscount)) {
            $total->addTotalAmount($this->getCode(), $discountAmount - $appliedCartDiscount);
            $total->addBaseTotalAmount($this->getCode(), $discountAmount - $appliedCartDiscount);
        } else {
            $total->addTotalAmount($this->getCode(), $discountAmount);
            $total->addBaseTotalAmount($this->getCode(), $discountAmount);
        }
        return $this;
    }

    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        $amount = $total->getDiscountAmount();

        if ($amount != 0) {
            $result = [
                'code' => $this->getCode(),
                'value' => $amount
            ];
        }
        return $result;
    }
}
