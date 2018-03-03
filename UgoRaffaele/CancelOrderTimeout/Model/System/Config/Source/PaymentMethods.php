<?php
namespace UgoRaffaele\CancelOrderTimeout\Model\System\Config\Source;

class PaymentMethods {
	
	protected $paymentConfig;

	protected $scopeConfig;
	
    public function __construct(
		\Magento\Payment\Model\Config $paymentConfig,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
		$this->paymentConfig = $paymentConfig;
		$this->scopeConfig = $scopeConfig;
    }
	
    protected function getPaymentMethods()
    {
        return $this->paymentConfig->getActiveMethods();
    }
	
    public function toOptionArray()
    {
		
        $methods = [
			[
				'value'=>'',
				'label'=>''
			]
		];
		
        $payments = $this->getPaymentMethods();
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = $this->scopeConfig->getValue('payment/' . $paymentCode . '/title');
            $methods[$paymentCode] = [
                'label'   => $paymentTitle,
                'value' => $paymentCode
            ];
        }
		
        return $methods;
		
    }
		
}