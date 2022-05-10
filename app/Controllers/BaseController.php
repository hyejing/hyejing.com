<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\MenuModel;
use App\Libraries\Template_;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Validation\Validation;
use Config\Common;
use Config\Services;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
class BaseController extends Controller
{
    protected Common $common;
    protected Template_ $tpl;
    protected UserModel $user;
    protected MenuModel $menu;
    protected Validation $validation;
    protected URI $uri;

    protected $request;
    protected $helpers = ['form', 'url', 'common'];

    protected array $view_page = [];
    protected array $data_set = [];

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        $this->request    = Services::request();
        $this->validation = Services::validation();
        $this->uri        = current_url(true);

        $this->common = new Common();
        $this->tpl    = new Template_();
        $this->user   = new UserModel();
        $this->menu   = new MenuModel();

        if ( is_cli() === false ) {
            // 언어셋
            $aSupportLanguage = $this->request->config->supportedLocales;
            if ( isset($_COOKIE['lang']) === true && in_array($_COOKIE['lang'], $aSupportLanguage) === true ) {
                $sSetLocale = $_COOKIE['lang'];
            }
            else {
                $sSetLocale = 'kr';
            }
            $this->request->setLocale($sSetLocale); // getLocale(); // 현재 언어셋 가져오기

            $this->data_set['USER']     = $this->user->chkLogin();
            $this->data_set['LEFTMENU'] = $this->user->getMenu();

            //세그먼트 배열
            $this->data_set['MENU']['segment']    = $this->uri->getSegments();
            $this->data_set['MENU']['uri_string'] = rtrim(str_replace($_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']), '?');

            // 리스트 페이지에서 목록 이동용 쿠키 생성
            if ( empty($this->data_set['MENU']['segment']) === false && in_array('lists', $this->data_set['MENU']['segment']) === true ) {
                $history_back = str_replace(URL_DOMAIN, '', $this->data_set['MENU']['uri_string'] . '?' . $_SERVER['QUERY_STRING']);
                set_cookies('list_url', $history_back);
            }

            $agent                                 = $this->request->getUserAgent();
            $this->data_set['AGENT']['is_browser'] = $agent->isBrowser();
            $this->data_set['AGENT']['is_mobile']  = $agent->isMobile();
            $this->data_set['AGENT']['platform']   = $agent->getPlatform();
            $this->data_set['AGENT']['browser']    = $agent->getBrowser();
            $this->data_set['AGENT']['version']    = $agent->getVersion();
            $this->data_set['AGENT']['mobile']     = $agent->getMobile();
            $this->data_set['AGENT']['refer']      = $agent->getReferrer();
        }
    }

    /**
     * 로그인 여부 체크 및 접근권한 체크
     */
    protected function chkLogin($flag, $level = 0, $msg = '', $is_api = false)
    {
        $referral = '';

        // 페이지 이동 설정 : 로그인이 되어 있어야 하는 경우
        if ( $flag === true ) {
            // 로그인이 되어있을 때는 메인 페이지로
            if ( $this->user->isLogin() === true ) {
                $referral = '/';
            }
            else {
                // 로그인이 안되어있을 때는 마지막 페이지로
                if ( empty($_SERVER['QUERY_STRING']) === false ) {
                    $referral = current_url() . '?' . $_SERVER['QUERY_STRING'];
                }
                else {
                    $referral = current_url();
                }
                $referral = str_replace(base_url(), '', $referral);
            }
        }

        if ( $is_api === true ) {
            // api 파일일 경우 json 리턴
            if ( $this->user->isLogin() !== $flag || $this->user->getManagerLevel() < $level ) {
                $output            = [];
                $output['result']  = false;
                $output['code']    = '9999';
                $output['message'] = empty($msg) ? '접근 권한이 없습니다.' : $msg;
                $this->displayJson($output);
            }

        }
        else {
            // 이동 페이지 처리
            if ( $this->user->isLogin() !== $flag || $this->user->getManagerLevel() < $level ) {
                $redirect_url = ($this->user->isLogin() === false && $flag) ? site_url('login') . '?ref=' . urlencode($referral) : site_url($referral);
                if ( empty($msg) === false ) {
                    $this->alert($msg, 'move', $redirect_url);
                }
                else {
                    $this->redirect($redirect_url);
                }
            }
        }
    }

    /**
     * 파라미터 체크 및 셋팅하기
     *
     * @throws Exception
     */
    protected function chkParam($params = [], $type = "get_post"): array
    {
        $types     = [];
        $param     = [];
        $param_tmp = [];

        // 타입 추출 : get, post
        if ( strpos($type, '_') !== false ) {
            $type_arr = explode('_', $type);
            foreach ( $type_arr as $tv ) {
                $tv = trim($tv);
                if ( empty($tv) === false && in_array($tv, ['get', 'post']) === true ) {
                    $types[] = $tv;
                }
            }
        }
        else {
            if ( empty($type) === false && in_array($type, ['get', 'post']) === true ) {
                $types[] = $type;
            }
        }

        // 타입별로 값 입력
        foreach ( $types as $v ) {
            $reqMethod = 'get' . Ucfirst($v);
            $p         = $this->request->{$reqMethod}();
            foreach ( $p as $pk => $pv ) {
                $param_tmp[$pk] = $pv;
            }
        }

        // 디폴트 값 입력
        //$param = $param_tmp;  // 주석O : 셋팅값만 처리 / 주석X : request값 전부 허용
        if ( is_array($params) && empty($params) === false && count($params) > 0 ) {
            foreach ( $params as $k => $v ) {
                if ( isset($v['rules']) === true && empty($v['rules']) === false ) {
                    // 해당 request 값 선언여부 체크 : 선언 안된 경우 Notice 에러로 default 선언 혹은 error 처리 불가능
                    if ( isset($param_tmp[$k]) === false ) {
                        $param_tmp[$k] = null;
                    }
                    $this->validation->reset();
                    $rtv = $this->validation->check($param_tmp[$k], $v['rules']);
                    if ( $rtv === false ) {
                        $error_msg = $this->validation->getErrors();
                        $err_msg   = $v['error'] ?? $error_msg['check'] ?? '';
                        throw new Exception($err_msg);
                    }
                }

                $param[$k] = (isset($param_tmp[$k]) === true) ? $param_tmp[$k] : $v['default'];
            }
        }

        // trim 처리
        array_walk_recursive($param, function(&$v) {
            $v = trim($v);
        });

        $this->data_set['HTML']['param'] = $param;

        return $param;
    }

    /**
     * JS 링크 생성
     */
    protected function linkJs($filename): string
    {
        $result = [];
        if ( is_array($filename) === true ) {
            foreach ( $filename as $file_arr ) {
                $result[] = '<script src="' . $file_arr . '" type="text/javascript"></script>';
            }
        }
        else {
            $result[] = '<script src="' . $filename . '" type="text/javascript"></script>';
        }

        return "\n" . @implode("\n", $result);
    }

    /**
     * CSS 링크 생성
     */
    protected function linkCss($filename): string
    {
        $result = [];
        if ( is_array($filename) === true ) {
            foreach ( $filename as $file_arr ) {
                $result[] = '<link rel="stylesheet" type="text/css" media="all" href="' . $file_arr . '" />';
            }
        }
        else {
            $result[] = '<link rel="stylesheet" type="text/css" media="all" href="' . $filename . '" />';
        }

        return "\n" . @implode("\n", $result);
    }

    /* ===================================================================
        Alert & Redirect
    =================================================================== */
    /**
     * URL 이동
     */
    protected function redirect($url)
    {
        if ( strpos($url, 'http') !== false ) {
            header('Location: ' . $url);
        }
        else {
            header('Location: ' . rtrim(URL_DOMAIN, '/') . '/' . ltrim($url, '/'));
        }
        exit;
    }

    /**
     * 알림창 띄우기
     */
    protected function alert($msg, $type = 'none', $url = '')
    {
        $script = '';
        if ( empty($msg) === false ) {
            $script = "alert('" . $msg . "');" . "\n";
        }

        if ( strpos($url, 'http') === false ) {
            $url = site_url($url);
        }

        switch ( $type ) {
            case 'move':
                $script .= "location.href = '" . $url . "';" . "\n";
                break;

            case 'opener_move':
                $script .= "opener.location.href = '" . $url . "';" . "\n";
                $script .= "self.close();" . "\n";
                break;

            case 'back':
                $script .= "history.back();" . "\n";
                break;

            case 'close':
                $script .= "opener.location.reload();" . "\n";
                $script .= "self.close();" . "\n";
                break;

            case 'self':
                $script .= "location.reload();" . "\n";
                break;

            default:
                break;
        }

        echo '<script>' . "\n";
        echo $script;
        echo '</script>' . "\n";
        exit;
    }

    /* ===================================================================
        Display
    =================================================================== */
    /**
     * 템플릿 뷰 설정 : 일반 페이지
     */
    protected function display($except = [], $fetch = false): bool
    {
        // default page
        $default = [
            'head'      => 'common/head.php',
            'menu'      => 'common/menu.php',
            'left_menu' => 'common/left_menu.php',
            'body'      => 'common/blank.php',
            'foot'      => 'common/foot.php',
            'layer'     => 'common/layer.php',
            'js'        => 'common/js.php',
            'import_js' => 'common/blank.php',
            'layout'    => 'common/layout.php',
            'editor'    => 'common/blank.php',// 에디터 호출 @_ckeditor4_add
            'editor_js' => 'common/blank.php',// 에디터 호출 @_ckeditor4_add
        ];

        return $this->displayProc($default, $except, $fetch);
    }

    /**
     * 템플릿 뷰 설정 : 팝업 페이지
     */
    protected function displayPop($except = [], $fetch = false): bool
    {
        // default page
        $default = [
            'head'      => 'common/head.php',
            'body'      => 'common/blank.php',
            'js'        => 'common/js.php',
            'import_js' => 'common/blank.php',
            'layout'    => 'common/layout_popup.php',
            'editor'    => 'common/blank.php',// 에디터 호출 @_ckeditor4_add
            'editor_js' => 'common/blank.php',// 에디터 호출 @_ckeditor4_add
        ];

        return $this->displayProc($default, $except, $fetch);
    }

    /**
     * 템플릿 뷰 설정 : 에러 페이지
     */
    protected function displayError($except = [], $fetch = false): bool
    {
        // error HTML
        $this->view_page = ['body' => 'common/error'];

        $this->display($except, $fetch);
        exit;
    }

    private function displayProc($default, $except, $fetch): bool
    {
        // set default
        $def_list = [];
        foreach ( $default as $k => $v ) {
            if ( in_array($k, $except) === false ) {
                $def_list[$k] = $v;
            }
        }

        // set define file lists
        $this->data_set['DEFINE'] = $def_list;
        $this->tpl->define($def_list);

        // set page
        foreach ( $this->view_page as $type => $file ) {
            if ( empty($file) === false ) {
                $this->tpl->define($type, $file . '.php');
            }
            else {
                $this->tpl->define($type, 'common/blank.php');
            }
        }

        // set variable
        foreach ( $this->data_set as $key => $val ) {
            $this->tpl->assign($key, $val);
        }

        // print
        if ( $fetch === false ) {
            $this->tpl->print_('layout');
        }
        else {
            return $this->tpl->fetch('layout');
        }

        return true;
    }

    /**
     * Json 값 출력
     */
    protected function displayJson(array $val)
    {
        header('Content-Type: application/json');
        $jsonpCallback_fn_name = $this->request->getPost('callback');
        echo (empty($jsonpCallback_fn_name) === false) ? $jsonpCallback_fn_name . '(' . json_encode($val) . ');' : json_encode($val);
        exit;
    }
}
