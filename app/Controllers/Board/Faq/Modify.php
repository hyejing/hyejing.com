<?php namespace App\Controllers\Board\Faq;

/* ===================================================================
    FAQ 수정 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\Board\FaqModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Modify extends BaseController
{
    protected FaqModel $oFaq;

    protected array $param;
    protected array $where;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 2, '접근권한이 없습니다.');

        // 게시판 FAQ 모델
        $this->oFaq = new FaqModel();
    }

    /**
     * FAQ 수정 페이지
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            // parameters
            $params      = [
                'idx' => ['default' => '', 'rules' => 'required|is_natural_no_zero', 'error' => '잘못된 접근입니다.']
            ];
            $this->param = $this->chkParam($params, 'get');

            // FAQ 정보 가져오기 by idx
            $this->oFaq->setIdx($this->param['idx']);

            // set view datas
            $this->data_set['HTML']['category_list'] = $this->oFaq->getCategoryList();
            $this->data_set['HTML']['state']         = $this->oFaq->getStatusList();
            $this->data_set['HTML']['JS']            = $this->linkJs(URL_DOMAIN . "/assets/editor/ckeditor4/ckeditor.js");//에디터 호출 @_ckeditor4_add
            $this->data_set['DATA']                  = $this->oFaq->getInfo();
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
     * FAQ 수정 proc
     */
    public function proc()
    {
        try {
            $params = [
                'idx'         => [
                    'default' => '',
                    'rules'   => 'required|is_natural_no_zero',
                    'error'   => '잘못된 접근입니다',
                ],
                'title'       => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => '제목을 확인해주세요',
                ],
                'oricategory' => [
                    'default' => '0',
                    'rules'   => 'required|is_natural',
                    'error'   => '잘못된 접근입니다',
                ],
                'category'    => [
                    'default' => '0',
                    'rules'   => 'required|is_natural',
                    'error'   => '카테고리를 확인해주세요',
                ],
                'state'       => [
                    'default' => '',
                    'rules'   => 'required|is_natural',
                    'error'   => '노출여부를 선택 해 주세요.',
                ],
                'contents'    => [
                    'default' => '',
                    'rules'   => 'required|min_length[1]',
                    'error'   => '내용을 입력해주세요.',
                ],
            ];
            // 파라미터 체크
            $aParams = $this->chkParam($params, 'post');

            $aResult = $this->modifyFaq($aParams);

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
     * FAQ 수정
     * @return int
     */
    private function modifyFaq($aParams = []): ?int
    {
        // update data set
        $this->oFaq->setIdx($aParams['idx']);
        $this->oFaq->setAdminIdx($this->user->getIdx());
        $this->oFaq->setTitle($aParams['title']);
        $this->oFaq->setCategory($aParams['category']);
        $this->oFaq->setContents($aParams['contents']);
        $this->oFaq->setState($aParams['state']);

        // 카테고리가 변경되면 sort값 업데이트
        if ( $aParams['oricategory'] !== $aParams['category'] ) {
            // 카테고리 sort max값 가져오기
            $iGetSort = $this->oFaq->getCategorySortMax();
            $iSort    = $iGetSort + 1;

            $this->oFaq->setSort($iSort);
        }

        // update action
        //return $this->oFaq->modifyFaq();
        return $this->oFaq->modifyFaqModule();// 변경처리
    }

    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body'      => 'board/faq/modify',
            'import_js' => 'board/faq/modify.js',
            'editor_js' => 'board/faq/ckeditor4_set.js',// 에디터 호출 @_ckeditor4_add
            'editor'    => 'common/ckeditor4',// 에디터 호출 @_ckeditor4_add
        ];
    }
}