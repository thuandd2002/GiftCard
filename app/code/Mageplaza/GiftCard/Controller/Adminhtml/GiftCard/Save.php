<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mageplaza\GiftCard\Controller\Adminhtml\GiftCard;

use Magento\Framework\Exception\LocalizedException;
use \Mageplaza\GiftCard\Helper\Data;
class Save extends \Magento\Backend\App\Action
{
    protected $_helperData ;
    protected $dataPersistor;
    protected $giftCardFactory;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
         Data $helperData,
        \Mageplaza\GiftCard\Model\GiftCardFactory $giftCardFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->_helperData = $helperData;
        $this->giftCardFactory = $giftCardFactory;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if(isset($data['length'])){
            $length = $data['length'];
        }else{
            $length=$this->_helperData->getCodeConfig('length');
        }
        $codeRand =  $this->_helperData->generateGiftCode($length);
        if(isset($data['code'])){
            $code = $data['code'];
        }else{
            $code = $codeRand;
        }
        $id = !empty($data['giftcard_id']) ? $data['giftcard_id'] : null;
        $newData = [  
            'balance' => $data['balance'],
            'code' => $code,
        ];
        $giftCard = $this->giftCardFactory->create();
        if($id){
            $giftCard->load($id);
        }try {
            $giftCard->addData($newData);
            $giftCard->save();
            $this->messageManager->addSuccessMessage(__('You saved the GiftCard.'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        return $this->resultRedirectFactory->create()->setPath('giftcard/giftcard/index');
    }
}