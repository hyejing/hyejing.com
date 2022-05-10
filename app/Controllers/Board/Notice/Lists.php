<?php namespace App\Controllers\Board\Notice;

/* ===================================================================
	공지사항 리스트 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\Board\NoticeModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Lists extends BaseController
{
    protected NoticeModel $oNotice;

    protected array $param;
    protected array $where;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 2, '접근권한이 없습니다.');

        // 게시판 공지사항 모델
        $this->oNotice = new NoticeModel();
    }

    /**
     * 공지사항 리스트 페이지
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            // 파라미터 체크
            $this->chkInputParam();

            // 조건절 생성
            $this->getWhere();

            $offset = $this->param['view_count'] * ($this->param['page'] - 1);
            $limit  = $this->param['view_count'];

            // 전체 카운트
            $iTotalCount = $this->oNotice->getTotalCount($this->where);

            // set view data
            $this->data_set['HTML']['title']       = "공지사항";
            $this->data_set['HTML']['search_type'] = $this->oNotice->getSearchType();
            $this->data_set['HTML']['is_top_type'] = $this->oNotice->getIsTopType();
            $this->data_set['HTML']['state']       = $this->oNotice->getStatusList();
            $this->data_set['HTML']['view_count']  = [20, 50, 100];
            $this->data_set['HTML']['pagination']  = $this->oNotice->paginator($this->param['page'], $iTotalCount, $this->param['view_count'], 5, $this->param);
            $this->data_set['LIST']                = $this->oNotice->getList($this->where, $offset, $limit);
            $this->data_set['DATA']['total_count'] = $iTotalCount;
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
     * parameters 체크
     *
     * @throws Exception
     */
    protected function chkInputParam()
    {
        try {
            // parameters
            $params      = [
                'start_date'  => ['default' => '', 'rules' => '', 'error' => ''],
                'end_date'    => ['default' => '', 'rules' => '', 'error' => ''],
                'search_type' => ['default' => '', 'rules' => '', 'error' => ''],
                'search_text' => ['default' => '', 'rules' => '', 'error' => ''],
                'match_type'  => ['default' => 'match', 'rules' => '', 'error' => ''],
                'state'       => ['default' => '', 'rules' => '', 'error' => ''],
                'is_top'      => ['default' => '', 'rules' => '', 'error' => ''],
                'view_count'  => ['default' => 20],
                'page'        => ['default' => 1],
            ];
            $this->param = $this->chkParam($params, 'get');
        }
        catch ( Exception $e ) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 조건절 생성
     *
     * @return void
     */
    protected function getWhere()
    {
        $this->where = [];

        // 상태값 검색
        if ( $this->param['state'] !== '' ) {
            $this->where[] = "`tbono`.state = :state:";
            $this->oNotice->addBindData(['state' => $this->param['state']]);
        }

        // 상단 노출값 검색
        if ( $this->param['is_top'] !== '' ) {
            $this->where[] = "`tbono`.is_top = :is_top:";
            $this->oNotice->addBindData(['is_top' => $this->param['is_top']]);
        }

        // 검색어
        if ( empty($this->param['search_type']) === false && empty($this->param['search_text']) === false ) {
            // 검색타입별 테이블 지정
            switch ( $this->param['search_type'] ) {
                // 컨텐츠 테이블
                case 'contents':
                    $tbl_prefix = "`tbonoco`";
                    break;

                // 게시판 테이블
                case 'title':
                default:
                    $tbl_prefix = "`tbono`";
                    break;
            }

            // 일치/포함 구분
            if ( empty($this->param['match_type']) === false && $this->param['match_type'] == "include" ) {
                // 포함
                $this->where[] = $tbl_prefix . "." . $this->param['search_type'] . " LIKE :search_text:";
                $this->oNotice->addBindData([
                    'search_text' => "%" . $this->param['search_text'] . '%'
                ]);
            }
            else {
                // 일치
                $this->where[] = $tbl_prefix . "." . $this->param['search_type'] . " = :search_text:";
                $this->oNotice->addBindData([
                    'search_text' => $this->param['search_text']
                ]);
            }
        }

        // 시작일
        if ( empty($this->param['start_date']) === false ) {
            $this->where[] = "`tbono`.`reg_date` >= :start_date:";
            $this->oNotice->addBindData([
                'start_date' => date("Y-m-d", strtotime($this->param['start_date']))
            ]);
        }
        // 종료일
        if ( empty($this->param['end_date']) === false ) {
            $this->where[] = "`tbono`.`reg_date` >= :end_date:";
            $this->oNotice->addBindData([
                'end_date' => date("Y-m-d", strtotime($this->param['end_date']))
            ]);
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
            'body'      => 'board/notice/lists',
            'import_js' => 'board/notice/lists.js',
            'paging'    => 'common/paging',
        ];
    }
}