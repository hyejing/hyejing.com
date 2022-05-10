<?php namespace App\Controllers\Admin;

/* ===================================================================
	관리자 리스트 페이지
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
    protected array $param;
    protected array $where;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 4, '접근권한이 없습니다.');

        $this->admin = new AdminModel();
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

            // 페이징용 설정 : 페이지당 출력건수로 offset, limit 값 계산
            $offset = $this->param['view_count'] * ($this->param['page'] - 1);
            $limit  = $this->param['view_count'];

            // 리스트 전체 카운트 가져오기
            $iTotalCount = $this->admin->getTotalCount($this->where);

            // 리스트 출력개수 종류 추가
            $this->admin->addViewCount(10);

            // set view datas
            $this->data_set['HTML']['title']        = '관리자 목록';
            $this->data_set['HTML']['admin_level']  = $this->admin->getManagerLevel();
            $this->data_set['HTML']['admin_state']  = $this->admin->getStatusList();
            $this->data_set['HTML']['search_type']  = $this->admin->getSearchType();
            $this->data_set['HTML']['view_count']   = $this->admin->getViewCount();
            $this->data_set['HTML']['pagination']   = $this->admin->paginator($this->param['page'], $iTotalCount, $this->param['view_count'], 5, $this->param);
            $this->data_set['LIST']                 = $this->admin->getList($this->where, $offset, $limit);
            $this->data_set['DATA']['total_count']  = $iTotalCount;
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
        if ( empty($this->admin->getStatusList()[$this->param['state']]) === false ) {
            $this->where[] = "tad.state = :state:";
            $this->admin->addBindData(['state' => $this->param['state']]);
        }

        // 권한등급 검색
        if ( empty($this->admin->getManagerLevel()[$this->param['level']]) === false ) {
            $this->where[] = "tad.level = :level:";
            $this->admin->addBindData(['level' => $this->param['level']]);
        }

        // 검색어
        if ( empty($this->param['search_type']) === false && empty($this->param['search_text']) === false ) {
            switch ( $this->param['search_type'] ) {
                default:
                    $this->where[] = "tad." . $this->param['search_type'] . " LIKE :search_text:";
                    $this->admin->addBindData([
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
                'level'       => ['default' => '', 'rules' => '', 'error' => ''],
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
            'body'   => 'admin/lists',
            'paging' => 'common/paging',
        ];
    }
}
