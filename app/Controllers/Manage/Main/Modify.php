<?php namespace App\Controllers\Manage\Main;

/* ===================================================================
	메인 섹션 수정 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\Manage\MainSectionModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Modify extends BaseController
{
    protected MainSectionModel $oMainSection;
    protected array $param;
    protected array $where;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 4, '접근권한이 없습니다.');

        // 메인섹션 모델 오브젝트 생성
        $this->oMainSection = new MainSectionModel();
    }

    /**
     * 섹션 수정 페이지
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            // check parameters
            $params  = [
                'idx' => [
                    'default' => '0',
                    'rules'   => 'required|is_natural_no_zero',
                    'error'   => '잘못된 접근입니다.',
                ]
            ];
            $aParams = $this->chkParam($params, 'get');

            // get info
            $this->oMainSection->setIdx($aParams['idx']);

            $this->data_set['HTML']['types']  = $this->oMainSection->getTypes();
            $this->data_set['HTML']['states'] = $this->oMainSection->getStates();
            $this->data_set['DATA']           = $this->oMainSection->getInfo();
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
        }

        $this->displayPop(['head']);
    }

    /**
     * 메뉴 리스트 페이지
     */
    public function proc()
    {
        try {
            $params  = [
                'idx'   => [
                    'default' => 0,
                    'rules'   => 'required|is_natural_no_zero',
                    'error'   => 'idx 를 입력해주세요.',
                ],
                'type'  => [
                    'default' => '1',
                    'rules'   => 'required|is_natural_no_zero',
                    'error'   => '섹션 타입을 선택해 주세요.',
                ],
                'name'  => [
                    'default' => '',
                    'rules'   => 'required|min_length[1]',
                    'error'   => '섹션 이름을 입력해주세요.',
                ],
                'state' => [
                    'default' => '1',
                    'rules'   => 'required|is_natural',
                    'error'   => '상태를 선택해 주세요.',
                ],
            ];
            $aParams = $this->chkParam($params, 'post');

            //섹션 상세 내용 수정
            $aResult = $this->infoModify($aParams);

            if ( $aResult === false ) {
                throw new Exception('잘못된 값이 있습니다.', '8897');
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
                'code'    => $e->getCode(),
                'msg'     => $e->getMessage(),
                'data'    => $e->getFile() . '=' . $e->getLine(),
            ];
        }

        $this->displayJson($aOutPut);
        exit;
    }

    /**
     * @param array $aParams
     * @return bool
     * @throws Exception
     */
    private function infoModify(array $aParams): bool
    {
        // data set
        $this->oMainSection->setIdx($aParams['idx']);
        $this->oMainSection->setName($aParams['name']);
        $this->oMainSection->setState($aParams['state']);
        $this->oMainSection->setType($aParams['type']);

        // 섹션 이름 중복 체크 로직
        if ( $this->oMainSection->chkName() === true ) {
            throw new Exception('동일한 이름의 섹션이 존재합니다.', '8896');
        }

        // 정보 업데이트
        return $this->oMainSection->modifyInfo();
    }

    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body'      => 'manage/main/modify',
            'import_js' => 'manage/main/lists.js',
        ];
    }
}