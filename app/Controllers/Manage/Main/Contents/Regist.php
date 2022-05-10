<?php namespace App\Controllers\Manage\Main\Contents;

/* ===================================================================
    메인 섹션 컨텐츠 등록 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\Manage\SectionMainContentsModel;
use App\Models\BbsModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Regist extends BaseController
{
    protected SectionMainContentsModel $oSectionMainContents;
    protected BbsModel $oBbs;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 4, '접근권한이 없습니다.');

        // 메인섹션 컨텐츠 관리 모델
        $this->oSectionMainContents = new SectionMainContentsModel();

        // 컨텐츠 모델
        $this->oBbs = new BbsModel();
    }

    /**
     * 해당 섹션 내 컨텐츠 등록 페이지
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            $this->data_set['HTML']['states'] = $this->oSectionMainContents->getStates();
            $this->data_set['HTML']['bbs_idx_list'] = $this->oBbs->getList('tbb.idx, tbb.title', 0, 0);
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
     * 컨텐츠 등록
     */
    public function proc()
    {
        try {
            $params = [
                'bbs_idx'     => [
                    'default' => '',
                    'rules'   => 'required|min_length[1]',
                    'error'   => 'bbs_idx 를 입력해주세요.',
                ],
                'state'       => [
                    'default' => '',
                    'rules'   => 'required|min_length[1]',
                    'error'   => '상태를 선택 해 주세요.',
                ],
                'section_idx' => [
                    'default' => '',
                    'rules'   => 'required|min_length[1]',
                    'error'   => 'section_idx 가 존재 하지 않습니다.',
                ]
            ];

            // input check
            $aParams = $this->chkParam($params, 'post');

            // 섹션 컨텐츠 등록 액션
            $aResult = $this->registSectionContent($aParams);

            // fail db insert
            if ( $aResult === false ) {
                throw new Exception('등록 실패', '8897');
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
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'data'    => $e,
            ];
        }

        $this->displayJson($aOutPut);
    }

    /**
     * @param array $aParams
     * @return bool
     * @throws Exception
     */
    private function registSectionContent(array $aParams = []): bool
    {
        // bbs_idx set 처리
        $this->oBbs->setIdx($aParams['bbs_idx']);

        // bbs_idx가 실제 존재 하는지 체크 로직
        if ( $this->oBbs->getCheckIdxInfoCount() == 0 ) {
            throw new Exception('해당 BBS_IDX 가 존재 하지 않습니다.', '8896');
        }

        // insert data set
        $this->oSectionMainContents->setBbsIdx($aParams['bbs_idx']);
        $this->oSectionMainContents->setState($aParams['state']);
        $this->oSectionMainContents->setSectionIdx($aParams['section_idx']);

        // 중복 bbs_idx 체크 로직
        if ( $this->oSectionMainContents->chkBbsIdx() > 0 ) {
            throw new Exception('동일한 컨텐츠가 존재 합니다.', '8896');
        }

        // 컨텐츠 등록
        return $this->oSectionMainContents->registSectionContent();
    }

    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body' => 'manage/main/contents/regist'
        ];
    }
}