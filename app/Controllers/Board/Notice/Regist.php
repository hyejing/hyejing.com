<?php namespace App\Controllers\Board\Notice;

/* ===================================================================
    공지사항 등록 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\Board\NoticeModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use Psr\Log\LoggerInterface;

class Regist extends BaseController
{
    protected NoticeModel $oNotice;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 2, '접근권한이 없습니다.');

        // 게시판 공지사항 모델
        $this->oNotice = new NoticeModel();
    }

    /**
     * 공지사항 등록 페이지
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            // set view data
            $this->data_set['HTML']['is_top_type'] = $this->oNotice->getIsTopType();
            $this->data_set['HTML']['state']       = $this->oNotice->getStatusList();
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
     * 공지사항 등록 proc
     */
    public function proc()
    {
        try {
            $params = [
                'title'  => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => '제목을 입력해주세요',
                ],
                'is_top' => [
                    'default' => '',
                    'rules'   => 'required|is_natural',
                    'error'   => '상단 고정여부를 선택해 주세요.',
                ],
                'state'  => [
                    'default' => '',
                    'rules'   => 'required|is_natural',
                    'error'   => '출력여부를 선택해 주세요.',
                ],
                'contents' => [
                    'default' => '',
                    'rules'   => 'required|min_length[1]',
                    'error'   => '내용을 입력해주세요.',
                ],
            ];
            // 파라미터 체크
            $aParams = $this->chkParam($params, 'post');

            // 공지사항 등록
            $aResult = $this->registNotice($aParams);

            // fail db insert
            if ( $aResult === false ) {
                throw new Exception('실패', '8897');
            }

            $aOutPut = [
                'success' => true,
                'code'    => '1111',
                'msg'     => '등록 완료 하였습니다.',
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
     * 공지사항 등록
     *
     * @param array $aParams
     * @return bool
     * @throws Exception
     */
    private function registNotice(array $aParams): bool
    {
        //insert data set
        $this->oNotice->setAdminIdx($this->user->getIdx());
        $this->oNotice->setTitle($aParams['title']);
        $this->oNotice->setIsTop($aParams['is_top']);
        $this->oNotice->setContents($aParams['contents']);
        $this->oNotice->setState($aParams['state']);

        // insert action
        return $this->oNotice->registNotice();
    }

    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body'      => 'board/notice/regist',
            'import_js' => 'board/notice/regist.js',
            'editor'    => 'common/ckeditor5'
        ];
    }
}