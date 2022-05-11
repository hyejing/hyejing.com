<?php
/*
|--------------------------------------------------------------------------
| BASIC CONFIG
|--------------------------------------------------------------------------
*/

// development로 보일 IP 목록
$__OFFICE_IP = ['121.138.58.134',];

$cnf_domain = 'hyejing.com';
$service_url = 'https://hyejing.com/';

if( in_array($_SERVER["REMOTE_ADDR"], $__OFFICE_IP) ) {
	defined('ENVIRONMENT')		|| define('ENVIRONMENT',	'development');
}else{
	defined('ENVIRONMENT') || define('ENVIRONMENT', 'production');
}
defined('CNF_DOMAIN') || define('CNF_DOMAIN', $cnf_domain);
defined('CNF_ROOT') || define('CNF_ROOT', FCPATH . '..');

defined('CNF_SERVICE') || define('CNF_SERVICE', 'hyejing');
defined('CNF_SUFFIX_LOG') || define('CNF_SUFFIX_LOG', '.log');
defined('CNF_TITLE') || define('CNF_TITLE', 'HYEJING.COM');

/*
|--------------------------------------------------------------------------
| PATH
|--------------------------------------------------------------------------
*/
defined('PATH_ROOT') || define('PATH_ROOT', CNF_ROOT . DIRECTORY_SEPARATOR);
defined('PATH_APP') || define('PATH_APP', PATH_ROOT . 'app' . DIRECTORY_SEPARATOR);
defined('PATH_PUBLIC') || define('PATH_PUBLIC', PATH_ROOT . 'public' . DIRECTORY_SEPARATOR);
defined('PATH_WRITABLE') || define('PATH_WRITABLE', PATH_ROOT . 'writable' . DIRECTORY_SEPARATOR);
defined('PATH_CONFIG') || define('PATH_CONFIG', PATH_APP . 'Config' . DIRECTORY_SEPARATOR);
defined('PATH_VIEWS') || define('PATH_VIEWS', PATH_APP . 'Views' . DIRECTORY_SEPARATOR);
defined('PATH_LOG') || define('PATH_LOG', PATH_WRITABLE . 'logs' . DIRECTORY_SEPARATOR);
defined('PATH_TPL') || define('PATH_TPL', PATH_WRITABLE . 'cache/template' . DIRECTORY_SEPARATOR);
defined('PATH_TPL_COMPILE') || define('PATH_TPL_COMPILE', PATH_TPL . '_compile');
defined('PATH_TPL_CACHE') || define('PATH_TPL_CACHE', PATH_TPL . '_cache');
defined('PATH_ASSET') || define('PATH_ASSET', PATH_PUBLIC . 'assets' . DIRECTORY_SEPARATOR);
defined('PATH_CPNT') || define('PATH_CPNT', PATH_ASSET . 'components' . DIRECTORY_SEPARATOR);
defined('PATH_JS') || define('PATH_JS', PATH_ASSET . 'js' . DIRECTORY_SEPARATOR);
defined('PATH_CSS') || define('PATH_CSS', PATH_ASSET . 'css' . DIRECTORY_SEPARATOR);

/*
|--------------------------------------------------------------------------
| URL
|--------------------------------------------------------------------------
*/
$CNF = [];
if ( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ) {
	$CNF['PROTOCOL'] = $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://';
}
else {
	$CNF['PROTOCOL'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
}
if ( $CNF['PROTOCOL'] == 'https://' ) {
	$_SERVER['HTTPS'] = 'on';
}

$CNF['DOMAIN'] = CNF_DOMAIN;
if ( isset($_SERVER['SERVER_NAME']) === true ) {
	$CNF['DOMAIN'] = rtrim($_SERVER['SERVER_NAME'] . str_replace(basename($_SERVER['SCRIPT_NAME']), null, $_SERVER['SCRIPT_NAME']), '/');
}

$CNF['PORT'] = '';
if ( isset($_SERVER['SERVER_PORT']) === true && in_array($_SERVER['SERVER_PORT'], ['80', '443']) === false ) {
	$CNF['PORT'] = ':' . $_SERVER['SERVER_PORT'];
}

defined('URL_DOMAIN') || define('URL_DOMAIN', $CNF['PROTOCOL'] . $CNF['DOMAIN'] . $CNF['PORT']);
defined('URL_COOKIE') || define('URL_COOKIE', '.' . $CNF['DOMAIN'] . $CNF['PORT']);
defined('URL_SERVICE') || define('URL_SERVICE', $service_url);
defined('URL_CPNT') || define('URL_CPNT', URL_DOMAIN . '/assets/components');
defined('URL_JS') || define('URL_JS', '/assets/js');

/*
|--------------------------------------------------------------------------
| COOKIE : 쿠키키값
|--------------------------------------------------------------------------
*/
defined('COOKIE_ID') || define('COOKIE_ID', CNF_SERVICE . '_save_id');                             // 아이디 저장
defined('COOKIE_ACCESS_TOKEN') || define('COOKIE_ACCESS_TOKEN', CNF_SERVICE . '_access_token');    // 접근권한
defined('COOKIE_TIME') || define('COOKIE_TIME', 60 * 60 * 24);                                     // 쿠키 재생성 시간

/*
|--------------------------------------------------------------------------
| TIME
|--------------------------------------------------------------------------
*/
defined('TIME_STAMP') || define('TIME_STAMP', strtotime('NOW'));
defined('TIME_NOW') || define('TIME_NOW', date('Y-m-d H:i:s', strtotime('NOW')));
