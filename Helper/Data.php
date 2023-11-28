<?php
namespace Create\OtpRegister\Helper;
use Magento\Contact\Model\ConfigInterface;
use \Magento\Framework\Mail\Template\TransportBuilder;
use \Magento\Framework\Translate\Inline\StateInterface;
//use Twilio\Rest\Client;
/**
 * Summary of Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const OTP_TYPE = 'number';
    const OTP_LENGTH = '6';  
    const EXPIRE_TIME = '';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    protected $otpFactory;

    protected $inlineTranslation;
	protected $transportBuilder;
	

   
    /**
     * Summary of __construct
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Create\OtpRegister\Model\OtpFactory $otpFactory
     * @param \Magento\Customer\Model\ResourceModel\Customer\Collection $collection
     * @return mixed
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Create\OtpRegister\Model\OtpFactory $otpFactory,
        \Magento\Customer\Model\ResourceModel\Customer\Collection $collection,
        StateInterface $inlineTranslation,
		TransportBuilder $transportBuilder,
        ConfigInterface $contactsConfig
    ) {
        $this->otpFactory = $otpFactory;
        $this->contactsConfig = $contactsConfig;
        $this->collection = $collection;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
		$this->transportBuilder = $transportBuilder;
        return parent::__construct($context);
    }

     /**
     * @param  String $path
     * @return string
     */
    public function getConfigvalue($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getOtptype()
    {
        return $this->getConfigvalue(self::OTP_TYPE);
    }

    /**
     * @return string
     */
    public function getOtplength()
    {
        return $this->getConfigvalue(self::OTP_LENGTH);
    }

   
    /**
     * @return string
     */
    public function getExpiretime()
    {
        return $this->getConfigvalue(self::EXPIRE_TIME);
    }

    /**
     * @return string
     */
    public function getOtpcode()
    {
        $otp_type = self::OTP_TYPE;
        $otp_length = self::OTP_LENGTH;

        if (empty($otp_length)) {
            $otp_length = 4;
        }
        if ($otp_type == "number") {
            $str_result = '0123456789';
            $otp_code =  substr(str_shuffle($str_result), 0, $otp_length);
        } elseif ($otp_type == "alphabets") {
            $str_result = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            $otp_code =  substr(str_shuffle($str_result), 0, $otp_length);
        } elseif ($otp_type == "alphanumeric") {
            $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            $otp_code =  substr(str_shuffle($str_result), 0, $otp_length);
        } else {
            $otp_code = mt_rand(10000, 99999);
        }
       
        return $otp_code;
    }

    /**
     * Send Sms
     */
    public function getSendotp($otp_code, $email)
    {
       // Send Mail To Admin For This
		  
			$this->inlineTranslation->suspend();
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $transport = $this->transportBuilder
               ->setTemplateIdentifier('otp_email_template_for_regisetr_users')
			   ->setTemplateOptions(
                    [
                        'area' => 'frontend',
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
               ->setTemplateVars([
                    'otp_code'  => $otp_code,
					'email' => $email
            	])
               ->setFrom($this->contactsConfig->emailSender())
               ->addTo($email)
               ->getTransport();

            $transport->sendMessage();
			$this->inlineTranslation->resume();
			
    }

    /**
     * Send Sms
     */


     /*
    public function getSendotpByMobile($otp_code, $mobile_number)
    {
        $number = '+913853453455';
        $sid  =    'SID';
        $token  = 'Auth_Key';

        $twilio = new Client($sid, $token);
        $twilio->messages
            ->create(
                $mobile_number, // to
                ["from" => "+" . $number, "body" => $otp_code]
            );
    }
    */

    /**
     * Save Otp
     */
    public function setOtpdata($otp, $email)
    {
        $question = $this->otpFactory->create();
        $question->setOtp($otp);
        $question->setCustomer($email);
        $question->setStatus('1');
        $question->save();
    }

    /**
     * Update Otp
     */
    public function setUpdateotpstatus($email)
    {
        $customerstatus = $this->otpFactory->create()->getCollection()->addFieldToFilter('customer', $email)->getData();
        if (!empty($customerstatus)) {
            foreach ($customerstatus as $data) {
                $customerstatus1 = $this->otpFactory->create()->load($data['entity_id']);
                $customerstatus1->setStatus('0');
                $customerstatus1->save();
            }
        }
    }
}