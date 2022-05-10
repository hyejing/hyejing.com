<?php namespace App\Models;

/* ===================================================================
    컨텐츠 모델링
=================================================================== */

use App\Libraries\Validation;
use App\Libraries\Protect;
use Exception;

class BbsModel extends MyModel
{
    protected Validation $valid;
    protected Protect $protect;

    protected array $aWhere;
    protected array $aStatusBg;
    protected array $aStatus;
    protected array $aSearchType;
    protected array $aCate;

    protected int $iIdx;
    protected int $iMidx;   // tbl_member.idx
    protected int $iFileIdx;// tbl_bbs_file.idx
    protected int $iCash;
    protected int $iSort;
    protected int $status;

    protected string $sTitle;
    protected string $sDetail;
    protected string $sFilePath;
    protected string $sFileName;
    protected string $sCate;
    protected string $sCateTitle;

    public function __construct()
    {
        parent::__construct();

        $this->valid   = new Validation();
        $this->protect = new Protect();

        $this->init();
    }

    protected function init()
    {
        $this->iIdx = 0;
        // 컨텐츠 상태
        $this->aStatus = [
            '1'   => '정상',
            '0'   => '업로드중',
            '100' => '삭제',
        ];

        // 컨텐츠 상태 색상
        $this->aStatusBg = [
            '1'   => 'success',
            '100' => 'danger',
        ];

        // 검색타입
        $this->aSearchType = [
            'id'   => '아이디',
            'nick' => '닉네임',
        ];

        // 카테고리
        $this->aCate = [
            'movie' => '영화',
            'drama' => '드라마',
            'ani'   => '애니메이션',
            'adult' => '성인'
        ];

        $this->aWhere = [];
    }

    /* ===================================================================
        Set Functions
    =================================================================== */
    /**
     * 자료번호
     *
     * @throws Exception
     */
    public function setIdx($iIdx)
    {
        if ( $iIdx > 0 ) {
            $this->iIdx = $iIdx;
        }
        else {
            throw new Exception('정확한 컨텐츠 idx 값을 입력해주세요.');
        }
    }

    /**
     * 회원번호
     * @throws Exception
     */
    public function setMidx($iMidx)
    {
        if ( $iMidx > 0 ) {
            $this->iMidx = $iMidx;
        }
        else {
            throw new Exception('정확한 컨텐츠 회원번호를 입력해주세요.');
        }
    }

    // 제목
    public function setTitle($sTitle)
    {
        if ( empty($sTitle) === false ) {
            $this->sTitle = $sTitle;
        }
        else {
            $this->sTitle = '';
        }
    }

    // 가격
    public function setCash($iCash)
    {
        if ( $iCash > 0 ) {
            $this->iCash = $iCash;
        }
        else {
            $this->iCash = 0;
        }
    }

    // 정렬

    /**
     * 정렬
     * @throws Exception
     */
    public function setSort($iSort)
    {
        if ( $iSort > 0 ) {
            $this->iSort = $iSort;
        }
        else {
            throw new Exception('정확한 sort 값을 입력해주세요.');
        }
    }

    // 상세내용
    public function setDetail($sDetail)
    {
        if ( $sDetail ) {
            $this->sDetail = $sDetail;
        }
    }


    /**
     * 파일경로
     * @throws Exception
     */
    public function setFile($sFile)
    {
        if ( $sFile ) {
            $this->sFilePath = $sFile;
        }
        else {
            throw new Exception('업로드 파일을 확인해주세요');
        }
    }

    /**
     * 카테고리
     * @throws Exception
     */
    public function setCate($sCate)
    {
        if ( $sCate ) {
            $this->sCate      = $sCate;
            $this->sCateTitle = $this->aCate[$sCate];
        }
        else {
            throw new Exception('카테고리를 확인해주세요');
        }
    }

    // 조건문 추가
    public function setWhere(string $str)
    {
        if ( empty($this->aWhere) === false ) {
            $this->aWhere[] = $str;
        }
        else {
            $this->aWhere = [$str];
        }
    }



    /* ===================================================================
        Get Functions
    =================================================================== */
    // 자료번호
    public function getIdx(): int
    {
        return $this->iIdx ?? 0;
    }

    // 자료 상태
    public function getStatusList(): array
    {
        return $this->aStatus;
    }

    // 검색항목
    public function getSearchType(): array
    {
        return $this->aSearchType;
    }

    // 파일경로에서 filename 가져오기
    public function getFileName(): string
    {
        $aTmp = explode('\\', $this->sFilePath);
        return end($aTmp);
    }

    // 카테고리
    public function getCate(): array
    {
        return $this->aCate;
    }

    /**
     * 컨텐츠정보 가공
     *
     * @param $result
     * @return mixed
     */
    protected function remakeInfo($result): array
    {
        $result['state_txt'] = '';

        if ( isset($result['idx']) === true ) {
            if ( isset($result['state']) && isset($this->aStatus[$result['state']]) ) {
                $result['state_txt'] = $this->aStatus[$result['state']];
                $result['state_bg']  = $this->aStatusBg[$result['state']] ?? 'warning';
            }
        }

        return $result;
    }

    /* ===================================================================
        Module
    =================================================================== */
    /**
     * 컨텐츠 추가
     *
     * @throws Exception
     */
    public function registContents(): int
    {
        $aRes = [];
        // ID값 없으면 입력진행 안함
        if ( empty($this->iMidx) === true ) {
            throw new Exception('업로드 회원정보가 없습니다.');
        }
        $transKey = "bbs_insert";           // 트랜잭션 키
        $this->dbm->transBegin($transKey);  // 트랜잭션 시작

        // 컨텐츠 입력
        $iBbsIdx = $this->registBbs();

        //컨텐츠 번호
        $this->setIdx($iBbsIdx);

        // 컨텐츠 정보 입력
        $aRes[] = $this->registBbsInfo();

        // 상세정보 입력
        $aRes[] = $this->registBbsDetail();

        // 파일정보 입력
        $file_idx = $this->registBbsFile();

        $aRes[] = $file_idx;

        $this->iFileIdx = $file_idx;

        // 업로드 파일경로 입력
        $aRes[] = $this->registBbsUpload();


        if ( in_array(false, $aRes) == false && $this->dbm->transStatus($transKey) ) {
            $this->dbm->transCommit($transKey); // 트랜잭션 커밋

            return $iBbsIdx;
        }
        else {
            $this->dbm->transRollback($transKey);   // 트랜잭션 롤백

            return false;
        }
    }

    /**
     * 상세 수정
     *
     * @return int
     */
    public function modifyDetail(): int
    {
        // ID값 없으면 입력진행 안함
        if ( empty($this->iIdx) === true ) {
            return false;
        }
        $transKey = "bbs_modify";           // 트랜잭션 키
        $this->dbm->transBegin($transKey);  // 트랜잭션 시작

        // bbs 수정
        $iBbsCnt = $this->modifyBbs();

        // bbs_detail 수정
        $iBbsDetailCnt = $this->modifyBbsDetail();

        if ( $iBbsCnt !== false && $iBbsDetailCnt !== false && $this->dbm->transStatus($transKey) ) {
            $this->dbm->transCommit($transKey); // 트랜잭션 커밋

            return $iBbsCnt;
        }
        else {
            $this->dbm->transRollback($transKey);   // 트랜잭션 롤백

            return false;
        }

    }

    /* ===================================================================
        Select Functions
    =================================================================== */
    /**
     * 전체 자료 카운트
     *
     * @return int
     */
    public function getTotalCount(): int
    {
        $table = "tbl_bbs AS tbb JOIN tbl_member AS tme ON tbb.member_idx = tme.idx";
        $this->dbs->setBind($this->aBind);

        return $this->dbs->getCount($table, $this->aWhere);
    }

    /**
     * 전체 자료 리스트
     *
     * @param string $field
     * @param int $offset
     * @param int $lpp
     * @return array
     */
    public function getList(string $field = "*", int $offset = 0, int $lpp = 20): array
    {
        $table = "tbl_bbs AS tbb left JOIN tbl_member AS tme ON tbb.member_idx = tme.idx";

        $this->dbs->setBind($this->aBind);
        $lists = $this->dbs->getList($table, $field, $this->aWhere, null, "tbb.`sort` DESC", $offset, $lpp);

        foreach ( $lists as $key => $section_data ) {
            $lists[$key] = $this->remakeInfo($section_data);
        }

        return $lists;
    }

    // bbs_idx 가 실제 존재 하는지 체크
    public function getCheckIdxInfoCount(): int
    {
        $sWhere = "`idx`= :iIdx:";

        // set bind
        $aBindData = [
            'iIdx' => $this->iIdx
        ];
        $this->dbs->setBind($aBindData);

        return $this->dbs->getCount("tbl_bbs", $sWhere);
    }

    /**
     * 자료상세
     *
     * @param $field
     * @return array
     */
    public function getBbs($field): array
    {
        $table = "tbl_bbs AS tbb LEFT JOIN tbl_member AS tme ON tbb.member_idx = tme.idx";

        $this->dbs->setBind($this->aBind);

        return $this->dbs->get($table, $field, $this->aWhere);
    }

    /**
     * 파일리스트
     *
     * @return array
     */
    public function getBbsFile(): array
    {
        $this->aWhere = ["tbbfi.bbs_idx = :bbs_idx:"];

        $this->dbs->setBind(['bbs_idx' => $this->iIdx]);

        return $this->dbs->getList("tbl_bbs_file AS tbbfi", "tbbfi.*", $this->aWhere);
    }

    /**
     * 컨텐츠 이미지
     *
     * @return array
     */
    public function getBbsImage(): array
    {
        $this->aWhere = ["tbbim.bbs_idx = :bbs_idx:"];

        $this->dbs->setBind(['bbs_idx' => $this->iIdx]);

        return $this->dbs->getList("tbl_bbs_image AS tbbim", "tbbim.*", $this->aWhere);
    }

    /**
     * 컨텐츠 세부 내용
     *
     * @return array
     */
    public function getBbsDetail(): array
    {
        $this->aWhere = ["tbbde.bbs_idx = :bbs_idx:"];

        $this->dbs->setBind(['bbs_idx' => $this->iIdx]);

        return $this->dbs->get("tbl_bbs_detail AS tbbde", "tbbde.detail", $this->aWhere);
    }

    /**
     * sort 최대값 가져오기
     *
     * @return int
     */
    public function getMaxSort(): int
    {
        return $this->dbm->getMax('tbl_bbs', 'sort');
    }

    /**
     * 컨텐츠 세부 내용
     *
     * @return array
     */
    public function getBbsStreaming(): array
    {
        $where   = [];
        $where[] = "tbbst.bbs_idx = :bbs_idx:";
        $this->dbs->setBind(['bbs_idx' => $this->iIdx]);

        return $this->dbs->get("tbl_bbs_streaming AS tbbst", "tbbst.file_name", $where);
    }

    /**
     * 업로드 파일 리스트
     *
     * @return array
     */
    public function getUploadFile(): array
    {
        $this->aWhere   = ["tbbup.bbs_idx = :bbs_idx:"];
        $this->aWhere[] = "tbbup.state = 0";

        $this->dbs->setBind(['bbs_idx' => $this->iIdx]);

        return $this->dbs->getList("tbl_bbs_upload AS tbbup", "tbbup.*", $this->aWhere);
    }

    /* ===================================================================
        Insert / Update / Delete Functions
    =================================================================== */

    /**
     * 컨텐츠 입력
     *
     * @return int
     */
    public function registBbs(): ?int
    {
        $aIns               = [];
        $aIns['title']      = $this->sTitle;
        $aIns['cash']       = $this->iCash;
        $aIns['member_idx'] = $this->iMidx;
        $aIns['sort']       = $this->iSort;
        $aIns['cate']       = $this->sCate;
        $aIns['cate_title'] = $this->sCateTitle;

        $iIdx = $this->dbm->insert("tbl_bbs", $aIns);

        if ( empty($iIdx) === true ) {
            return false;
        }
        else {
            return $iIdx;
        }
    }

    /**
     * 컨텐츠 정보 입력
     *
     * @return int
     */
    public function registBbsInfo(): ?int
    {
        $aIns            = [];
        $aIns['bbs_idx'] = $this->getIdx();

        $iIdx = $this->dbm->insert("tbl_bbs_info", $aIns);

        if ( empty($iIdx) === true ) {
            return false;
        }
        else {
            return $iIdx;
        }
    }

    /**
     * 상세 정보 입력
     *
     * @return int
     */
    public function registBbsDetail(): int
    {
        $aIns            = [];
        $aIns['bbs_idx'] = $this->iIdx;
        $aIns['detail']  = $this->sDetail;


        return $this->dbm->insert("tbl_bbs_detail", $aIns);
    }

    /**
     * 업로드 정보 입력
     *
     * @return int
     */
    public function registBbsUpload(): int
    {
        $aIns             = [];
        $aIns['bbs_idx']  = $this->iIdx;
        $aIns['file_idx'] = $this->iFileIdx;
        $aIns['path']     = $this->sFilePath;


        return $this->dbm->insert("tbl_bbs_upload", $aIns);
    }

    /**
     * 업로드 정보 입력
     *
     * @return int
     */
    public function registBbsFile(): int
    {
        $aIns            = [];
        $aIns['bbs_idx'] = $this->iIdx;
        // todo $this->sFileName 으로 변경 필요
        $aIns['filename'] = $this->getFileName();

        return $this->dbm->insert("tbl_bbs_file", $aIns);
    }

    /**
     * 컨텐츠 수정
     *
     * @return int
     */
    public function modifyBbs(): int
    {
        $aUpd          = [];
        $aUpd['title'] = $this->sTitle;

        $this->dbm->setBind(['idx' => $this->iIdx]);

        return $this->dbm->update("tbl_bbs", $aUpd, "idx = :idx:");
    }

    /**
     * 상세 수정
     *
     * @return int
     */
    public function modifyBbsDetail(): int
    {
        $aUpd           = [];
        $aUpd['detail'] = $this->sDetail;

        $where = "bbs_idx = :idx:";
        $this->dbm->setBind(['idx' => $this->iIdx]);

        return $this->dbm->update("tbl_bbs_detail", $aUpd, $where);
    }

    /**
     * 컨텐츠 삭제
     *
     * @return int
     */
    public function removeBbs(): int
    {
        $aUpd          = [];
        $aUpd['state'] = '100';

        $this->dbm->setBind(['idx' => $this->iIdx]);

        return $this->dbm->update("tbl_bbs", $aUpd, "idx = :idx:");
    }
}
