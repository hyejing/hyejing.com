<?php namespace App\Controllers\Meta;

/* ===================================================================
	컨텐츠 메타 관리 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\MetaModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Lists extends BaseController
{
    protected MetaModel $meta;
    protected array $param;
    protected array $where;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 9, '접근권한이 없습니다.');

        $this->meta = new MetaModel();
    }

    /**
     * 관리자 리스트 페이지
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

            // 리스트 및 전체 카운트 가져오기
            $iTotalCount = $this->meta->getTotalCount($this->where);
            $lists       = $this->meta->getList($this->where, $offset, $limit);

            // set view datas
            $this->data_set['HTML']['state']       = $this->meta->getStatusList();
            $this->data_set['HTML']['search_type'] = $this->meta->getSearchType();
            $this->data_set['HTML']['view_count']  = [20, 50, 100];
            $this->data_set['HTML']['pagination']  = $this->meta->paginator($this->param['page'], $iTotalCount, $this->param['view_count'], 5, $this->param);
            $this->data_set['LIST']                = $lists;
            $this->data_set['DATA']['total_count'] = $iTotalCount;
            $this->data_set['LINK']['details']     = URL_DOMAIN . "/admin/details";
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
    protected function getWhere()
    {
        $this->where = [];
        // 상태값 검색
        if ( empty($this->meta->getStatusList()[$this->param['state']]) === false ) {
            $this->where[] = "state = :state:";
            $this->meta->addBindData(['state' => $this->param['state']]);
        }

        // 로그인 시간
        if ( empty($this->param['start_date']) === false && empty($this->param['end_date']) === false ) {
            $this->where[] = $this->param['date_type'] . " BETWEEN :start_date: AND :end_date:";
            $this->meta->addBindData([
                'start_date' => $this->param['start_date'] . ' 00:00:00',
                'end_date'   => $this->param['end_date'] . ' 23:59:59',
            ]);
        }

        // 검색어
        if ( empty($this->param['search_type']) === false && empty($this->param['search_text']) === false ) {
            switch ( $this->param['search_type'] ) {
                default:
                    $this->where[] = $this->param['search_type'] . " LIKE :search_text:";
                    $this->meta->addBindData([
                        'search_text' => "%" . $this->param['search_text'] . '%',
                    ]);
            }
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
                'date_type'   => ['default' => 'login_date', 'rules' => '', 'error' => ''],
                'search_type' => ['default' => '', 'rules' => '', 'error' => ''],
                'search_text' => ['default' => '', 'rules' => '', 'error' => ''],
                'state'       => ['default' => '', 'rules' => '', 'error' => ''],
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
     * view page 셋팅
     *
     * @return void
     */
    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body'      => 'meta/lists',
            'import_js' => 'meta/lists.js',
            'paging'    => 'common/paging',
        ];
    }
}