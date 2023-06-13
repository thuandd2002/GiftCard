<?php

namespace Mageplaza\GiftCard\Plugin;

class CouponPost
{

    protected $_escaper;
    protected $resultFactory;
    protected $giftCard;
    protected $couponFactory;
    protected $cart;
    protected $quoteRepository;
    protected $_checkoutSession;
    protected $_messageManager;
    public function __construct(
        \Mageplaza\GiftCard\Model\ResourceModel\GiftCard\CollectionFactory $giftCard,
        \Magento\Framework\Escaper $_escaper,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->giftCard = $giftCard;
        $this->resultFactory = $resultFactory;
        $this->couponFactory = $couponFactory;
        $this->_escaper=$_escaper;
        $this->quoteRepository = $quoteRepository;
        $this->cart = $cart;
        $this->_messageManager = $messageManager;
        $this->_checkoutSession = $checkoutSession;
    }

    function aroundExecute(\Magento\Checkout\Controller\Cart\CouponPost $object, callable $proceed)
    {
       
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl('/checkout/cart/');

        $couponCode = $object->getRequest()->getParam('remove') == 1
            ? ''
            : trim($object->getRequest()->getParam('coupon_code', ''));

        // $cartQuote = $this->cart->getQuote();
        // $oldCouponCode = $cartQuote->getCouponCode() ?? '';
        // $codeLength = strlen($couponCode);
        // if (!$codeLength && !strlen($oldCouponCode)) {
        //     return $redirect;
        // }
        $colection =  $this->giftCard->create();
        $giftCard = $colection->addFieldToFilter('code', ['eq' => $couponCode])->getData();
        $id ="";
        foreach ($giftCard as $items) {
            $id = $items['giftcard_id'];
        }
        if ($id!=null) {
            $this->_checkoutSession->getQuote()->setCouponCode($couponCode)->save();
            $this->_checkoutSession->setCodeCustom($couponCode);
            $this->_messageManager->addSuccessMessage(
                __(
                    'You used coupon code "%1".',
                    $this->_escaper->escapeHtml($couponCode)
                )
            );
            return $redirect;
        }else{
            $this->_checkoutSession->unsCodeCustom();
            $result = $proceed();
            return $result;
        }    
    }
}
