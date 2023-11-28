<?php
namespace Create\OtpRegister\Model;
use Magento\Framework\Model\AbstractModel;
class Otp extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Create\OtpRegister\Model\ResourceModel\Otp');
    }
}