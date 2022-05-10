<?php namespace App\Controllers;

/* ===================================================================
	가입 페이지
=================================================================== */

use App\Models\AdminModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;


class Signup extends BaseController
{
    protected AdminModel $admin;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        //$this->chkLogin(false);

        // 관리자 관련 모델링
        $this->admin = new AdminModel();
    }

    /**
     * 가입 페이지
     *
     * @return void
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            $this->data_set['HTML']['body']['background'] = 'bg-info bg-gradient';
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
     * 가입 처리 메소드
     *
     * @return void
     */
    public function exec()
    {
        try {
            // 페이지 출력
            $this->setDefaultView();

            // parameters
            $params = [
                'id'          => [
                    'default' => '',
                    'rules'   => 'required|min_length[4]|max_length[16]',
                    'error'   => '아이디는 4~16자의 영문소문자 및 숫자로 입력해주세요.',
                ],
                'password'    => [
                    'default' => '',
                    'rules'   => 'required|min_length[6]|max_length[20]',
                    'error'   => '패스워드는 6글자 이상 입력해주세요.',
                ],
                'password_re' => [
                    'default' => '',
                    'rules'   => 'required|min_length[6]|max_length[20]',
                    'error'   => '패스워드 확인란을 입력해주세요.',
                ],
                'name'        => [
                    'default' => '',
                    'rules'   => 'required|min_length[2]|max_length[20]',
                    'error'   => '이름은 2~20자의 한글 및 영문으로 입력해주세요.',
                ],
            ];
            $param  = $this->chkParam($params, 'post');

            // 패스워드 일치 여부 확인
            if ( $param['password'] != $param['password_re'] ) {
                throw new Exception('입력하신 패스워드가 서로 일치하지 않습니다.');
            }

            // 등록할 관리자 정보 셋팅
            $this->admin->setId($param['id']);
            $this->admin->setPw($param['password']);
            $this->admin->setName($param['name']);

            // 등록 처리
            if ( $this->admin->registAdmin() === false ) {
                $rtv = $this->admin->getResultData();
                throw new Exception($rtv['message']);
            }

            $this->alert('관리자 가입 성공!\n가입 승인 후 로그인 할 수 있습니다.', 'move', '/login');
        }
        catch ( Exception $e ) {
            $this->data_set['HTML']['body']['background'] = 'bg-info bg-gradient';
            $this->data_set['HTML']['ERROR']['message']   = $e->getMessage();
            $this->display();
        }
    }

    /**
     * 가입 뷰 페이지
     *
     * @return void
     */
    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body'   => 'signup',
            'layout' => 'common/layout_nomenu',
        ];
    }
}
