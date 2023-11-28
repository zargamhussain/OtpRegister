<?php
namespace Create\OtpRegister\Model\ResourceModel\Otp;

/**
 * Class Collection
 * @package Create\OtpRegister\Model\ResourceModel\Otp
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	/**
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Create\OtpRegister\Model\Otp', 'Create\OtpRegister\Model\ResourceModel\Otp');
	}
}