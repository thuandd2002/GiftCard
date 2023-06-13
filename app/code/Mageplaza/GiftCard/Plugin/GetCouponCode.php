<?php
namespace Mageplaza\GiftCard\Plugin;

class GetCouponCode
{
    protected $_checkoutSession;
    protected $_quote;
    protected $cart;
    public function __construct(
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $cart,
        )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_quote = $quoteFactory;
        $this->cart = $cart;
    }

    function afterGetCouponCode(\Magento\Checkout\Block\Cart\Coupon $subject)
    {
        $cartQuote = $this->cart->getQuote()->getId();
        $quote = $this->_quote->create()->load($cartQuote);
        return $quote->getCouponCode();
    }
}
