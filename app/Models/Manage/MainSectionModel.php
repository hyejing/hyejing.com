<?php namespace App\Models\Manage;

/* ===================================================================
	메인 섹션 모델링
=================================================================== */

use App\Models\MyModel;
use Exception;

class MainSectionModel extends MyModel
{
    protected array $aTypes;
    protected array $aState;
    protected int $iSort;
    protected int $iIdx;
    protected string $sName;
    protected string $sState;
    protected string $sType;

    public function __construct()
    {
        parent::__construct();

        $this->init();
    }

    protected function init()
    {
        $this->aTypes = [
            '1' => '컨텐츠',
            '2' => '메인',
            '3' => '배너',
        ];

        $this->aState = [
            '1' => '노출',
            '0' => '숨김'
        ];
    }

    /* ===================================================================
        Set Functions
    =================================================================== */
    public function setName($sName)
    {
        $this->sName = $sName;
    }

    public function setState(int $state)
    {
        if ( in_array($state, array_keys($this->aState), true) === true ) {
            $this->sState = $state;
        }
        else {
            throw new Exception('정상적인 상태값을 입력해주세요.');
        }
    }

    public function setType($sType)
    {
        $this->sType = $sType;
    }

    public function setIdx($iIdx)
    {
        if ( $iIdx > 0 ) {
            $this->iIdx = $iIdx;
        }
        else {
            throw new Exception('정상적인 Idx를 입력해주세요.');
        }
    }

    public function setSort($iSort)
    {
        if ( $iSort > 0 ) {
            $this->iSort = $iSort;
        }
    }

    /* ===================================================================
        Get Functions
    =================================================================== */
    public function getName(): string
    {
        return $this->sName;
    }

    public function getState(): string
    {
        return $this->sState;
    }

    public function getType(): string
    {
        return $this->sType;
    }

    public function getIdx(): int
    {
        return $this->iIdx;
    }

    public function getSort(): int
    {
        return $this->iSort;
    }

    public function getTypes(): array
    {
        return $this->aTypes;
    }

    public function getStates(): array
    {
        return $this->aState;
    }

    /* ===================================================================
        Modules
    =================================================================== */
    /**
     *  메인 섹션 이름 중복 체크
     */
    public function chkName(): bool
    {
        $sWhere   = [];
        $sWhere[] = "tsema.`name`= :sName: ";

        $aBindData['sName'] = $this->sName;

        // 수정시
        if ( isset($this->iIdx) === true && $this->iIdx > 0 ) {
            $sWhere[] = "tsema.`idx`!= :iIdx:";

            $aBindData['iIdx'] = $this->iIdx;
        }

        // set bind
        $this->dbs->setBind($aBindData);
        $cnt = $this->dbs->getCount("tbl_section_main AS tsema", $sWhere);

        return $cnt > 0;
    }

    /* ===================================================================
        Sub Functions
    =================================================================== */
    /* --------------------------   Select   -------------------------- */
    /**
     * 메인 섹션 카운트
     */
    public function getTotalCount($where): int
    {
        $this->dbs->setBind($this->aBind);

        return $this->dbs->getCount("tbl_section_main AS tsema", $where);
    }

    /**
     * 메인 섹션 리스트
     */
    public function getList($where = [], $field = "*"): array
    {
        $this->dbs->setBind($this->aBind);
        $lists = $this->dbs->getList('tbl_section_main AS tsema', $field, $where, null, "`sort` ASC", 0, 0);

        if ( count($lists) > 0 ) {
            foreach ( $lists as $k => $data ) {
                $lists[$k] = $this->remakeInfo($data);
            }
        }

        return $lists;
    }

    /**
     * 메인 섹션 리스트
     */
    public function getInfo(): array
    {
        $this->dbs->setBind(['iIdx' => $this->iIdx]);
        $result = $this->dbs->get('tbl_section_main AS tsema', 'tsema.*', "tsema.idx=:iIdx:");

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
            $data['type_txt']  = $this->aTypes[$data['type']];
            $data['state_txt'] = $this->aState[$data['state']];
        }

        return $data;
    }

    /* --------------------------   Insert   -------------------------- */
    public function registSection(): bool
    {
        $iMaxSort = $this->dbs->getMax("tbl_section_main", 'sort');
        $iMaxSort = ($iMaxSort == 0) ? 1 : ($iMaxSort + 1);

        $ins             = [];
        $ins['type']     = $this->sType;
        $ins['name']     = $this->sName;
        $ins['sort']     = $iMaxSort;
        $ins['state']    = $this->sState;
        $ins['reg_date'] = TIME_NOW;

        $iResult = $this->dbm->insert("tbl_section_main", $ins);

        return $iResult > 0;
    }

    /* --------------------------   Update   -------------------------- */
    public function modifySort(): bool
    {
        $upd         = [];
        $upd['sort'] = $this->iSort;

        // set bind
        $aBindData = [
            'iIdx' => $this->iIdx,
        ];

        $this->dbm->setBind($aBindData);
        $rtv = $this->dbm->update('tbl_section_main', $upd, "`idx`=:iIdx: ");

        return $rtv !== false;
    }

    public function modifyInfo(): bool
    {
        $upd          = [];
        $upd['type']  = $this->sType;
        $upd['name']  = $this->sName;
        $upd['state'] = $this->sState;

        // set bind
        $aBindData = [
            'iIdx' => $this->iIdx
        ];
        $this->dbm->setBind($aBindData);

        $rtv = $this->dbm->update('tbl_section_main', $upd, "`idx`=:iIdx: ");

        return $rtv !== false;
    }
}
