<?php
namespace AHT\GiftCard\Controller\Adminhtml\GiftCard;


class Edit extends  \Magento\Backend\App\Action
{

    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
      
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // $id = $this->getRequest()->getParam('giftcard_id');
        // $model = $this->_objectManager->create(\AHT\GiftCard\Model\GiftCard::class);
        
        // // 2. Initial checking
        // if ($id) {
        //     $model->load($id);
        //     if (!$model->getId()) {
        //         $this->messageManager->addErrorMessage(__('This Giftcard Code no longer exists.'));
        //         /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        //         $resultRedirect = $this->resultRedirectFactory->create();
        //         return $resultRedirect->setPath('*/*/');
        //     }
        // }     
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("Edit giftcard"));
        return $resultPage;
        // /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        // $resultPage = $this->resultPageFactory->create();
        // $resultPage->getConfig()->getTitle()->prepend($model->getId() ? __('Edit Giftcard Code %1', $model->getId()) : __('New Giftcard Code'));
        // return $resultPage;
    }
}
