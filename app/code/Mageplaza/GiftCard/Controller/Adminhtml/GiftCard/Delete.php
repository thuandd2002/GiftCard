<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mageplaza\GiftCard\Controller\Adminhtml\GiftCard;

class Delete extends \Mageplaza\GiftCard\Controller\Adminhtml\GiftCard
{
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('giftcard_id');
        if ($id) {
            try {
                $model = $this->_objectManager->create(\Mageplaza\GiftCard\Model\GiftCard::class)->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the Giftcard.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['giftcard_id' => $id]);
            }
        }    
        $this->messageManager->addErrorMessage(__('We can\'t find a Giftcard to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
