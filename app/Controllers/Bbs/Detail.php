<?php namespace App\Controllers\Bbs;

/* ===================================================================
    컨텐츠 상세 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\BbsModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Detail extends BaseController
{
    protected BbsModel $oBbs;
    protected array $param;
    protected array $where;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->oBbs = new BbsModel();
    }

    /**
     * 상세페이지
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            // 파라미터 체크
            $this->chkInputParam();

            //컨텐츠 번호
            $this->oBbs->setIdx($this->param['idx']);

            // 조건절 생성
            $this->getWhere();


            //컨텐츠 정보
            $aLists = $this->oBbs->getBbs('tbb.*,tme.id,tme.nick');

            //파일 정보
            $aFileInfo = $this->oBbs->getBbsFile();

            //이미지 정보
            $aImageInfo = $this->oBbs->getBbsImage();

            //상세 정보
            $aDetaileInfo = $this->oBbs->getBbsDetail();

            $aVideoUrl = $this->oBbs->getBbsStreaming();
            //vv($aVideoUrl);
            // set view datas
            $this->data_set['LIST']           = $aLists;
            $this->data_set['DATA']['file']   = $aFileInfo;
            $this->data_set['DATA']['image']  = $aImageInfo;
            $this->data_set['DATA']['detail'] = $aDetaileInfo;
            //$this->data_set['DATA']['video']  = 'https://bitdash-a.akamaihd.net/content/MI201109210084_1/m3u8s/f08e80da-bf1d-4e3d-8899-f0f6155f6efa.m3u8';
            $this->data_set['DATA']['video']  = $aVideoUrl;

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
                'idx' => ['default' => '', 'rules' => 'required', 'error' => '잘못된 접근입니다.']
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


    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body'      => 'bbs/detail',
            'import_js' => 'bbs/detail.js'
        ];
    }
}