<?php
namespace Create\OtpRegister\Model\ResourceModel;
/**
 * Class Otp
 * @package Create\OtpRegister\\Model\ResourceModel
 */
class Otp extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('email_otp', 'entity_id');
    }
}