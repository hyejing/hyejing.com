<?php namespace App\Models\Board;

use Exception;

class NoticeModel extends BoardModel
{
    protected array $aSearchType;
    protected array $aIsTopType;

    protected int $idx;
    protected int $admin_idx;
    protected int $notice_idx;
    protected int $is_top;
    protected string $title;
    protected string $contents;
    protected string $reg_date;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTableName('tbl_board_notice');
        $this->init();
    }

    /* ===================================================================
        Set Functions
    =================================================================== */
    protected function init()
    {
        // 검색조건
        $this->aSearchType = [
            'title'    => '제목',
            'contents' => '내용',
        ];

        // 상단 고정
        $this->aIsTopType = [
            '0' => '일반',
            '1' => '고정',
        ];
    }

    public function setIdx(int $iIdx)
    {
        if ( $iIdx > 0 ) {
            $this->idx = $iIdx;
        }
        else {
            $this->idx = 0;
        }
    }

    // 작성자 IDX
    public function setAdminIdx($admin_idx)
    {
        if ( $admin_idx > 0 ) {
            $this->admin_idx = $admin_idx;
        }
        else {
            $this->admin_idx = 0;
        }
    }

    // 상단노출
    public function setIsTop($is_top)
    {
        if ( $is_top > 0 ) {
            $this->is_top = $is_top;
        }
        else {
            $this->is_top = 0;
        }
    }

    /**
     * 공지 IDX
     *
     * @throws Exception
     */
    public function setNoticeIdx($notice_idx)
    {
        if ( $notice_idx > 0 ) {
            $this->notice_idx = $notice_idx;
        }
        else {
            throw new Exception('공지 IDX를 확인해주세요');
        }
    }

    /**
     * 제목
     *
     * @throws Exception
     */
    public function setTitle($title)
    {
        if ( empty($title) === false ) {
            $this->title = $title;
        }
        else {
            throw new Exception('제목을 입력해 주세요.');
        }
    }

    /**
     * 내용
     *
     * @throws Exception
     */
    public function setContents($contents)
    {
        if ( empty($contents) === false ) {
            $this->contents = $contents;
        }
        else {
            throw new Exception('내용을 입력해 주세요.');
        }
    }

    /* ===================================================================
        Get Functions
    =================================================================== */
    public function getIdx(): int
    {
        return $this->idx ?? 0;
    }

    public function getAdminIdx(): int
    {
        return $this->admin_idx;
    }

    public function getIsTop(): int
    {
        return $this->is_top;
    }

    public function getNoticeIdx(): int
    {
        return $this->notice_idx;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function getSearchType(): array
    {
        return $this->aSearchType;
    }

    public function getIsTopType(): array
    {
        return $this->aIsTopType;
    }


    /* ===================================================================
        Modules
    =================================================================== */
    /**
     * 공지사항 등록
     *
     * @return bool
     * @throws Exception
     */
    public function registNotice(): bool
    {
        // TODO : 트랜젝션 적용
        // 공지사항 제목 insert
        $rtv = $this->regist();

        if ( $rtv === false || $rtv <= 0 ) {
            $this->setResultData(['message' => '제목 입력에 실패하였습니다.']);

            return false;
        }

        $this->setNoticeIdx($rtv);

        // 공지사항 내용 insert
        $rtv2 = $this->registContents();
        if ( $rtv2 === false || $rtv2 <= 0 ) {
            $this->setResultData(['message' => '내용 입력에 실패하였습니다.']);

            return false;
        }

        return true;
    }

    /**
     * 공지사항 수정
     *
     * @return bool
     */
    public function modifyNotice(): bool
    {
        // TODO : 트랜젝션 적용
        // 공지사항 제목 modify
        $rtv = $this->modify();
        if ( $rtv === false ) {
            $this->setResultData(['message' => '제목 수정에 실패하였습니다.']);

            return false;
        }

        // 공지사항 내용 modify
        $rtv2 = $this->modifyContents();
        if ( $rtv2 === false ) {
            $this->setResultData(['message' => '내용 수정에 실패하였습니다.']);

            return false;
        }

        return true;
    }
    /* ===================================================================
        Sub Functions
    =================================================================== */
    /* --------------------------   Select   -------------------------- */
    /**
     * 리스트 카운트
     *
     * @param $where
     * @return int
     */
    public function getTotalCount($where): int
    {
        $table = "`tbl_board_notice` AS `tbono` 
                    LEFT JOIN `tbl_board_notice_contents` AS `tbonoco` 
                    ON `tbono`.idx = `tbonoco`.`notice_idx` ";
        $this->dbs->setBind($this->aBind);

        return $this->dbs->getCount($table, $where);
    }

    /**
     * Notice 리스트
     *
     * @param     $where
     * @param int $offset
     * @param int $lpp
     * @return array
     */
    public function getList($where, int $offset = 0, int $lpp = 20): array
    {
        $field   = " `tbono`.*, `tbonoco`.`contents`";
        $table   = "`tbl_board_notice` AS `tbono` 
                    LEFT JOIN `tbl_board_notice_contents` AS `tbonoco` 
                    ON `tbono`.idx = `tbonoco`.`notice_idx` ";
        $orderby = "is_top DESC, `tbono`.idx DESC";

        $this->dbs->setBind($this->aBind);
        $lists = $this->dbs->getList($table, $field, $where, null, $orderby, $offset, $lpp);

        $result = [];
        if ( empty($lists) === false && count($lists) > 0 ) {
            foreach ( $lists as $k => $v ) {
                $result[$k] = $this->_remakeInfo($v);
            }
        }

        return $result;
    }

    /**
     *  내용 조회 by idx
     *
     * @return array
     */
    public function getInfo(): array
    {
        $table = "`tbl_board_notice` AS `tbono` LEFT JOIN `tbl_board_notice_contents` AS `tbonoco` ON `tbono`.idx = `tbonoco`.`notice_idx` ";

        $this->dbs->setBind(['idx' => $this->idx]);
        $result = $this->dbs->get($table, "`tbono`.*, `tbonoco`.`contents`", "`tbono`.`idx`=:idx:");

        return $this->_remakeInfo($result);
    }

    /**
     * Notice 추가 정보
     */
    protected function _remakeInfo($result)
    {
        if ( isset($result['idx']) === true ) {
            $result['state_txt']  = '';
            $result['is_top_txt'] = '';

            if ( isset($result['state']) && isset($this->aStatus[$result['state']]) ) {
                $result['state_txt'] = $this->aStatus[$result['state']];
            }
            if ( isset($result['is_top']) && isset($this->aIsTopType[$result['is_top']]) ) {
                $result['is_top_txt'] = $this->aIsTopType[$result['is_top']];
            }
        }

        return $result;
    }

    /* --------------------------   Insert   -------------------------- */
    /**
     * Notice insert
     *
     * @return int
     */
    public function regist(): int
    {
        $ins              = [];
        $ins['admin_idx'] = $this->admin_idx;
        $ins['is_top']    = $this->is_top;
        $ins['title']     = $this->title;
        $ins['state']     = $this->state;
        $ins['reg_date']  = TIME_NOW;

        return $this->dbm->insert('tbl_board_notice', $ins);
    }

    /**
     * Notice Contents insert
     *
     * @return bool
     */
    public function registContents(): bool
    {
        $ins               = [];
        $ins['notice_idx'] = $this->notice_idx;
        $ins['contents']   = $this->contents;

        return $this->dbm->insert('tbl_board_notice_contents', $ins);
    }

    /* --------------------------   Update   -------------------------- */
    /**
     * Notice 정보 수정
     *
     * @return int
     */
    public function modify(): ?int
    {
        $upd              = array();
        $upd['admin_idx'] = $this->admin_idx;
        $upd['is_top']    = $this->is_top;
        $upd['title']     = $this->title;
        $upd['state']     = $this->state;

        $aBindData = [
            'idx' => $this->idx,
        ];
        $this->dbm->setBind($aBindData);

        return $this->dbm->update('tbl_board_notice', $upd, "`idx` = :idx:");
    }

    /**
     * Notice Contents 정보 수정
     *
     * @return int
     */
    public function modifyContents(): ?int
    {
        $upd             = array();
        $upd['contents'] = $this->contents;

        $aBindData = [
            'notice_idx' => $this->notice_idx,
        ];
        $this->dbm->setBind($aBindData);

        return $this->dbm->update('tbl_board_notice_contents', $upd, "`notice_idx` = :notice_idx:");
    }
}