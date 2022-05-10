<?php namespace App\Controllers\Board\Faq;

/* ===================================================================
	FAQ 리스트 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\Board\FaqModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Lists extends BaseController
{
    protected FaqModel $oFaq;

    protected array $param;
    protected array $where;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 2, '접근권한이 없습니다.');

        // 게시판 FAQ 모델
        $this->oFaq = new FaqModel();
    }

    /**
     * FAQ 리스트 페이지
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            // 파라미터 체크
            $this->chkInputParam();

            // 카테고리 검색일때는 sort
            if ( $this->param['category'] !== '' ) {
                $this->param['view_count'] = 0;
            }

            // 조건절 생성
            $this->getWhere();

            $offset = $this->param['view_count'] * ($this->param['page'] - 1);
            $limit  = $this->param['view_count'];

            // 전체 카운트
            $iTotalCount = $this->oFaq->getTotalCount($this->where);

            // set view datas
            $this->data_set['HTML']['search_type']   = $this->oFaq->getSearchType();
            $this->data_set['HTML']['category_list'] = $this->oFaq->getCategoryList();
            $this->data_set['HTML']['state']         = $this->oFaq->getStatusList();
            $this->data_set['HTML']['view_count']    = [20, 50, 100];
            $this->data_set['LIST']                  = $this->oFaq->getList($this->where, $this->orderby, $offset, $limit);
            $this->data_set['DATA']['total_count']   = $iTotalCount;

            // 페이지 처리
            if ( $this->param['view_count'] > 0 ) {
                $this->data_set['HTML']['pagination'] = $this->oFaq->paginator($this->param['page'], $iTotalCount, $this->param['view_count'], 5, $this->param);
            }
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
                'search_type' => ['default' => '', 'rules' => '', 'error' => ''],
                'search_text' => ['default' => '', 'rules' => '', 'error' => ''],
                'state'       => ['default' => '', 'rules' => '', 'error' => ''],
                'category'    => ['default' => ''],
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
        $this->where   = [];
        $this->orderby = "`tbofa`.`idx` DESC";

        // 상태값 검색
        if ( $this->param['state'] !== '' ) {
            $this->where[] = "`tbofa`.state = :state:";
            $this->oFaq->addBindData(['state' => $this->param['state']]);
        }

        // 카테고리 검색
        if ( $this->param['category'] !== '' ) {
            $this->where[] = "`tbofa`.category = :category:";
            $this->oFaq->addBindData(['category' => $this->param['category']]);

            $this->orderby = "`tbofa`.`sort` ASC";
        }

        // 검색어
        if ( empty($this->param['search_type']) === false && empty($this->param['search_text']) === false ) {
            switch ( $this->param['search_type'] ) {
                default:
                    $this->where[] = "`tbofa`." . $this->param['search_type'] . " LIKE :search_text:";
                    $this->oFaq->addBindData([
                        'search_text' => "%" . $this->param['search_text'] . '%',
                    ]);
            }
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
            'body'      => 'board/faq/lists',
            'import_js' => 'board/faq/lists.js',
            'paging'    => 'common/paging',
        ];
    }
}