<?php namespace App\Controllers\Manage\Main\Contents;

/* ===================================================================
	메인 섹션 컨텐츠 리스트 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\Manage\SectionMainContentsModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Lists extends BaseController
{
    protected SectionMainContentsModel $oSectionMainContents;
    protected array $where;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 4, '접근권한이 없습니다.');

        // 메인섹션 컨텐츠 관리 모델
        $this->oSectionMainContents = new SectionMainContentsModel();
    }

    /**
     * 컨텐츠 관리 리스트 페이지
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            $params = [
                'section_idx' => [
                    'default' => '',
                    'rules'   => 'required|min_length[1]',
                    'error'   => '섹션 idx가 존재 하지 않습니다.',
                ]
            ];

            // input check
            $aParams = $this->chkParam($params, 'get');

            // Section idx set 처리
            $this->oSectionMainContents->setSectionIdx($aParams['section_idx']);

            // 해당 섹션 컨텐츠 리스트
            $aLists = $this->oSectionMainContents->getSectionMainContents(['tsemaco.section_idx=:iSectionIdx:']);

            $this->data_set['LIST']                = $aLists;                 // 리스트 데이터
            $this->data_set['DATA']['total_count'] = sizeof($aLists);         // 섹션의 검색된 컨텐츠 총 갯수
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
     * view page 셋팅
     *
     * @return void
     */
    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body'      => 'manage/main/contents/lists',
            'import_js' => 'manage/main/contents/lists.js',
            'paging'    => 'common/paging',
        ];
    }
}