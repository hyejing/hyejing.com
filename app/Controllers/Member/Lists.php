<?php namespace App\Controllers\Member;

/* ===================================================================
    회원 리스트 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\MemberModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Psr\Log\LoggerInterface;

class Lists extends BaseController
{
    protected MemberModel $member;
    protected array $param;
    protected array $where;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 4, '접근권한이 없습니다.');

        $this->member = new MemberModel();
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

            // 페이징용 설정 : 페이지당 출력건수로 offset, limit 값 계산
            $offset = $this->param['view_count'] * ($this->param['page'] - 1);
            $limit  = $this->param['view_count'];

            // 리스트 및 전체 카운트 가져오기
            $iTotalCount = $this->member->getTotalCount($this->where);

            // set view datas
            $this->data_set['HTML']['state']       = $this->member->getStatusList();
            $this->data_set['HTML']['search_type'] = $this->member->getSearchType();
            $this->data_set['HTML']['view_count']  = [20, 50, 100];
            $this->data_set['HTML']['pagination']  = $this->member->paginator($this->param['page'], $iTotalCount, $this->param['view_count'], 5, $this->param);
            $this->data_set['LIST']                = $this->member->getList($this->where, $offset, $limit);
            $this->data_set['DATA']['total_count'] = $iTotalCount;
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
     * 메뉴 리스트 엑셀 페이지
     */
    public function excel()
    {
        // 메모리 및 타임아웃 시간 무제한 풀기
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);

        try {
            // 파라미터 체크
            $this->chkInputParam();

            // 조건절 생성
            $this->getWhere();

            // 리스트 가져오기
            $lists = $this->member->getList($this->where, 0, 0);

            // 엑셀 라이브러리 로드
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->getTabColor()->setRGB('FF0000');               // 탭칼라
            $sheet->getDefaultRowDimension()->setRowHeight(15);    // 기본행 높이 설정

            // 제목
            $sheet->setCellValue('A1', 'Idx');
            $sheet->setCellValue('B1', '상태');
            $sheet->setCellValue('C1', '아이디');
            $sheet->setCellValue('D1', '닉네임');
            $sheet->setCellValue('E1', '이름');
            $sheet->setCellValue('F1', '성별');
            $sheet->setCellValue('G1', '생년월일');
            $sheet->setCellValue('H1', '마지막 로그인');
            $sheet->setCellValue('I1', '활동 IP');
            $sheet->setCellValue('J1', '가입일');

            $i = 2;
            foreach ( $lists as $row ) {
                $sheet->setCellValue('A' . $i, $row['idx']);
                $sheet->setCellValue('B' . $i, $row['state_txt']);
                $sheet->setCellValueExplicit('C' . $i, $row['id'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('D' . $i, $row['nick'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('E' . $i, $row['name'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('F' . $i, $row['gender_txt']);
                $sheet->setCellValue('G' . $i, $row['birth']);
                $sheet->setCellValue('H' . $i, $row['login_last']);
                $sheet->setCellValue('I' . $i, $row['login_ip']);
                $sheet->setCellValue('J' . $i, $row['reg_date']);

                $i++;
            }

            if ( false ) {
                // 셀너비 자동설정
                foreach ( $sheet->getColumnIterator() as $column ) {
                    $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(false);
                }
            }
            else {
                // 셀너비 수동설정
                $columns = [
                    'A' => 9,
                    'B' => 10,
                    'C' => 30,
                    'D' => 30,
                    'E' => 8,
                    'F' => 8,
                    'G' => 15,
                    'H' => 20,
                    'I' => 20,
                    'J' => 20,
                ];
                foreach ( $sheet->getColumnIterator() as $column ) {
                    if ( isset($columns[$column->getColumnIndex()]) === false ) {
                        $columns[$column->getColumnIndex()] = 20;
                    }

                    $sheet->getColumnDimension($column->getColumnIndex())->setWidth($columns[$column->getColumnIndex()]);
                }
            }

            // output excel
            $writer   = new Xlsx($spreadsheet);
            $filename = 'member_lists_' . date('ymd_his');
            header('Content-Type: application/vnd.ms-excel;charset=UTF-8"');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
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
        if ( empty($this->param['state']) === false ) {
            $this->where[] = "tme.state = :state:";
            $this->member->addBindData(['state' => $this->param['state']]);
        }

        // 로그인 시간
        if ( empty($this->param['start_date']) === false && empty($this->param['end_date']) === false ) {
            $alias         = $this->param['date_type'] == "login_last" ? "mi" : "m";   // 테이블 구분
            $this->where[] = $alias . "." . $this->param['date_type'] . " BETWEEN :start_date: AND :end_date:";
            $this->member->addBindData([
                'start_date' => $this->param['start_date'] . ' 00:00:00',
                'end_date'   => $this->param['end_date'] . ' 23:59:59',
            ]);
        }

        // 검색어
        if ( empty($this->param['search_type']) === false && empty($this->param['search_text']) === false ) {
            switch ( $this->param['search_type'] ) {
                case 'login_ip':
                case 'join_ip':
                case 'login_last':
                    $this->where[] = "tmein." . $this->param['search_type'] . " LIKE :search_text:";
                    $this->member->addBindData([
                        'search_text' => "%" . $this->param['search_text'] . "%",
                    ]);
                    break;
                default:
                    $this->where[] = "tme." . $this->param['search_type'] . " LIKE :search_text:";
                    $this->member->addBindData([
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
                'date_type'   => ['default' => 'login_last', 'rules' => '', 'error' => ''],
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
            'body'      => 'member/lists',
            'import_js' => 'member/member.js',
            'paging'    => 'common/paging',
        ];
    }
}