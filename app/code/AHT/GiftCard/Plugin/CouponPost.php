<?php

namespace AHT\GiftCard\Plugin;

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
    protected $_helper;
    public function __construct(
        \AHT\GiftCard\Model\ResourceModel\GiftCard\CollectionFactory $giftCard,
        \Magento\Framework\Escaper $_escaper,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \AHT\GiftCard\Helper\Data $_helper,
    ) {
        $this->giftCard = $giftCard;
        $this->resultFactory = $resultFactory;
        $this->couponFactory = $couponFactory;
        $this->_escaper=$_escaper;
        $this->quoteRepository = $quoteRepository;
        $this->cart = $cart;
        $this->_messageManager = $messageManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $_helper;
    }

    function aroundExecute(\Magento\Checkout\Controller\Cart\CouponPost $object, callable $proceed)
    {
       
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl('/checkout/cart/');
        $couponCode = $object->getRequest()->getParam('remove') == 1
            ? ''
            : trim($object->getRequest()->getParam('coupon_code', ''));
        $colection =  $this->giftCard->create();
        $giftCard = $colection->addFieldToFilter('code', ['eq' => $couponCode])->getData();
        $id ="";
        foreach ($giftCard as $items) {
            $id = $items['giftcard_id'];
        }
        $configuration = $this->_helper->getGeneralConfig('used');
        if ($id!=null) {
           if($configuration == 1){
            $this->_checkoutSession->getQuote()->setCouponCode($couponCode)->save();
            $this->_checkoutSession->setCodeCustom($couponCode);
            $this->_messageManager->addSuccessMessage(
                __(
                    'You used coupon code "%1".',
                    $this->_escaper->escapeHtml($couponCode)
                )
            );
            return $redirect;
           }else {
            $this->_messageManager->addErrorMessage(
                __(
                    'Admin has disabled this feature',
                )
            );
            return $redirect;
           }
           
        }else{
            $this->_checkoutSession->unsCodeCustom();
            $result = $proceed();
            return $result;
        }    
    }
}
