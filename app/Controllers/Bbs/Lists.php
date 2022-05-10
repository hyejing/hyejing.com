<?php namespace App\Controllers\Bbs;

/* ===================================================================
    컨텐츠 리스트 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\BbsModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Lists extends BaseController
{
    protected BbsModel $oBbs;
    protected array $param;
    protected array $where;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true);

        // 컨텐츠 모델 로드
        $this->oBbs = new BbsModel();
    }

    /**
     * 메뉴 리스트 페이지
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            // 파라미터 체크
            $this->chkInputParam();

            // 조건절 생성
            $this->getWhere();

            // 페이징 데이터
            $offset = $this->param['view_count'] * ($this->param['page'] - 1);
            $limit  = $this->param['view_count'];

            // 전체 카운트
            $iTotalCount = $this->oBbs->getTotalCount();

            // getList
            $aLists = $this->oBbs->getList('tbb.*, tme.id, tme.nick', $offset, $limit);

            // set view datas
            $this->data_set['HTML']['state']       = $this->oBbs->getStatusList();
            $this->data_set['HTML']['search_type'] = $this->oBbs->getSearchType();
            $this->data_set['HTML']['view_count']  = [20, 50, 100];
            $this->data_set['HTML']['pagination']  = $this->oBbs->paginator($this->param['page'], $iTotalCount, $this->param['view_count'], 5, $this->param);
            $this->data_set['LIST']                = $aLists;
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
     * 조건절 생성
     *
     * @return void
     */
    protected function getWhere()
    {
        try {
            // 상태값 검색
            if ( $this->param['state'] !== '' ) {
                $this->oBbs->setWhere("tbb.state = :state:");
                $this->oBbs->addBindData(['state' => $this->param['state']]);
            }

            // 등록일
            if ( empty($this->param['start_date']) === false && empty($this->param['end_date']) === false ) {
                $this->oBbs->setWhere("tbb." . $this->param['date_type'] . " BETWEEN :start_date: AND :end_date:");
                $this->oBbs->addBindData([
                    'start_date' => $this->param['start_date'] . ' 00:00:00',
                    'end_date'   => $this->param['end_date'] . ' 23:59:59',
                ]);
            }

            // 검색어
            if ( empty($this->param['search_type']) === false && empty($this->param['search_text']) === false ) {
                switch ( $this->param['search_type'] ) {
                    case 'id':
                    case 'nick':
                        $this->oBbs->setWhere("tme." . $this->param['search_type'] . " LIKE :search_text:");
                        $this->oBbs->addBindData([
                            'search_text' => "%" . $this->param['search_text'] . "%",
                        ]);
                        break;
                    default:
                        $this->oBbs->setWhere("tme." . $this->param['search_type'] . " LIKE :search_text:");
                        $this->oBbs->addBindData([
                            'search_text' => "%" . $this->param['search_text'] . '%',
                        ]);
                }
            }
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
        }
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
                'date_type'   => ['default' => 'reg_date', 'rules' => '', 'error' => ''],
                'search_type' => ['default' => '', 'rules' => '', 'error' => ''],
                'search_text' => ['default' => '', 'rules' => '', 'error' => ''],
                'state'       => ['default' => '', 'rules' => '', 'error' => ''],
                'view_count'  => ['default' => 20],
                'page'        => ['default' => 1],
            ];
            $this->param = $this->chkParam($params, 'get');
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
            exit;
        }
    }

    /**
     * 입력테스트
     *
     * @return false
     */
    public function add()
    {
        try {
            //입력테스트
            return false;
            $this->oBbs->setMidx($this->data_set['USER']['idx']);
            // echo $this->oBbs->registContents();

            for ( $i = 1; $i < 1000; $i++ ) {
                $this->oBbs->setTitle('컨텐츠 등록 테스트 입니다. ' . $i);
                $this->oBbs->setCash(3 + $i);
                $this->oBbs->setSort(($this->oBbs->getMaxSort()) + 1);
                $this->oBbs->registContents();
            }
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
            exit;
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
            'body'      => 'bbs/lists',
            'import_js' => 'bbs/lists.js',
            'paging'    => 'common/paging',
        ];
    }
}