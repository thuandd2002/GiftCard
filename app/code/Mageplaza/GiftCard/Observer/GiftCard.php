<?php
namespace Mageplaza\GiftCard\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Psr\Log\LoggerInterface;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Magento\Catalog\Model\ProductRepository ;
use Mageplaza\GiftCard\Helper\Data;

class GiftCard implements \Magento\Framework\Event\ObserverInterface
{
    protected $_gifrCardFactory;
    protected $_productRepository;
    protected $_helperData;

    public function __construct(
        GiftCardFactory $gifrCardFactory,
        ProductRepository $_productRepository,
        Data $_helperData
    )
    {
        $this->_helperData = $_helperData;
        $this->_productRepository = $_productRepository;
        $this->_gifrCardFactory = $gifrCardFactory;
    }

    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $qtiVitural = $quote->getData('virtual_items_qty');
        $order = $observer->getEvent()->getOrder();
        $itemOder = $order->getItems();
        foreach ($itemOder as $items) {
            $typeProduct = $items->getProductType();

            $idProduct = $items->getProductId();
            if ($typeProduct=='virtual'){
                $productCollection  = $this->_productRepository->getById($idProduct);
                $productAmount = $productCollection->getData('giftcard_amount');
                $lengthCode = $this->_helperData->getCodeConfig('length'); 
                if($productAmount != null){
                    for($i =0; $i < $qtiVitural; $i++){
                        $giftCard = $this->_gifrCardFactory->create();
                        $giftCode = $this->_helperData->generateGiftCode($lengthCode);
                        $newData = [  
                            'balance' => $productAmount,
                            'code' => $giftCode,
                            'create_from'=>'#'.$quote->getData('reserved_order_id')
                         ];
                         $giftCard->addData($newData);
                         $giftCard->save();
                    }
                }
                
            }
        }
    }
}