<?php namespace App\Controllers\Board\Faq;

/* ===================================================================
    FAQ 등록 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\Board\FaqModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Regist extends BaseController
{
    protected FaqModel $oFaq;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 2, '접근권한이 없습니다.');

        // 게시판 FAQ 모델
        $this->oFaq = new FaqModel();
    }

    /**
     * FAQ 등록 페이지
     */
    public function index()
    {
        try {
            $this->setDefaultView();
            // set view datas
            $this->data_set['HTML']['category_list'] = $this->oFaq->getCategoryList();
            $this->data_set['HTML']['state']         = $this->oFaq->getStatusList();
            $this->data_set['HTML']['JS']            = $this->linkJs(URL_DOMAIN . "/assets/editor/ckeditor4/ckeditor.js");//에디터 호출 @_ckeditor4_add
            //$this->data_set['HTML']['JS']          = $this->linkJs(URL_DOMAIN . "/assets/editor/ckeditor5/ckeditor.js");//에디터 호출 @_ckeditor5_add
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
     * FAQ 등록 proc
     */
    public function proc()
    {
        try {
            $params = [
                'title'    => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => '제목을 확인해주세요',
                ],
                'category' => [
                    'default' => '',
                    'rules'   => 'required|is_natural',
                    'error'   => '카테고리를 확인해주세요',
                ],
                'state'    => [
                    'default' => '',
                    'rules'   => 'required|is_natural',
                    'error'   => '노출여부를 선택 해 주세요.',
                ],
                'contents' => [
                    'default' => '',
                    'rules'   => 'required|min_length[1]',
                    'error'   => '내용을 입력해주세요.',
                ],
            ];
            // 파라미터 체크
            $aParams = $this->chkParam($params, 'post');

            // FAQ 등록
            $aResult = $this->registFaq($aParams);

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
     * FAQ 등록
     * @return int
     */
    private function registFaq($aParams = []): ?int
    {
        //insert data set
        $this->oFaq->setAdminIdx($this->user->getIdx());
        $this->oFaq->setTitle($aParams['title']);
        $this->oFaq->setCategory($aParams['category']);
        $this->oFaq->setContents($aParams['contents']);
        $this->oFaq->setState($aParams['state']);

        // 카테고리 sort max값 가져오기
        $iGetSort = $this->oFaq->getCategorySortMax();
        $iSort    = $iGetSort + 1;

        $this->oFaq->setSort($iSort);

        // insert action
        //return $this->oFaq->registFaq();
        return $this->oFaq->registFaqModule();// 변경처리
    }

    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body'      => 'board/faq/regist',
            'import_js' => 'board/faq/regist.js',
            'editor_js' => 'board/faq/ckeditor4_set.js',// 에디터 호출 @_ckeditor4_add
            'editor'    => 'common/ckeditor4',// 에디터 호출 @_ckeditor4_add
        ];
    }
}