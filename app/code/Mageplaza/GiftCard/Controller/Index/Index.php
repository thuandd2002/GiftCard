<?php
namespace Mageplaza\GiftCard\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;
use Symfony\Component\VarDumper\VarDumper;

class Index extends Action {

    protected $_helperData;
    private $pageFactory;
    public function __construct(
        \Mageplaza\GiftCard\Helper\Data $helperData,
        Context $context,
        PageFactory $pageFactory
    )
    {
        $this->_helperData = $helperData;
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }
    public function execute()
    {
        $this->_view->loadLayout();
        // $config = $this->_helperData->getGeneralConfig('enable');

        // if ($config==1) {
        //     $this->_view->getPage()->getConfig()->getTitle()->set(__(''));
        // }
        // else {
        //     $this->_view->getPage()->getConfig()->getTitle()->set(__('aaaaaaa'));
        // }
        // $this->_view->renderLayout();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('My Gift Card'));
        $this->_view->renderLayout();
    }
}
