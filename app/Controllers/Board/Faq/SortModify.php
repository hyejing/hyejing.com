<?php namespace App\Controllers\Board\Faq;

/* ===================================================================
	FAQ sort 정렬 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\Board\FaqModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class SortModify extends BaseController
{
    protected FaqModel $oFaq;

    protected array $param;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 2, '접근권한이 없습니다.');

        // 게시판 FAQ 모델
        $this->oFaq = new FaqModel();
    }

    /**
     * FAQ 정렬 수정
     */
    public function index()
    {
        try {
            $params = [
                'sort' => [
                    'default' => [],
                    'rules'   => '',
                    'error'   => 'sort array 가 빈 값 입니다.',
                ],
            ];
            // 파라미터 체크
            $aParams = $this->chkParam($params, 'post');

            foreach ( $aParams["sort"] as $sort ) {
                // 기존 정렬값과 변경된 정렬값이 같지 않을때만 업데이트
                if ( $sort['sort'] !== $sort['ori_sort'] ) {
                    // data set
                    $this->oFaq->setIdx($sort['idx']);
                    $this->oFaq->setSort($sort['sort']);

                    //sort action
                    $aResult = $this->oFaq->modifySort();

                    if ( $aResult === false ) {
                        throw new Exception('잘못 된 값이 있습니다.', '8897');
                    }
                }
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
                'data'    => $e
            ];
        }

        $this->displayJson($aOutPut);
    }
}