<?php namespace App\Models\Manage;

/* ===================================================================
	메인 섹션 컨텐츠 관리 모델링
=================================================================== */

use App\Models\MyModel;
use Exception;

class SectionMainContentsModel extends MyModel
{
    protected array $aState;
    protected int $iIdx;
    protected int $iSectionIdx;
    protected int $iBbsIdx;
    protected int $iSort;
    protected string $sState;

    public function __construct()
    {
        parent::__construct();

        $this->init();
    }

    protected function init()
    {
        $this->aState = [
            '1' => '노출',
            '0' => '숨김'
        ];
    }

    /* ===================================================================
        Set Functions
    =================================================================== */
    public function setIdx($iIdx)
    {
        if ( $iIdx > 0 ) {
            $this->iIdx = $iIdx;
        }
        else {
            throw new Exception('정상적인 Idx를 입력해주세요.');
        }
    }

    public function setSectionIdx($iSectionIdx)
    {
        if ( $iSectionIdx > 0 ) {
            $this->iSectionIdx = $iSectionIdx;
        }
        else {
            throw new Exception('정상적인 SectionIdx를 입력해주세요.');
        }
    }

    public function setBbsIdx($iBbsIdx)
    {
        if ( $iBbsIdx > 0 ) {
            $this->iBbsIdx = $iBbsIdx;
        }
        else {
            throw new Exception('정상적인 BbsIdx를 입력해주세요.');
        }
    }

    public function setState(int $sState)
    {
        if ( in_array($sState, array_keys($this->aState), true) === true ) {
            $this->sState = $sState;
        }
        else {
            throw new Exception('정상적인 상태값을 입력해주세요.');
        }
    }

    public function setSort($iSort)
    {
        if ( $iSort > 0 ) {
            $this->iSort = $iSort;
        }
        else {
            throw new Exception('정상적인 Sort Idx를 입력해주세요.');
        }
    }

    /* ===================================================================
        Get Functions
    =================================================================== */
    public function getIdx(): int
    {
        return $this->iIdx;
    }

    public function getSectionIdx(): int
    {
        return $this->iSectionIdx;
    }

    public function getBbsIdx(): int
    {
        return $this->iBbsIdx;
    }

    public function getState(): string
    {
        return $this->sState;
    }

    public function getStates(): array
    {
        return $this->aState;
    }

    public function getSort(): int
    {
        return $this->iSort;
    }
    /* ===================================================================
        Select Functions
    =================================================================== */
    /* --------------------------   Select   -------------------------- */
    /**
     * 해당 섹션 컨텐츠 리스트
     */
    public function getSectionMainContents($where = []): array
    {
        $field  = 'tsemaco.sort, tsemaco.idx, tsemaco.bbs_idx, tsemaco.state, tbb.title, tsemaco.reg_date';
        $sTable = 'tbl_section_main_contents AS tsemaco INNER JOIN tbl_bbs AS tbb ON tsemaco.bbs_idx = tbb.idx';

        $this->dbs->setBind(['iSectionIdx' => $this->iSectionIdx]);
        $lists = $this->dbs->getList($sTable, $field, $where, null, "`sort` ASC", 0, 0);

        if ( count($lists) > 0 ) {
            foreach ( $lists as $k => $data ) {
                $lists[$k] = $this->remakeInfo($data);
            }
        }

        return $lists;
    }

    /**
     * 컨텐츠 관리
     */
    public function getModifyInfo(): array
    {
        $field  = 'tsemaco.idx, tbb.title, tsemaco.state';
        $sTable = 'tbl_section_main_contents AS tsemaco INNER JOIN tbl_bbs AS tbb ON tsemaco.bbs_idx = tbb.idx';

        $this->dbs->setBind(['iIdx' => $this->iIdx]);
        $result = $this->dbs->get($sTable, $field, "tsemaco.idx=:iIdx:");

        return $this->remakeInfo($result);
    }

    /**
     * 데이터 정제
     *
     * @param $data
     * @return mixed
     */
    protected function remakeInfo($data)
    {
        if ( isset($data['idx']) === true ) {
            $data['state_txt'] = $this->aState[$data['state']];
        }

        return $data;
    }

    /* ===================================================================
    Modules
    =================================================================== */
    // 해당 섹션에 동일안 컨테츠가 있는지 체크
    public function chkBbsIdx(): int
    {
        $sWhere = "`bbs_idx`= :iBbsIdx: AND `section_idx` = :iSectionIdx:";

        // set bind
        $aBindData = [
            'iBbsIdx'     => $this->iBbsIdx,
            'iSectionIdx' => $this->iSectionIdx
        ];

        $this->dbs->setBind($aBindData);

        return $this->dbs->getCount("tbl_section_main_contents", $sWhere);
    }

    /* --------------------------   Insert   -------------------------- */
    // content insert
    public function registSectionContent(): bool
    {
        $sWhere = "`section_idx` = :iSectionIdx:";
        // set bind
        $aBindData = [
            'iSectionIdx' => $this->iSectionIdx
        ];
        $this->dbs->setBind($aBindData);
        $iMaxSort = $this->dbs->getMax("tbl_section_main_contents", 'sort', $sWhere);
        $iMaxSort = ($iMaxSort == 0) ? 1 : ($iMaxSort + 1);

        $ins                = [];
        $ins['bbs_idx']     = $this->iBbsIdx;
        $ins['sort']        = $iMaxSort;
        $ins['section_idx'] = $this->iSectionIdx;
        $ins['state']       = $this->sState;
        $ins['reg_date']    = TIME_NOW;

        $iResult = $this->dbm->insert("tbl_section_main_contents", $ins);

        if ( $iResult > 0 ) {
            return true;
        }

        return false;
    }

    /* --------------------------   Update   -------------------------- */
    // sort update
    public function modifySort(): bool
    {
        $upd         = [];
        $upd['sort'] = $this->iSort;

        // set bind
        $aBindData = [
            'iIdx' => $this->iIdx,
        ];

        $this->dbm->setBind($aBindData);
        $rtv = $this->dbm->update('tbl_section_main_contents', $upd, "`idx`=:iIdx: ");

        return $rtv !== false;
    }

    // content update
    public function infoModify(): bool
    {
        $upd          = [];
        $upd['state'] = $this->sState;
        $where        = "`idx`=:iIdx: ";

        // set bind
        $aBindData = [
            'iIdx' => $this->iIdx
        ];

        $this->dbm->setBind($aBindData);

        $rtv = $this->dbm->update('tbl_section_main_contents', $upd, $where);

        return $rtv !== false;
    }
}
