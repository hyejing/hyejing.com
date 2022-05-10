<?php namespace App\Controllers\Manage\Main\Contents;

/* ===================================================================
	메인 섹션 컨텐츠 수정 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\Manage\SectionMainContentsModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Modify extends BaseController
{
    protected SectionMainContentsModel $oSectionMainContents;
    protected array $param;
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
     * 컨텐츠 수정 페이지
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            // check parameters
            $params = [
                'idx' => [
                    'default' => 0,
                    'rules'   => 'required|is_natural_no_zero',
                    'error'   => '컨텐츠 idx가 존재 하지 않습니다.',
                ]
            ];

            // Input get data
            $aParams = $this->chkParam($params, 'get');

            // idx set 처리
            $this->oSectionMainContents->setIdx($aParams['idx']);

            $this->data_set['HTML']['states'] = $this->oSectionMainContents->getStates();
            $this->data_set['DATA']['info']   = $this->oSectionMainContents->getModifyInfo();
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
        }

        // head 예외 처리
        $this->displayPop(['head']);
    }

    /**
     * 컨텐츠 상태 수정
     */
    public function proc()
    {
        try {
            $params = [
                'idx'   => [
                    'default' => 0,
                    'rules'   => 'required|is_natural_no_zero',
                    'error'   => 'idx 를 입력해주세요.',
                ],
                'state' => [
                    'default' => '',
                    'rules'   => 'required|min_length[1]',
                    'error'   => '상태를 선택 해 주세요.',
                ]
            ];

            $aParams = $this->chkParam($params, 'post');

            // 컨텐츠 관리 상태 수정
            $aResult = $this->infoModify($aParams);

            if ( $aResult === false ) {
                throw new Exception('실패', '8897');
            }

            $aOutPut = [
                'success' => true,
                'code'    => '1111',
                'msg'     => '성공 하였습니다.',
                'data'    => []
            ];
        }
        catch ( Exception $e ) {
            $aOutPut = [
                'success' => false,
                'code'    => '8896',
                'msg'     => '실패',
                'data'    => $e
            ];
        }

        $this->displayJson($aOutPut);
    }

    /**
     * @param array $aParams
     * @return bool
     * @throws Exception
     */
    private function infoModify(array $aParams): bool
    {
        // data set
        $this->oSectionMainContents->setIdx($aParams['idx']);
        $this->oSectionMainContents->setState($aParams['state']);

        // 정보 업데이트
        return $this->oSectionMainContents->infoModify();
    }

    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body'      => 'manage/main/contents/modify',
            'import_js' => 'manage/main/contents/lists.js',
        ];
    }
}