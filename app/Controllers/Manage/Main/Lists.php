<?php namespace App\Controllers\Manage\Main;

/* ===================================================================
	메인 섹션 리스트 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\Manage\MainSectionModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Lists extends BaseController
{
    protected MainSectionModel $oMainSection;
    protected array $where;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 4, '접근권한이 없습니다.');

        // 메인섹션 모델 오브젝트 생성
        $this->oMainSection = new MainSectionModel();
    }

    /**
     * 메뉴 리스트 페이지
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            $params = [
                'type'        => [
                    'default' => '',
                    'rules'   => '',
                    'error'   => '섹션 이름을 입력해주세요.',
                ],
                'state'       => [
                    'default' => '',
                    'rules'   => '',
                    'error'   => '섹션 오픈 여부를 선택 해 주세요.',
                ],
                'search_text' => [
                    'default' => '',
                    'rules'   => '',
                    'error'   => '섹션 타입을 선택 해 주세요.',
                ]
            ];
            // input check
            $aParams = $this->chkParam($params, 'get');

            // 조건절 생성
            $this->getWhere($aParams);

            // 섹션 리스트 가져오기
            $field  = "tsema.*, (SELECT COUNT(*) FROM tbl_section_main_contents AS tsemaco WHERE tsemaco.section_idx = tsema.idx) AS content_cnt";
            $aLists = $this->oMainSection->getList($this->where, $field);

            // set view datas
            $this->data_set['HTML']['type']  = $this->oMainSection->getTypes();
            $this->data_set['HTML']['state'] = $this->oMainSection->getStates();
            $this->data_set['LIST']          = $aLists; // 리스트 데이터
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
     * 조건절 생성
     *
     * @return void
     */
    protected function getWhere($aParams = [])
    {
        $this->where = [];
        // 타입 검색
        if ( empty($aParams['type']) === false ) {
            $this->where[] = "tsema.type = :type:";
            $this->oMainSection->addBindData(['type' => $aParams['type']]);
        }

        // 오픈여부 검색
        if ( empty($aParams['state']) === false ) {
            $this->where[] = "tsema.state = :state:";
            $this->oMainSection->addBindData(['state' => $aParams['state']]);
        }

        // 검색어
        if ( empty($aParams['search_text']) === false ) {
            $this->where[] = "tsema.name LIKE :search_text:";
            $this->oMainSection->addBindData(['search_text' => '%' . $aParams['search_text'] . '%']);
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
            'body'      => 'manage/main/lists',
            'import_js' => 'manage/main/lists.js',
        ];
    }
}