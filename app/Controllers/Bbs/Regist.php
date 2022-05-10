<?php namespace App\Controllers\Bbs;

/* ===================================================================
    컨텐츠 입력 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\BbsModel;
use App\Models\MemberModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Regist extends BaseController
{
    protected BbsModel $oBbs;
    protected MemberModel $oMember;
    protected array $param;
    protected array $where;
    protected array $aCate;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true);

        // 컨텐츠 모델 로드
        $this->oBbs    = new BbsModel();
        
        // 회원 모델 로드
        $this->oMember = new MemberModel();

        // 카테고리
        $this->aCate = $this->oBbs->getCate();
    }

    /**
     * 입력페이지
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            //회원번호 설정
            $this->oMember->setIdx(1);
            //회원정보
            $aLists = $this->oMember->getInfo();


            //vv($aVideoUrl);
            // set view data
            $this->data_set['LIST'] = $aLists;
            $this->data_set['CATE'] = $this->aCate;


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
     * 컨텐츠 정보 저장
     */
    public function proc()
    {
        try {
            // 파라미터 체크
            $this->chkInputParam();

            
            // 관리자페이지에서 업로드시 기본업로드 회원 설정
            $this->oBbs->setMidx(1);

            // 입력값
            $this->oBbs->setTitle($this->param['title']);
            $this->oBbs->setCash($this->param['cash']);
            $this->oBbs->setDetail($this->param['detailText']);
            $this->oBbs->setFile($this->param['filePath']);
            $this->oBbs->setCate($this->param['cate']);
            $this->oBbs->setSort(($this->oBbs->getMaxSort()) + 1);

            // insert
            $this->oBbs->registContents();

            $aOutPut = [
                'success' => true,
                'code'    => '1111',
                'msg'     => '입력 하였습니다.',
                'data'    => []
            ];

        }
        catch ( Exception $e ) {
            $aOutPut = [
                'success' => false,
                'code'    => $e->getCode(),
                'msg'     => $e->getMessage(),
                'data'    => $e
            ];
        }
        $this->displayJson($aOutPut);
    }


    /**
     * 조건절 생성
     *
     * @return void
     */
    protected function getWhere()
    {
        // idx 검색
        if ( $this->param['idx'] !== '' ) {

            $this->oBbs->setWhere("tbb.idx = :idx:");
            $this->oBbs->addBindData(['idx' => $this->param['idx']]);
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
                'title'      => ['default' => '', 'rules' => 'required', 'error' => '제목을 입력해 주세요'],
                'detailText' => ['default' => '', 'rules' => 'required', 'error' => '내용을 입력해 주세요'],
                'filePath'   => ['default' => '', 'rules' => 'required', 'error' => '파일을 선택해 주세요'],
                'cash'       => ['default' => '', 'rules' => 'required', 'error' => '판매가격을 입력해 주세요'],
                'cate'       => ['default' => '', 'rules' => 'required', 'error' => '카테고리를 선택해 주세요']
            ];
            $this->param = $this->chkParam($params);
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
        }
    }

    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body'      => 'bbs/regist',
            'import_js' => 'bbs/regist.js'
        ];
    }
}