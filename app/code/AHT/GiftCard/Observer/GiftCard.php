<?php
namespace AHT\GiftCard\Observer;

use Magento\Framework\Event\Observer;
use AHT\GiftCard\Model\GiftCardFactory;
use AHT\GiftCard\Model\GiftCardHistoryFactory;
use Magento\Catalog\Model\ProductRepository ;
use AHT\GiftCard\Helper\Data;

class GiftCard implements \Magento\Framework\Event\ObserverInterface
{
    protected $_gifrCardFactory;
    protected $_productRepository;
    protected $_helperData;
    protected $giftCardHistoryFactory;
    protected $customerSession;
    protected $_checkoutSession;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        GiftCardFactory $gifrCardFactory,
        GiftCardHistoryFactory $giftCardHistoryFactory,
        ProductRepository $_productRepository,
        Data $_helperData,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->_helperData = $_helperData;
        $this->giftCardHistoryFactory = $giftCardHistoryFactory;
        $this->_productRepository = $_productRepository;
        $this->_gifrCardFactory = $gifrCardFactory;
        $this ->customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
    }

    public function execute(Observer $observer)
    {
    
        $giftCard = $this->_gifrCardFactory->create();
        $giftCardHistory = $this->giftCardHistoryFactory->create();
        $quote = $observer->getEvent()->getQuote();
        $qtiVitural = $quote->getData('virtual_items_qty');
        $order = $observer->getEvent()->getOrder();
        $itemOder = $order->getItems();
        $id = $this->customerSession->getCustomer()->getId();
        if(!empty($this->_checkoutSession->getCodeCustom())){
            $dataUpdate = [
                'balance' => $this->_checkoutSession->getBalance(),
                'amount_use' => 30
            ];
            $collection = $giftCard->getCollection()->addFieldToFilter('code', $this->_checkoutSession->getCodeCustom())->getFirstItem();
            $giftCard->load($collection['giftcard_id']);
            $giftCard->addData($dataUpdate);
            $giftCard->save();
            $newData2 = [
               'giftcard_id' =>  $collection['giftcard_id'],
               'customer_id' => $id,
               'action' => 'use for order #'.$quote->getData('reserved_order_id')
            ];
            $giftCardHistory->addData($newData2);
            $giftCardHistory->save();
        }

        foreach ($itemOder as $items) {
            $typeProduct = $items->getProductType();
            $idProduct = $items->getProductId();
            if ($typeProduct=='virtual'){   
                $productCollection  = $this->_productRepository->getById($idProduct);
                $productAmount = $productCollection->getData('giftcard_amount');
                $lengthCode = $this->_helperData->getCodeConfig('length'); 
                if($productAmount != null){
                    for($i =0; $i < $qtiVitural; $i++){
                        $giftCode = $this->_helperData->generateGiftCode($lengthCode);
                        $newData = [  
                            'balance' => $productAmount,
                            'code' => $giftCode,
                            'create_from'=>'#'.$quote->getData('reserved_order_id')
                        ];
                         $giftCard->addData($newData);
                         $giftCard->save();
                         $collection = $giftCard->getCollection()->addFieldToFilter('code', $giftCode)->getFirstItem();
                         $newData2 = [
                            'giftcard_id' =>  $collection['giftcard_id'],
                            'customer_id' => $id,
                            'action' => 'create #'.$quote->getData('reserved_order_id')
                         ];
                         $giftCardHistory->addData($newData2);
                         $giftCardHistory->save();
                    }
                }
            }
        }
        $this->_checkoutSession->unsCodeCustom();
    }
}