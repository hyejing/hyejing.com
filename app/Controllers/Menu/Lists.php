<?php namespace App\Controllers\Menu;

/* ===================================================================
	메뉴 리스트 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\AdminModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Lists extends BaseController
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
     * 메뉴 리스트 페이지
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            // 메뉴 최고 권한 설정
            $aManagerLevel = $this->admin->getManagerLevel();
            $iMaxLevel     = max(array_keys($aManagerLevel));

            $where     = "tadme.`level` <= :level:";// 로그인한 계정의 권한
            $this->menu->addBindData(['level' => $iMaxLevel]);

            $this->data_set['HTML']['title'] = "메뉴 관리";
            $this->data_set['HTML']['CSS']   = $this->linkCss("/assets/css/menu.css");
            $this->data_set['LIST']          = $this->menu->getListMenu($where); // 메뉴리스트 가져오기
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
            exit;
        }

        $this->display();
    }

    /**
     * 메뉴 순서 저장
     */
    public function sort()
    {
        try {
            //parameters
            $params = [
                'category_idx' => [
                    'default' => '',
                    'rules'   => 'required|is_natural_no_zero',
                    'error'   => '잘못된 접근입니다.',
                ],
                'sort'         => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => '카테고리 이름을 확인해주세요',
                ],
            ];
            $param  = $this->chkParam($params, 'post');

            // 메뉴 최고 권한 설정
            $aManagerLevel = $this->admin->getManagerLevel();
            $iMaxLevel     = max(array_keys($aManagerLevel));
            $this->menu->setLevel($iMaxLevel);

            // 정렬 저장
            $this->menu->saveSortMenu($param['category_idx'], $param['sort']);
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
            exit;
        }

        // 페이지 이동
        $this->redirect('/menu/lists');
    }

    public function swap()
    {
        try {
            //parameters
            $params = [
                'idx'  => [
                    'default' => '',
                    'rules'   => 'required|is_natural_no_zero',
                    'error'   => '정상적인 접근이 아닙니다.',
                ],
                'sort' => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => '정렬방향이 필요합니다.',
                ],
            ];
            $param  = $this->chkParam($params, 'get');

            // 메뉴 idx 설정
            $this->menu->setIdx($param['idx']);

            // 방향별 스왑할 메뉴 idx 가져옴
            switch ( $param['sort'] ) {
                case "prev" :
                    $iSwapIdx = $this->menu->getPrevSort();
                    break;
                case "next" :
                    $iSwapIdx = $this->menu->getNextSort();
                    break;
            }

            // 스왑활 idx 체크
            if ( empty($iSwapIdx) ) {
                throw new Exception("순서변경이 불가능합니다.");
            }

            // 순서 변경
            $bSwap = $this->menu->swapSort($iSwapIdx);
            if ( $bSwap === false ) {
                throw new Exception("순서 변경에 실패했습니다.");
            }
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
            exit;
        }

        // 처리 후 리스트 페이지 이동
        $this->redirect("/menu/lists");
    }

    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body'      => 'menu/lists',
            'import_js' => 'menu/lists.js',
        ];
    }
}
