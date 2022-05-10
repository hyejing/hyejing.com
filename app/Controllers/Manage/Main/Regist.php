<?php namespace App\Controllers\Manage\Main;

/* ===================================================================
    메인 섹션 등록 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\Manage\MainSectionModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Regist extends BaseController
{
    protected MainSectionModel $oMainSection;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 4, '접근권한이 없습니다.');

        // 메인섹션 모델 오브젝트 생성
        $this->oMainSection = new MainSectionModel();
    }

    /**
     * 섹션 등록 페이지
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            $this->data_set['HTML']['types']  = $this->oMainSection->getTypes();
            $this->data_set['HTML']['states'] = $this->oMainSection->getStates();
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
            $params = [
                'type'  => [
                    'default' => '',
                    'rules'   => 'required|min_length[1]',
                    'error'   => '섹션 타입을 선택 해 주세요.',
                ],
                'name'  => [
                    'default' => '',
                    'rules'   => 'required|min_length[1]',
                    'error'   => '섹션 이름을 입력해주세요.',
                ],
                'state' => [
                    'default' => '',
                    'rules'   => 'required|min_length[1]',
                    'error'   => '섹션 오픈 여부를 선택 해 주세요.',
                ],
            ];

            //input check
            $aParams = $this->chkParam($params, 'post');
            $aResult = $this->registSection($aParams);

            // fail db insert
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
                'code'    => $e->getCode(),
                'msg'     => $e->getMessage(),
                'data'    => $e->getFile() . '=' . $e->getLine(),
            ];
        }

        $this->displayJson($aOutPut);
    }

    //메인 섹션 insert 액션
    private function registSection($aParams = []): bool
    {
        //insert data set
        $this->oMainSection->setName($aParams['name']);
        $this->oMainSection->setState($aParams['state']);
        $this->oMainSection->setType($aParams['type']);

        // 섹션 이름 중복 체크 로직
        if ( $this->oMainSection->chkName() === true ) {
            throw new Exception('동일한 이름의 섹션이 존재합니다.', '8896');
        }

        // insert action
        return $this->oMainSection->registSection();
    }

    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body' => 'manage/main/regist'
        ];
    }
}