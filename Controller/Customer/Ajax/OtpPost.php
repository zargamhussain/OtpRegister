<?php
namespace Create\OtpRegister\Controller\Customer\Ajax;
use Create\OtpRegister\Model\OtpFactory;
use \Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Session\SessionManagerInterface;
use PHPUnit\Framework\Constraint\IsTrue;

class OtpPost extends \Magento\Framework\App\Action\Action
{

    protected $subscriberFactory;
    /**
     * Summary of __construct
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\CustomerFactory $customer
     * @param \Thecoachsmb\Customer\Model\OtpFactory $otpFactory
     * @param \Magento\Customer\Model\Session $customersession
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Create\OtpRegister\Helper\Data $helper
     * @param \Magento\Customer\Model\ResourceModel\Customer\Collection $collection
     * @return mixed
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\CustomerFactory $customer,
        OtpFactory $otpFactory,
        \Magento\Customer\Model\Session $customersession,
        SessionManagerInterface $session,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Create\OtpRegister\Helper\Data $helper,
        \Magento\Customer\Model\ResourceModel\Customer\Collection $collection,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
    ) {
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->collection = $collection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_customer = $customer;
        $this->otpFactory = $otpFactory;
        $this->customersession = $customersession;
        $this->_sessionManager = $session;
        $this->subscriberFactory = $subscriberFactory;
        return parent::__construct($context);
    }

    /**
     * @return PageFactory
     */
    public function execute()
    {

        //get session
        $sessiondata = $this->_sessionManager->getUserFormData();
        $collection = $this->collection->addAttributeToSelect('*')
            ->addAttributeToFilter('email', $sessiondata['email'])
            ->load()->getData();
        //get otp
        $otpbymobile = $this->getRequest()->getParam('otp');
        //$otp = base64_encode($otpbymobile);
        $otp = $otpbymobile;
        $otpvalue = $this->otpFactory->create()->getCollection()->addFieldToFilter('otp', $otp)->getData();
        $status = $this->otpFactory->create()->getCollection()->addFieldToFilter('otp', $otp)->addFieldToSelect('status')->getData();

        //config expire time
        $expiredtime = $this->helper->getExpiretime();

        // check value is empty or not
        if (!empty($otpvalue)) {
            $created_at = (int) strtotime($otpvalue[0]['created_at']);
            $now = time();
            $now = (int) $now;
            $expire = $now -= $created_at;
            $otpstatus = $status[0]['status'];
            if ($otpstatus == 1) {
                //check expiredtime
                if ($expire <= $expiredtime) {
                    if (!empty($collection)) {
                        $customer = $this->_customer->create()->load($collection[0]['entity_id']);
                        $customerSession = $this->customersession;
                        $customerSession->setCustomerAsLoggedIn($customer);
                        $customerSession->regenerateId();
                        $response = [
                            'errors' => false,
                            'message' => __("Logged In Successfully.")
                        ];
                        $resultJson = $this->resultJsonFactory->create();
                        return $resultJson->setData($response);
                    } else {
                        $customer = $this->_customer->create();
                        $customer->setEmail($sessiondata['email']);
                        $customer->setFirstname($sessiondata['firstname']);
                        $customer->setLastname($sessiondata['lastname']);
                        $customer->setPassword($sessiondata['password']);
                        $customer->save();
                        //$customerData = $customer->getDataModel();
                        //$customerData->setCustomAttribute('mobile_number', $sessiondata['mobile_number']);
                        //$customer->updateData($customerData);
                        //$customer->save();
                        $customer = $this->_customer->create()->load($customer->getEntityId());
                        $customerSession = $this->customersession;
                        $customerSession->setCustomerAsLoggedIn($customer);
                        $customerSession->regenerateId();
                        if (isset($sessiondata['is_subscribed']) ){
                            $this->subscriberFactory->create()->subscribeCustomerById($customer->getEntityId());
                        }
                        $response = [
                            'errors' => false,
                            'message' => __("User Created Successfully.")
                        ];
                        $resultJson = $this->resultJsonFactory->create();
                        return $resultJson->setData($response);
          
                    }
                } else {
                    $response = [
                        'errors' => true,
                        'message' => __("OTP Expire")
                    ];
                    $resultJson = $this->resultJsonFactory->create();
                    return $resultJson->setData($response);
                }
            } else {
                $response = [
                    'errors' => true,
                    'message' => __("Invalid OTP")
                ];
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData($response);
            }
        } else {
            $response = [
                'errors' => true,
                'message' => __("Invalid OTP")
            ];
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
        }
    }
}