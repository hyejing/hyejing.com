<?php namespace Config;

/* ===================================================================
	Common Variables
----------------------------------------------------------------------
공통클래스에서 사용할 경우
use Config\Common;
protected Common $common;
$this->common = new Common();
v($this->common->aValue);
----------------------------------------------------------------------
모델이나 심플하게 사용할 경우
$common = config('Common');
$cSiteName = $common->aValue;
=================================================================== */
use CodeIgniter\Config\BaseConfig;

class Common extends BaseConfig
{
	/**
	 * 텔레그램 발송 대상
	 */
	public array $aTgAdmin = [
		'NHJ' => '756843550',
	];
}
