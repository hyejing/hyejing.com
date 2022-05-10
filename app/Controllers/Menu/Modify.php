<?php namespace App\Controllers\Menu;

/* ===================================================================
	메뉴 수정 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\AdminModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Modify extends BaseController
{
    protected AdminModel $admin;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 4, '접근권한이 없습니다.');

        // 관리자 관련 모델
        $this->admin = new AdminModel();
    }

    /**
     * 메뉴 수정 페이지
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            //parameters
            $params = [
                'idx' => [
                    'default' => '',
                    'rules'   => 'required|is_natural_no_zero',
                    'error'   => '정상적인 접근이 아닙니다.',
                ],
            ];
            $param  = $this->chkParam($params, 'get');

            // 메뉴 정보 가져오기
            $this->menu->setIdx($param['idx']);
            $aInfo = $this->menu->getInfo();

            $this->data_set['HTML']['title']       = "[ " . $aInfo['name'] . " ] 메뉴 수정";
            $this->data_set['HTML']['admin_level'] = $this->admin->getManagerLevel(); // 회원권한 레벨
            $this->data_set['DATA']['menu']        = $aInfo;                          // 현 메뉴 정보
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
     * 메뉴 처리
     */
    public function proc()
    {
        try {
            //parameters
            $params = [
                'idx'   => [
                    'default' => '0',
                    'rules'   => 'required',
                    'error'   => '잘못된 접근입니다.',
                ],
                'name'  => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => '메뉴명을 확인해주세요.',
                ],
                'level' => [
                    'default' => '0',
                ],
                'link'  => [
                    'default' => '',
                ],
                'state' => [
                    'default' => '0',
                ],
            ];
            $param  = $this->chkParam($params, 'post');

            // DB 저장내용
            $this->menu->setIdx($param['idx']);         // 메뉴명 설정
            $this->menu->setName($param['name']);       // 메뉴명 설정
            $this->menu->setLink($param['link']);       // 메뉴 링크
            $this->menu->setLevel($param['level']);     // 접근권한 등급
            $this->menu->setState($param['state']);     // 사용 여부

            // 메뉴 수정
            $result = $this->menu->modify();

            // 결과처리
            if ( $result === false ) {
                throw new Exception('수정 처리에 실패했습니다.', '3001');
            }
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
            exit;
        }

        $this->alert('', 'close');
    }

    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body' => 'menu/modify',
        ];
    }
}
