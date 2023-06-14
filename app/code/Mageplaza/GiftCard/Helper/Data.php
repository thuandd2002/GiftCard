<?php
namespace Mageplaza\GiftCard\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
 
class Data extends AbstractHelper
{
    const XML_PATH_HELLOWORLD = "gift_section_id/";
    public function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue(
			$field, ScopeInterface::SCOPE_STORE, $storeId
		);
	}

    public function getGeneralConfig($code, $storeId = null)
	{

		return $this->getConfigValue(self::XML_PATH_HELLOWORLD .'general/'. $code, $storeId);
	}
	
	public function getCodeConfig($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_HELLOWORLD .'code/'. $code, $storeId);
	}
	
	function generateGiftCode($length) {
		$characters = 'ABCDEFGHIJKLMLOPQRSTUVXYZ0123456789';
		$charactersLength = strlen($characters);
		$giftCode = '';
		for ($i = 0; $i < $length; $i++) {
		$giftCode .= $characters[random_int(0, $charactersLength - 1)];
		}
		return $giftCode;
		}
}
