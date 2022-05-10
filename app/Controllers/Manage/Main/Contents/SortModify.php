<?php namespace App\Controllers\Manage\Main\Contents;

/* ===================================================================
	컨텐츠 리스트 순서 저장
=================================================================== */

use App\Controllers\BaseController;
use App\Models\Manage\SectionMainContentsModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class SortModify extends BaseController
{
    protected SectionMainContentsModel $oSectionMainContents;
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
                'section_idx' => [
                    'default' => '',
                    'rules'   => 'required|min_length[1]',
                    'error'   => '섹션 idx가 존재 하지 않습니다.',
                ]
            ];

            // 파라미터 체크
            $aParams = $this->chkParam($params, 'post');

            // Section idx set 처리
            $this->oSectionMainContents->setSectionIdx($aParams['section_idx']);

            // 해당 섹션 컨텐츠 리스트 순서 저장
            $aResult = $this->modify($aParams['sort'], $this->oSectionMainContents->getSectionMainContents(['tsemaco.section_idx=:iSectionIdx:']));

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
            // 정렬값이 같을 경우 update 필요 없음
            if ( $sort === (int)$aOrigin[$sortIdx]['sort'] ) {
                continue;
            }

            // data set
            $this->oSectionMainContents->setIdx($sort['idx']);
            $this->oSectionMainContents->setSort($sort['sort']);

            // 정렬값 수정 처리
            $bResult = $this->oSectionMainContents->modifySort();

            // 결과값 체크
            if ( $bResult === false ) {
                throw new Exception('잘못된 값이 있습니다.', '8897');
            }
        }

        return true;
    }
}