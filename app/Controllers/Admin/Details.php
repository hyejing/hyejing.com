<?php namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Details extends BaseController
{
    protected AdminModel $admin;
    protected array $param;
    protected array $where;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 4, '접근권한이 없습니다.');

        // 관리자 관련 모델링
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

            // set parameters
            $params = [
                'idx' => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => '잘못된 접근 입니다.'
                ],
            ];
            $param  = $this->chkParam($params, 'get');

            // 상세정보 조회용 IDX
            $this->admin->setIdx($param['idx']);

            $this->data_set['HTML']['title']       = "관리자 상세정보";
            $this->data_set['HTML']['list_url']    = $_COOKIE['list_url'] ?? '/admin/lists';
            $this->data_set['HTML']['admin_level'] = $this->admin->getManagerLevel();
            $this->data_set['HTML']['admin_state'] = $this->admin->getStatusList();
            $this->data_set['DATA']['info']        = $this->admin->getInfo();
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
     * 관리자 정보 수정 처리
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
                'idx'   => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => '잘못된 접근입니다.'
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

            // 변경될 관리자 정보 셋팅
            $this->admin->setidx($param['idx']);
            $this->admin->setName($param['name']);
            $this->admin->setLevel($param['level']);
            $this->admin->setStatus($param['state']);

            // 수정 처리
            if ( $this->admin->modifyAdmin() === false ) {
                $rtv = $this->admin->getResultData();
                throw new Exception($rtv['message']);
            }

            // 수정 성공
            $this->alert('성공적으로 수정 되었습니다.', 'move', URL_DOMAIN . '/admin/details?idx=' . $param['idx']);
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
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
            'body' => 'admin/details',
        ];
    }
}
