<?php namespace App\Controllers\Manage\Main;

/* ===================================================================
	리스트 순서 저장
=================================================================== */

use App\Controllers\BaseController;
use App\Models\Manage\MainSectionModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class SortModify extends BaseController
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
     * 메뉴 리스트 페이지
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
            // 지정 된 순서 update
            $aResult = $this->modify($aParams['sort'], $this->oMainSection->getList([], "*"));

            if ( $aResult === false ) {
                throw new Exception('잘못 된 값이 있습니다.', '8897');
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

    /**
     * @param array $aParams
     * @param array $aOrigin
     * @return bool
     * @throws Exception
     */
    private function modify(array $aParams, array $aOrigin): bool
    {
        foreach ( $aParams as $sortIdx => $sort ) {
            // update 필요 없음
            if ( $sort === (int)$aOrigin[$sortIdx]['sort'] ) {
                continue;
            }
            // data set
            $this->oMainSection->setIdx($sort['idx']);
            $this->oMainSection->setSort($sort['sort']);
            //sort action
            $bResult = $this->oMainSection->modifySort();
            // 결과값 체크
            if ( $bResult === false ) {
                throw new Exception('잘못된 값이 있습니다.', '8897');
            }
        }

        return true;
    }
}