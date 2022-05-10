<?php namespace App\Controllers\Menu;

/* ===================================================================
	메뉴 등록 페이지
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
     * 메뉴 추가 페이지
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            //parameters
            $params = [
                'parent' => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => '잘못된 접근 입니다.',
                ],
            ];
            $param  = $this->chkParam($params, 'get');

            // 해당 메뉴의 정보 조회
            $this->menu->setIdx($param['parent']);
            $aInfo = $this->menu->getInfo();

            $this->data_set['HTML']['title']       = "[ " . $aInfo['name'] . " ] 카테고리 서브메뉴 추가";
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
     * 메뉴 처리
     */
    public function proc()
    {
        try {
            //parameters
            $params = [
                'name'   => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => '메뉴명을 확인해주세요',
                ],
                'parent' => [
                    'default' => '0',
                ],
                'state'  => [
                    'default' => '0',
                ],
                'level'  => [
                    'default' => '0',
                ],
                'link'   => [
                    'default' => '',
                ],
            ];
            $param  = $this->chkParam($params, 'post');

            // DB 저장내용
            $this->menu->setParent($param['parent']);  // 상위 메뉴 idx
            $this->menu->setName($param['name']);      // 메뉴명 설정
            $this->menu->setLink($param['link']);      // 메뉴 링크
            $this->menu->setLevel($param['level']);    // 접근권한 등급
            $this->menu->setState($param['state']);    // 노출 여부

            // 메뉴 추가
            $iRegId = $this->menu->regist();

            // 결과 처리
            if ( $iRegId < 1 ) {
                throw new Exception('저장 처리에 실패했습니다.', '3001');
            }
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
            exit;
        }

        // 완료 후 창 닫기
        $this->alert('', 'close');
    }

    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body'   => 'menu/regist',
            'layout' => 'common/layout_popup',
        ];
    }
}
