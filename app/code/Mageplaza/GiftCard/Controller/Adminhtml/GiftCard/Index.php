<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mageplaza\GiftCard\Controller\Adminhtml\GiftCard;
use \Mageplaza\GiftCard\Helper\Data;

class Index extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;
    protected $helperData;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Data $helperData
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {   
    //  
        $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(__("GiftCard"));
        return $resultPage;
        
    }
}
