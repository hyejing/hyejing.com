<?php namespace App\Controllers\Bbs;

/* ===================================================================
    컨텐츠 삭제
=================================================================== */

use App\Controllers\BaseController;
use App\Models\BbsModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Remove extends BaseController
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
     * 컨텐츠 삭제
     */
    public function index()
    {
        try {
            // 파라미터 체크
            $this->chkInputParam();

            //삭제 컨텐츠 번호
            $this->oBbs->setIdx($this->param['idx']);

            //update
            $this->oBbs->removeBbs();

            $aOutPut = [
                'success' => true,
                'code'    => '1111',
                'msg'     => '삭제 하였습니다.',
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
     * parameters 체크
     *
     * @throws Exception
     */
    protected function chkInputParam()
    {
        try {
            // parameters
            $params      = [
                'idx'        => ['default' => '', 'rules' => 'required', 'error' => '잘못된 접근입니다.'],
            ];
            $this->param = $this->chkParam($params);
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
            exit;
        }
    }
}