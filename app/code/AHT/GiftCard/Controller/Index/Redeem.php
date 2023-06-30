<?php
namespace AHT\GiftCard\Controller\Index;
use Magento\Customer\Api\CustomerRepositoryInterface;
class Redeem extends \Magento\Framework\App\Action\Action
{


    protected $giftCardFactory;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $customerRepository;
    protected $_pageFactory;
    protected $_customer;
    protected $customerSession;
    protected $giftCardCollectionFactory;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        \AHT\GiftCard\Model\ResourceModel\GiftCard\CollectionFactory $giftCardCollectionFactory,
        \AHT\GiftCard\Model\GiftCardFactory $giftCardFactory,
       \Magento\Framework\App\Action\Context $context,
       \Magento\Framework\View\Result\PageFactory $pageFactory,
       \Magento\Customer\Model\CustomerFactory $customers,
       \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->giftCardFactory = $giftCardFactory;
        $this->giftCardCollectionFactory = $giftCardCollectionFactory;
        $this->_pageFactory = $pageFactory;
        $this->_customer = $customers;
        return parent::__construct($context);
    }
    /**
     * View page action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $customerSession= $this->customerSession->getCustomer();
        $idCustomer= $customerSession->getId();
        if($idCustomer){
            $customerBalance = $customerSession->getData('giftcard_balance');
            $data = $this->getRequest()->getPostValue();
            $codeRedem = $data['code'];
            $giftCardCollection = $this->giftCardCollectionFactory->create();
            $colect =  $giftCardCollection->addFieldToFilter('code',array('eq' =>$codeRedem))->getData();
            if($colect){
                foreach($colect as $item){
                    $customerBalance += $item['balance'];
                    $item['balance'] = 0;
                }
                $dataCustomer = ['giftcard_balance'=>'8'];
                $dataGiftCard = ['balance'=>$item['balance']];
                $customer = $this->_customer->create();
                $giftCard = $this->giftCardFactory->create();

                    $customer =   $this->customerRepository->getById($idCustomer);
                $customer->setData('giftcard_balance','8');
//                    $customer->addData($dataCustomer);
                    // // $customer->getResource()->getMainTable();
                    // dd($customer->getResource());
                    // $model = $this->customerRepository;

                    $this->customerRepository->save($customer);
                    $giftCard->load($item['giftcard_id']);
                    $giftCard->addData($dataGiftCard);
                    $giftCard->save();

            }
            else{
                dd('faild');
            }
        }else{
            echo ("Error! Vui long dang nhap");
        }
        return $this->resultRedirectFactory->create()->setPath('addinfo/index/index');
    }
}
