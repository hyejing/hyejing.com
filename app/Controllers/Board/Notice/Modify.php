<?php namespace App\Controllers\Board\Notice;

/* ===================================================================
    공지사항 수정 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\Board\NoticeModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Modify extends BaseController
{
    protected NoticeModel $oNotice;
    protected array $param;
    protected array $where;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 2, '접근권한이 없습니다.');

        // 게시판 공지사항 모델
        $this->oNotice = new NoticeModel();
    }

    /**
     * 공지사항 수정 페이지
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            // parameters
            $params      = [
                'idx' => [
                    'default' => '',
                    'rules'   => 'required|is_natural_no_zero',
                    'error'   => '잘못된 접근입니다.'
                ]
            ];
            $this->param = $this->chkParam($params, 'get');

            // 공지사항 정보 가져오기 by idx
            $this->oNotice->setIdx($this->param['idx']);

            // set view data
            $this->data_set['HTML']['is_top_type'] = $this->oNotice->getIsTopType();
            $this->data_set['HTML']['state']       = $this->oNotice->getStatusList();
            $this->data_set['DATA']                = $this->oNotice->getInfo();
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
        }

        $this->displayPop();
    }

    /**
     * 공지사항 수정 proc
     */
    public function proc()
    {
        try {
            $params = [
                'idx'      => [
                    'default' => '',
                    'rules'   => 'required|is_natural_no_zero',
                    'error'   => '잘못된 접근입니다',
                ],
                'title'    => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => '제목을 확인해주세요',
                ],
                'state'    => [
                    'default' => '',
                    'rules'   => 'required|is_natural',
                    'error'   => '노출여부를 선택 해 주세요.',
                ],
                'is_top'   => [
                    'default' => '',
                    'rules'   => 'required|is_natural',
                    'error'   => '상단 노출여부를 선택 해 주세요.',
                ],
                'contents' => [
                    'default' => '',
                    'rules'   => 'required|min_length[1]',
                    'error'   => '내용을 입력해주세요.',
                ],
            ];
            // 파라미터 체크
            $aParams = $this->chkParam($params, 'post');

            // 공지사항 수정
            $aResult = $this->modifyNotice($aParams);

            if ( $aResult === false ) {
                throw new Exception('잘못된 값이 있습니다.', '8897');
            }

            $aOutPut = [
                'success' => true,
                'code'    => '1111',
                'msg'     => '수정 완료하였습니다.',
                'data'    => []
            ];
        }
        catch ( Exception $e ) {
            $aOutPut = [
                'success' => false,
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'code'    => $e->getCode(),
                'msg'     => $e->getMessage(),
            ];
        }

        $this->displayJson($aOutPut);
    }

    /**
     * 공지사항 수정
     *
     * @param array $aParams
     * @return bool
     * @throws Exception
     */
    private function modifyNotice(array $aParams): bool
    {
        // update data set
        $this->oNotice->setIdx($aParams['idx']);
        $this->oNotice->setNoticeIdx($aParams['idx']);
        $this->oNotice->setAdminIdx($this->user->getIdx());
        $this->oNotice->setTitle($aParams['title']);
        $this->oNotice->setIsTop($aParams['is_top']);
        $this->oNotice->setContents($aParams['contents']);
        $this->oNotice->setState($aParams['state']);

        // update action
        return $this->oNotice->modifyNotice();
    }

    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body'      => 'board/notice/modify',
            'import_js' => 'board/notice/modify.js',
        ];
    }
}