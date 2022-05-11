<?php namespace App\Models;

/* ===================================================================
	관리자 메뉴(tbl_admin_menu) 모델링
=================================================================== */

use Exception;

class MenuModel extends MyModel
{
    protected array $aStatus;

    protected int $idx;
    protected int $parent;
    protected int $level;
    protected int $state;
    protected string $name;
    protected string $link;

    public function __construct()
    {
        parent::__construct();

        $this->init();
    }

    /* ===================================================================
        Set Function
    =================================================================== */
    protected function init()
    {
        $this->aStatus = [
            '0' => '미노출',
            '1' => '노출',
        ];
    }

    public function setIdx($idx)
    {
        if ( $idx > 0 ) {
            $this->idx = $idx;
        }
    }

    public function setParent($parent)
    {
        if ( empty($parent) === false ) {
            $this->parent = $parent;
        }
        else {
            $this->parent = 0;
        }
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    public function setState($state)
    {
        if ( isset($this->aStatus[$state]) ) {
            $this->state = $state;
        }
        else {
            throw new Exception("올바르지 않은 상태값이 입력되었습니다.");
        }
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setLink($link)
    {
        $this->link = $link;
    }

    /* ===================================================================
        Get Function
    =================================================================== */

    /* ===================================================================
        Modules
    =================================================================== */
    /**
     * 메뉴 순서 저장 함수
     *
     * @param $category_idx
     * @param $sort
     * @return bool
     * @throws Exception
     */
    public function saveSortMenu($category_idx, $sort): bool
    {
        // 카테고리의 서브메뉴를 가져옴
        $where = "tadme.`parent` = :parent:";// 로그인한 계정의 권한
        $this->addBindData(['parent' => $category_idx]);
        $aSubMenu = $this->getLists($where);

        // 트랜잭션 시작
        $sTransKey = "save_sort_menu";
        $this->dbm->transBegin($sTransKey);

        // 로우 하나씩 검증하면서 정렬 순서가 바뀌면 업데이트 처리
        if ( count($aSubMenu) > 0 ) {
            foreach ( $aSubMenu as $subMenu ) {
                if ( isset($sort[$subMenu['idx']]) && $subMenu['sort'] != $sort[$subMenu['idx']] ) {
                    $this->modifySort($subMenu['idx'], $sort[$subMenu['idx']]);
                }
            }
        }

        // 하나라도 변경되지 않으면 둘다 롤백
        if ( $this->dbm->transStatus($sTransKey) ) {
            // 트랜잭션 커밋
            $this->dbm->transCommit($sTransKey);

            return true;
        }
        else {
            // 트랜잭션 롤백
            $this->dbm->transRollback($sTransKey);

            return false;
        }
    }

    /**
     * 메뉴 순서 변경 함수
     *
     * @param int $swap
     * @return bool
     * @throws Exception
     */
    public function swapSort(int $swap): bool
    {
        // 메뉴정보 조회
        $aMenuOri = $this->getInfo();
        if ( empty($aMenuOri) ) {
            throw new Exception("올바르지 않은 메뉴번호 입니다.");
        }

        // 스왑 메뉴정보 조회
        $aMenuSwap = $this->getInfoByIdx($swap);
        if ( empty($aMenuSwap) ) {
            throw new Exception("올바르지 않은 메뉴번호(Swap) 입니다.");
        }

        $iOriSort  = $aMenuOri['sort'];     // 현 메뉴의 정렬순서
        $iSwapSort = $aMenuSwap['sort'];    // 스왑할 메뉴의 정렬순서

        // 순서값들의 차이가 2 이상인 경우 1로 줄여주기
        // 오름차순 정렬이므로, 작은 값을 기준으로 큰값을 줄여줌
        if ( $iOriSort < $iSwapSort && ($iSwapSort - $iOriSort) > 1 ) {
            $iSwapSort = $iOriSort + 1;
        }
        else {
            if ( $iOriSort > $iSwapSort && ($iOriSort - $iSwapSort) > 1 ) {
                $iOriSort = $iSwapSort + 1;
            }
        }

        // 트랜잭션 시작
        $sTransKey = "swap_sort";
        $this->dbm->transBegin($sTransKey);

        // 서로 순서변경
        $this->modifySort($this->idx, $iSwapSort);
        $this->modifySort($swap, $iOriSort);

        // 하나라도 변경되지 않으면 둘다 롤백
        if ( $this->dbm->transStatus($sTransKey) ) {
            $this->dbm->transCommit($sTransKey);

            return true;
        }
        else {
            // 트랜잭션 롤백
            $this->dbm->transRollback($sTransKey);

            return false;
        }
    }

    /* ===================================================================
        Sub Functions
    =================================================================== */
    /* --------------------------   Select   -------------------------- */
    /**
     * 소메뉴를 포함한 대메뉴 리스트 가져오기
     *
     * @param $where
     * @return array
     */
    public function getListMenu($where): array
    {
        $aMenu = $this->getLists($where);

        // 다중 배열로 가공
        $aResult = [];
        foreach ( $aMenu as $val ) {
            if ( $val['parent'] === '0' ) {
                // 대분류(카테고리)
                $aResult[$val['idx']] = $val;
            }
            else {
                if ( isset($aResult[$val['parent']]) ) {
                    // 일반 메뉴
                    $aResult[$val['parent']]['sub'][$val['idx']] = $val;
                }
            }
        }


        $bms_id = 'hjna';    // 아이디 입력안할시 기본설정그룹으로 옴(딱걸림)
        $msg    = print_r($aResult,true);
        $params = 'id=' . $bms_id . '&msg=' . urlencode($msg);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://bms.any4.me/push');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $return = curl_exec($ch);

        curl_close($ch);


        return $aResult;
    }

    /**
     * 메뉴 리스트 조회
     *
     * @param $where
     * @return array
     */
    public function getLists($where): array
    {
        $orderby = "tadme.parent ASC, tadme.sort ASC, tadme.idx ASC";
        $this->dbs->setBind($this->aBind);

        return $this->dbs->getList("tbl_admin_menu AS tadme", "tadme.*", $where, null, $orderby, 0, 100);
    }

    /**
     * 현 메뉴 정보 가져오기
     *
     * @return array
     */
    public function getInfo(): array
    {
        $this->dbs->setBind(['idx' => $this->idx]);

        return $this->dbs->get("tbl_admin_menu AS tadme", "tadme.*", "tadme.`idx` = :idx:");
    }

    /**
     * idx 인자를 넘겨받아 해당 메뉴정보를 불러오는 함수
     *
     * @param $idx
     * @return array
     */
    public function getInfoByIdx($idx): array
    {
        $this->dbs->setBind(['idx' => $idx]);

        return $this->dbs->get("tbl_admin_menu AS tadme", "tadme.*", "tadme.`idx` = :idx:");
    }

    /**
     * 메뉴(카테고리)별 정렬 마지막값
     *
     * @param $parent
     * @return int
     */
    public function getLastSort($parent): int
    {
        $this->dbs->setBind(['parent' => $parent]);

        $iSort = $this->dbs->getMax("tbl_admin_menu AS tadme", "tadme.sort", "tadme.`parent` = :parent:");

        return $iSort ?? 0;
    }

    /**
     * 현 메뉴의 다음 정렬순서 가져오기
     *
     * @return int
     */
    public function getNextSort(): int
    {
        // 현재 메뉴 정보 가져오기
        $aInfo = $this->getInfo();

        $where = "tadme.`parent` = :parent: AND tadme.`sort` > :sort: ORDER BY tadme.`sort` ASC";

        $aBindDate = [
            'parent' => $aInfo['parent'],
            'sort'   => $aInfo['sort'],
        ];
        $this->dbs->setBind($aBindDate);

        $aRtv = $this->dbs->get("tbl_admin_menu AS tadme", "tadme.idx", $where);

        return $aRtv['idx'] ?? 0;
    }

    /**
     * 현 메뉴의 이전 순서 메뉴 가져오기
     *
     * @return int
     */
    public function getPrevSort(): int
    {
        // 현재 메뉴 정보
        $aInfo = $this->getInfo();

        $where = "tadme.`parent` = :parent: AND tadme.`sort` < :sort: ORDER BY tadme.`sort` DESC";

        $aBindDate = [
            'parent' => $aInfo['parent'],
            'sort'   => $aInfo['sort'],
        ];
        $this->dbs->setBind($aBindDate);

        $aRtv = $this->dbs->get("tbl_admin_menu AS tadme", "tadme.idx", $where);

        return $aRtv['idx'] ?? 0;
    }

    /* --------------------------   Insert   -------------------------- */
    /**
     * 카테고리 추가
     *
     * @return int
     */
    public function registCategory(): int
    {
        // 해당 카테고리의 마지막 정렬번호 가져옴
        $iLast = $this->getLastSort(0);

        $ins          = array();
        $ins['name']  = $this->name;
        $ins['level'] = $this->level;
        $ins['state'] = $this->state;
        $ins['sort']  = $iLast + 1;

        return $this->dbm->insert('tbl_admin_menu', $ins);
    }

    /**
     * 메뉴 추가
     *
     * @return int
     */
    public function regist(): int
    {
        // 해당메뉴의 마지막 정렬번호 가져옴
        $iLast = $this->getLastSort($this->parent);

        $ins           = array();
        $ins['parent'] = $this->parent;
        $ins['name']   = $this->name;
        $ins['level']  = $this->level;
        $ins['state']  = $this->state;
        $ins['link']   = $this->link;
        $ins['sort']   = $iLast + 1;

        return $this->dbm->insert('tbl_admin_menu', $ins);
    }

    /* --------------------------   Update   -------------------------- */
    /**
     * 메뉴 정보 수정
     *
     * @return int
     */
    public function modifyCategory(): ?int
    {
        $upd          = array();
        $upd['name']  = $this->name;
        $upd['level'] = $this->level;
        $upd['state'] = $this->state;

        $this->dbm->setBind(['idx' => $this->idx]);

        return $this->dbm->update('tbl_admin_menu', $upd, "`idx` = :idx:");
    }

    /**
     * 메뉴 정보 수정
     *
     * @return int
     */
    public function modify(): ?int
    {
        $upd          = array();
        $upd['name']  = $this->name;
        $upd['level'] = $this->level;
        $upd['state'] = $this->state;
        $upd['link']  = $this->link;

        $this->dbm->setBind(['idx' => $this->idx]);

        return $this->dbm->update('tbl_admin_menu', $upd, "`idx` = :idx:");
    }

    /**
     * 메뉴 순서 변경
     *
     * @param int $idx
     * @param int $sort
     * @return int
     */
    public function modifySort(int $idx, int $sort): ?int
    {
        $upd         = [];
        $upd['sort'] = $sort;

        $this->dbm->setBind(['idx' => $idx]);

        return $this->dbm->update('tbl_admin_menu', $upd, "`idx` = :idx:");
    }
}