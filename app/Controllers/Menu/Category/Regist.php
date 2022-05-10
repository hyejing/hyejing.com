<?php namespace App\Controllers\Menu\Category;

/* ===================================================================
	카테고리 등록 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\AdminModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Regist extends BaseController
{
    protected AdminModel $admin;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 4, '접근권한이 없습니다.');

        // 관리자 모델링
        $this->admin = new AdminModel();
    }

    /**
     * 카테고리 추가 페이지
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            $this->data_set['HTML']['title']       = "카테고리 추가";
            $this->data_set['HTML']['admin_level'] = $this->admin->getManagerLevel();   // 회원권한 레벨
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
     * 카테고리 추가 처리
     */
    public function proc()
    {
        try {
            //parameters
            $params = [
                'name'  => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => '메뉴명을 확인해주세요',
                ],
                'level' => [
                    'default' => '0',
                ],
                'state' => [
                    'default' => '0',
                ],
            ];
            $param  = $this->chkParam($params, 'post');

            // DB 저장내용
            $this->menu->setName($param['name']);       // 메뉴명 설정
            $this->menu->setLevel($param['level']);     // 접근권한 등급
            $this->menu->setState($param['state']);     // 노출 여부

            // 카테고리 추가
            $iRegId = $this->menu->registCategory();

            // 결과처리
            if ( $iRegId < 1 ) {
                throw new Exception('저장 처리에 실패했습니다.', '3001');
            }

            $this->alert('', 'close');  // 완료 후 창 닫기
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
            exit;
        }
    }

    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body'   => 'menu/category/regist',
            'layout' => 'common/layout_popup',
        ];
    }
}
