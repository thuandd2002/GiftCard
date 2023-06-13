<?php
namespace Mageplaza\GiftCard\Preference\Checkout\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Psr\Log\LoggerInterface;

class Session extends \Magento\Checkout\Model\Session
{
    const CHECKOUT_STATE_BEGIN = 'begin';
    
    protected $_quote;
    protected $_customer;
    protected $_loadInactive = false;
    protected $_order;
    protected $_orderFactory;
    protected $_customerSession;
    protected $quoteRepository;
    protected $_remoteAddress;
    protected $_eventManager;
    protected $_storeManager;
    protected $customerRepository;
    protected $quoteIdMaskFactory;
    protected $isQuoteMasked;
    protected $quoteFactory;
    
    private $isLoading = false;
    private $logger;
    
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        LoggerInterface $logger = null)
    {
        $this->_orderFactory = $orderFactory;
        $this->_customerSession = $customerSession;
        $this->quoteRepository = $quoteRepository;
        $this->_remoteAddress = $remoteAddress;
        $this->_eventManager = $eventManager;
        $this->_storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteFactory = $quoteFactory;
        parent::_construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState
        );
        $this->logger = $logger ?: ObjectManager::getInstance()
            ->get(LoggerInterface::class);
    }
    
    public function setCustomerData($customer)
    {
        $this->_customer = $customer;
        return $this;
    }
    
    public function hasQuote()
    {
        return isset($this->_quote);
    }
    
    public function setLoadInactive($load = true)
    {
        $this->_loadInactive = $load;
        return $this;
    }
    
    public function getQuote()
    {
        $this->_eventManager->dispatch('custom_quote_process', ['checkout_session' => $this]);

        if ($this->_quote === null)
        {
            if ($this->isLoading)
            {
                throw new \LogicException("Infinite loop detected, review the trace for the looping path");
            }
            $this->isLoading = true;
            $quote = $this->quoteFactory->create();
            if ($this->getQuoteId())
            {
                try
                {
                    if ($this->_loadInactive)
                    {
                        $quote = $this->quoteRepository->get($this->getQuoteId());
                    }
                    else
                    {
                        $quote = $this->quoteRepository->getActive($this->getQuoteId());
                    }

                    $customerId = $this->_customer
                        ? $this->_customer->getId()
                        : $this->_customerSession->getCustomerId();

                    if ($quote->getData('customer_id') && (int)$quote->getData('customer_id') !== (int)$customerId)
                    {
                        $quote = $this->quoteFactory->create();
                        $this->setQuoteId(null);
                    }
                    
                    if ($quote->getQuoteCurrencyCode() != $this->_storeManager->getStore()->getCurrentCurrencyCode())
                    {
                        $quote->setStore($this->_storeManager->getStore());
                        $this->quoteRepository->save($quote->collectTotals());
                        $quote = $this->quoteRepository->get($this->getQuoteId());
                    }

                    if ($quote->getTotalsCollectedFlag() === false)
                    {
                        // $quote->collectTotals(); 
                        
                        /* Here, $quote->collectTotals(); generates error because of recursive call so, by commenting $quote->collectTotals(); or whole if condition it will not go in infinite and code will work properly. Actually there is no need of $quote->collectTotals(); in this file. */
                    }
                }
                catch (NoSuchEntityException $e)
                {
                    $this->setQuoteId(null);
                }
            }

            if (!$this->getQuoteId())
            {
                if ($this->_customerSession->isLoggedIn() || $this->_customer)
                {
                    $quoteByCustomer = $this->getQuoteByCustomer();
                    if ($quoteByCustomer !== null)
                    {
                        $this->setQuoteId($quoteByCustomer->getId());
                        $quote = $quoteByCustomer;
                    }
                }
                else
                {
                    $quote->setIsCheckoutCart(true);
                    $quote->setCustomerIsGuest(1);
                    $this->_eventManager->dispatch('checkout_quote_init', ['quote' => $quote]);
                }
            }

            if ($this->_customer)
            {
                $quote->setCustomer($this->_customer);
            }
            elseif ($this->_customerSession->isLoggedIn())
            {
                $quote->setCustomer($this->customerRepository->getById($this->_customerSession->getCustomerId()));
            }

            $quote->setStore($this->_storeManager->getStore());
            $this->_quote = $quote;
            $this->isLoading = false;
        }

        if (!$this->isQuoteMasked() && !$this->_customerSession->isLoggedIn() && $this->getQuoteId())
        {
            $quoteId = $this->getQuoteId();
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quoteId, 'quote_id');
            if ($quoteIdMask->getMaskedId() === null)
            {
                $quoteIdMask->setQuoteId($quoteId)->save();
            }
            $this->setIsQuoteMasked(true);
        }

        $remoteAddress = $this->_remoteAddress->getRemoteAddress();
        if ($remoteAddress)
        {
            $this->_quote->setRemoteIp($remoteAddress);
            $xForwardIp = $this->request->getServer('HTTP_X_FORWARDED_FOR');
            $this->_quote->setXForwardedFor($xForwardIp);
        }

        return $this->_quote;
    }
    
    protected function _getQuoteIdKey()
    {
        return 'quote_id_' . $this->_storeManager->getStore()->getWebsiteId();
    }
    
    public function setQuoteId($quoteId)
    {
        $this->storage->setData($this->_getQuoteIdKey(), $quoteId);
    }
    
    public function getQuoteId()
    {
        return $this->getData($this->_getQuoteIdKey());
    }
    
    public function loadCustomerQuote()
    {
        if (!$this->_customerSession->getCustomerId())
        {
            return $this;
        }

        $this->_eventManager->dispatch('load_customer_quote_before', ['checkout_session' => $this]);

        try
        {
            $customerQuote = $this->quoteRepository->getForCustomer($this->_customerSession->getCustomerId());
        } 
        catch (NoSuchEntityException $e)
        {
            $customerQuote = $this->quoteFactory->create();
        }
        $customerQuote->setStoreId($this->_storeManager->getStore()->getId());

        if ($customerQuote->getId() && $this->getQuoteId() != $customerQuote->getId())
        {
            if ($this->getQuoteId())
            {
                $quote = $this->getQuote();
                $quote->setCustomerIsGuest(0);
                $this->quoteRepository->save(
                    $customerQuote->merge($quote)->collectTotals()
                );
                $newQuote = $this->quoteRepository->get($customerQuote->getId());
                $this->quoteRepository->save(
                    $newQuote->collectTotals()
                );
                $customerQuote = $newQuote;
            }

            $this->setQuoteId($customerQuote->getId());

            if ($this->_quote)
            {
                $this->quoteRepository->delete($this->_quote);
            }
            $this->_quote = $customerQuote;
        }
        else
        {
            $this->getQuote()->getBillingAddress();
            $this->getQuote()->getShippingAddress();
            $this->getQuote()->setCustomer($this->_customerSession->getCustomerDataObject())
                ->setCustomerIsGuest(0)
                ->setTotalsCollectedFlag(false)
                ->collectTotals();
            $this->quoteRepository->save($this->getQuote());
        }
        return $this;
    }
    
    public function setStepData($step, $data, $value = null)
    {
        $steps = $this->getSteps();
        if ($value === null)
        {
            if (is_array($data))
            {
                $steps[$step] = $data;
            }
        }
        else
        {
            if (!isset($steps[$step]))
            {
                $steps[$step] = [];
            }
            if (is_string($data))
            {
                $steps[$step][$data] = $value;
            }
        }
        $this->setSteps($steps);

        return $this;
    }
    
    public function getStepData($step = null, $data = null)
    {
        $steps = $this->getSteps();
        if ($step === null)
        {
            return $steps;
        }
        if (!isset($steps[$step]))
        {
            return false;
        }
        if ($data === null)
        {
            return $steps[$step];
        }
        if (!is_string($data) || !isset($steps[$step][$data]))
        {
            return false;
        }
        return $steps[$step][$data];
    }
    
    public function clearQuote()
    {
        $this->_eventManager->dispatch('checkout_quote_destroy', ['quote' => $this->getQuote()]);
        $this->_quote = null;
        $this->setQuoteId(null);
        $this->setLastSuccessQuoteId(null);
        return $this;
    }
    
    public function clearStorage()
    {
        parent::clearStorage();
        $this->_quote = null;
        return $this;
    }
    
    public function clearHelperData()
    {
        $this->setRedirectUrl(null)->setLastOrderId(null)->setLastRealOrderId(null)->setAdditionalMessages(null);
    }
    
    public function resetCheckout()
    {
        $this->setCheckoutState(self::CHECKOUT_STATE_BEGIN);
        return $this;
    }
    
    public function replaceQuote($quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }
    
    public function getLastRealOrder()
    {
        $orderId = $this->getLastRealOrderId();
        if ($this->_order !== null && $orderId == $this->_order->getIncrementId())
        {
            return $this->_order;
        }
        $this->_order = $this->_orderFactory->create();
        if ($orderId)
        {
            $this->_order->loadByIncrementId($orderId);
        }
        return $this->_order;
    }
    
    public function restoreQuote()
    {
        $order = $this->getLastRealOrder();
        if ($order->getId())
        {
            try
            {
                $quote = $this->quoteRepository->get($order->getQuoteId());
                $quote->setIsActive(1)->setReservedOrderId(null);
                $this->quoteRepository->save($quote);
                $this->replaceQuote($quote)->unsLastRealOrderId();
                $this->_eventManager->dispatch('restore_quote', ['order' => $order, 'quote' => $quote]);
                return true;
            }
            catch (NoSuchEntityException $e)
            {
                $this->logger->critical($e);
            }
        }

        return false;
    }
    
    protected function setIsQuoteMasked($isQuoteMasked)
    {
        $this->isQuoteMasked = $isQuoteMasked;
    }
    
    protected function isQuoteMasked()
    {
        return $this->isQuoteMasked;
    }
    
    private function getQuoteByCustomer(): ?CartInterface
    {
        $customerId = $this->_customer
            ? $this->_customer->getId()
            : $this->_customerSession->getCustomerId();

        try
        {
            $quote = $this->quoteRepository->getActiveForCustomer($customerId);
        }
        catch (NoSuchEntityException $e)
        {
            $quote = null;
        }

        return $quote;
    }
}