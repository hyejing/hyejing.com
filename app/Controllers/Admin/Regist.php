<?php namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Regist extends BaseController
{
    protected AdminModel $admin;
    protected array $param;
    protected array $where;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 4, '접근권한이 없습니다.');

        $this->admin = new AdminModel();
    }

    /**
     * 기본 출력 페이지
     *
     * @return void
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            $this->data_set['HTML']['admin_level'] = $this->admin->getManagerLevel();
            $this->data_set['HTML']['admin_state'] = $this->admin->getStatusList();
            $this->data_set['HTML']['title']       = "관리자 계정 등록";
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
            exit;
        }

        $this->displayPop();
    }

    /**
     * 관리자 계정 등록 처리
     *
     * @return void
     * @throws Exception
     */
    public function exec()
    {
        try {
            $this->setDefaultView();

            // set parameters
            $params = [
                'id'    => [
                    'default' => '',
                    'rules'   => 'required|min_length[4]|max_length[16]',
                    'error'   => '아이디는 4~16자의 영문소문자 또는 숫자로 입력해주세요.'
                ],
                'pw'    => [
                    'default' => '',
                    'rules'   => 'required|min_length[6]|max_length[20]',
                    'error'   => '비밀번호는 6자 이상으로 입력해주세요.'
                ],
                'pw_re' => [
                    'default' => '',
                    'rules'   => 'required|min_length[6]|max_length[20]',
                    'error'   => '비밀번호는 6자 이상으로 입력해주세요.'
                ],
                'name'  => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => '이름을 입력해주세요.'
                ],
                'level' => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => '권한 등급을 선택해주세요.'
                ],
                'state' => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => '상태를 선택해주세요.'
                ],
            ];
            $param  = $this->chkParam($params, 'post');

            // 패스워드 일치 여부 확인
            if ( $param['pw'] != $param['pw_re'] ) {
                throw new Exception('패스워드가 일치하지 않습니다.');
            }

            // 관리자 정보 셋팅
            $this->admin->setId($param['id']);
            $this->admin->setPw($param['pw']);
            $this->admin->setName($param['name']);
            $this->admin->setLevel($param['level']);
            $this->admin->setStatus($param['state']);

            // 등록 처리
            if ( $this->admin->registAdmin() === false ) {
                $rtv = $this->admin->getResultData();
                throw new Exception($rtv['message']);
            }

            // 등록 성공
            $this->alert('성공적으로 등록되었습니다.', 'close');
        }
        catch ( Exception $e ) {
            $this->data_set['HTML']['title']       = "관리자 계정 등록";
            $this->data_set['HTML']['admin_level'] = $this->admin->getManagerLevel();
            $this->data_set['HTML']['admin_state'] = $this->admin->getStatusList();

            $this->data_set['HTML']['ERROR']['message'] = $e->getMessage();
            $this->displayPop();
        }
    }

    /**
     * view page 셋팅
     *
     * @return void
     */
    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body' => 'admin/regist',
        ];
    }
}
