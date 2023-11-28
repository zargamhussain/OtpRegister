<?php
namespace Create\OtpRegister\Controller\Customer\Ajax;
use Magento\Framework\Session\SessionManagerInterface;

class OTPregister extends \Magento\Framework\App\Action\Action
{
 

    /**
     * Summary of __construct
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Create\OtpRegister\Helper\Data $helper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Model\ResourceModel\Customer\Collection $collection
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        SessionManagerInterface $session,
        \Create\OtpRegister\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\ResourceModel\Customer\Collection $collection
    ) {
        $this->helper = $helper;
        $this->collection = $collection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_sessionManager = $session;
        parent::__construct($context);
    }

    /**
     * @return PageFactory
     */
    public function execute()
    {
            $params = $this->getRequest()->getParams();
            $collection = $this->collection->addAttributeToSelect('*')
                               ->addAttributeToFilter('email', $params['email'])
                               ->load()->getData();
            if (!empty($collection)) {
                $url = $this->_url->getUrl('customer/account/forgotpassword');
                $response = [
                    'errors' => true,
                    'message' => __(
                        'There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.',
                        $url
                    )
                ];
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData($response);
          }

          $password = $this->getRequest()->getParam('password'); 

          $confirmation = $this->getRequest()->getParam('password_confirmation');
          
          if(!empty($password) && !empty($confirmation)){
            if($password != $confirmation){
                $response = [
                  'errors' => true,
                  'message' => __(
                      'Please make sure your passwords match.'     
                  )
              ];
              $resultJson = $this->resultJsonFactory->create();
              return $resultJson->setData($response);
          }   
        }
        // set session
        $session = $this->_sessionManager;
        $session->setUserFormData($params);

        //update status
        try {

            $this->helper->setUpdateotpstatus($params['email']);

            //otp
            $otp_code = $this->helper->getOtpcode();

            //sms
            //$this->helper->getSendotp($otp_code,$params['email']);

            //save data
            $otp = base64_encode($otp_code);

            $this->helper->setOtpdata($otp_code,$params['email']);

            $response = [
                'errors' => false,
                'message' => __('OTP send to your Email Address')
            ];
        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

   


}