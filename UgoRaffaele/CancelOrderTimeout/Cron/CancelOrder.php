<?php
namespace UgoRaffaele\CancelOrderTimeout\Cron;

class CancelOrder {
	
	protected $scopeConfig;
	protected $orderRepository;
	protected $searchCriteriaBuilder;
	protected $orderManagement;
	protected $date;
	protected $logger;
	
	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
		\Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
		\Magento\Sales\Api\OrderManagementInterface $orderManagement,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Psr\Log\LoggerInterface $logger
    ){
		$this->scopeConfig = $scopeConfig;
		$this->orderRepository = $orderRepository;
		$this->searchCriteriaBuilder = $searchCriteriaBuilder;
		$this->orderManagement = $orderManagement;
		$this->date = $date;
		$this->logger = $logger;
    }
	
	public function isModuleEnabled()
	{
		$moduleEnabled = $this->scopeConfig->getValue('cancelordertimeout/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		return $moduleEnabled;
	}
	
	public function getTimeout()
	{
		$timeout = $this->scopeConfig->getValue('cancelordertimeout/general/timeout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		return intval($timeout);
	}
	
	public function getPaymentMethods()
	{
		$paymentMethods = $this->scopeConfig->getValue('cancelordertimeout/general/payment_methods', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		return $paymentMethods;
	}
	
	public function execute()
	{
		
		if ($this->isModuleEnabled()) {
			
			$methods = $this->getPaymentMethods();
			$methods = explode(',', $methods);
			
			$agoDate = $this->date->gmtDate(null, strtotime("-{$this->getTimeout()} minutes"));
			
			$searchCriteria = $this->searchCriteriaBuilder
				->addFilter('created_at', $agoDate, 'lt')
				->addFilter('status', 'pending', 'eq')
				->create();
			$orders = $this->orderRepository->getList($searchCriteria);
		
			foreach ($orders->getItems() as $order) {
				
				$paymentMethod = $order->getPayment()->getMethodInstance()->getCode();

				if (in_array($paymentMethod, $methods)) {
					$this->logger->info("Cancelling Order # {$order->getEntityId()}");
					$this->orderManagement->cancel($order->getEntityId());
				}
				
			};
			
		}
		
	}

}