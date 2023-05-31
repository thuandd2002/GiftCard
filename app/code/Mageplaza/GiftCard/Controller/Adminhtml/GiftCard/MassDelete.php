<?php
namespace Mageplaza\GiftCard\Controller\Adminhtml\GiftCard;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use Mageplaza\GiftCard\Model\ResourceModel\GiftCard\Collection;
class MassDelete extends Action
{    
    protected $filter;
    protected $GiftCardCollection;
    public function __construct(Context $context, Filter $filter, Collection $GiftCardCollection)
    {
        $this->filter = $filter;
        $this->GiftCardCollection = $GiftCardCollection;
        parent::__construct($context);
    }
    public function execute()
    {
        $collection = $this->filter->getCollection($this->GiftCardCollection);
        $collectionSize = $collection->getSize();
        $collection->walk('delete');
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('giftcard/giftcard/index');
    }
}