<?php namespace App\Controllers;

/* ===================================================================
	로그인 페이지
=================================================================== */

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use Psr\Log\LoggerInterface;

class Login extends BaseController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(false);
    }

    /**
     * 로그인 페이지
     *
     * @return void
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            // parameters
            $params = [
                'ref' => [
                    'default' => '/',
                ],
            ];
            $this->chkParam($params, 'get');

            $this->data_set['HTML']['body']['background'] = 'bg-primary bg-gradient';
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
        }

        $this->display();
    }

    /**
     * 로그인 처리 메소드
     *
     * @return void
     */
    public function exec()
    {
        try {
            $this->setDefaultView();

            // parameters
            $params = [
                'id'  => [
                    'default' => '',
                    'rules'   => 'required|min_length[4]|max_length[16]',
                    'error'   => '아이디는 4글자 이상 입력해주세요.',
                ],
                'pw'  => [
                    'default' => '',
                    'rules'   => 'required|min_length[6]|max_length[20]',
                    'error'   => '패스워드는 6글자 이상 입력해주세요.',
                ],
                'ref' => [
                    'default' => '/',   // 기본값 메인페이지
                ],
            ];
            $param  = $this->chkParam($params, 'post');

            // 로그인 설정
            $this->user->setId($param['id']);
            $this->user->setPw($param['pw']);

            // 로그인 전 정보 조회
            $aData = $this->user->getInfoForLogin();
            if ( isset($aData['idx']) === false || $aData['idx'] <= 0 ) {
                throw new Exception('로그인에 실패했습니다.');
            }

            // 로그인 처리
            $this->user->setIdx($aData['idx']);
            if ( $this->user->login() === false || $this->user->isLogin() === false ) {
                $rtv = $this->user->getResultData();
                throw new Exception($rtv['message']);
            }

            // 성공 시 설정된 페이지로 이동
            $this->redirect($param['ref']);
        }
        catch ( Exception $e ) {
            $this->data_set['HTML']['body']['background'] = 'bg-primary bg-gradient';
            $this->data_set['HTML']['ERROR']['message']   = $e->getMessage();
            $this->display();
        }
    }

    /**
     * 로그인 뷰 페이지
     *
     * @return void
     */
    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body'   => 'login',
            'layout' => 'common/layout_nomenu',
        ];
    }
}
