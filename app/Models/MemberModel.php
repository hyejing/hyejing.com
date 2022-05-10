<?php namespace App\Models;

/* ===================================================================
    회원 모델링
=================================================================== */
use App\Libraries\Protect;
use App\Libraries\Validation;

class MemberModel extends MyModel
{
    protected Validation $valid;
    protected Protect $protect;

    protected array $aState;
    protected array $aSearchType;

    protected int $idx;
    protected string $id;
    protected string $pw;
    protected string $enc_pw;
    protected string $nick;
    protected int $state;

    public function __construct()
    {
        parent::__construct();

        // 입력값 검증 라이브러리
        $this->valid = new Validation();
        // 암호화 관련 라이브러리
        $this->protect = new Protect();

        $this->init();
    }

    /* ===================================================================
        Set Functions
    =================================================================== */
    protected function init()
    {
        $this->aState = [
            '1' => '정상',
            '0' => '탈퇴',
        ];

        $this->aSearchType = [
            'id'       => '아이디',
            'nick'     => '닉네임',
            'login_ip' => '로그인 IP',
            'join_ip'  => '가입 IP',
        ];
    }

    /**
     * 회원 번호 설정
     */
    public function setIdx(int $idx)
    {
        if ( $idx > 0 ) {
            $this->idx = $idx;
        }
        else {
            $this->idx = 0;
        }
    }

    /**
     * 회원 ID 설정
     */
    public function setId($id)
    {
        // 이메일로 아이디사용
        if ( $this->valid->email($id) ) {
            $this->id = $id;
        }
        else {
            $this->id = null;
        }
    }

    /**
     * 비밀번호 설정
     *
     * @param $pw
     * @return void
     */
    public function setPw($pw)
    {
        if ( empty($pw) === false ) {
            $this->pw     = $pw;
            $this->enc_pw = $this->protect->getPasswordHash($pw);
        }
        else {
            $this->pw     = null;
            $this->enc_pw = null;
        }
    }

    /**
     * 상태값 설정
     *
     * @param $state
     * @return void
     */
    public function setStatus($state)
    {
        if ( array_key_exists($state, $this->aState) === true ) {
            $this->state = $state;
        }
        else {
            $this->state = 0;
        }
    }

    /**
     * 닉네임 설정
     * 
     * @param $nick
     * @return void
     */
    public function setNick($nick)
    {
        if ( $this->valid->nickname($nick) ) {
            $this->nick = $nick;
        }
        else {
            $this->nick = null;
        }
    }


    /* ===================================================================
        Get Functions
    =================================================================== */
    /**
     * 설정된 회원ID 출력
     */
    public function getId(): string
    {
        if ( empty($this->id) === false ) {
            return $this->id;
        }
        else {
            return false;
        }
    }

    /**
     * 회원 번호 출력
     */
    public function getIdx(): int
    {
        if ( empty($this->idx) === false ) {
            return $this->idx;
        }
        else {
            return false;
        }
    }

    public function getStatusList(): array
    {
        return $this->aState;
    }

    public function getSearchType(): array
    {
        return $this->aSearchType;
    }

    /**
     * 회원정보 가공
     *
     * @param $result
     * @return mixed
     */
    protected function _remakeInfo($result): array
    {
        $result['state_txt'] = '';
        if ( isset($result['idx']) === true ) {
            if ( isset($result['state']) && isset($this->aState[$result['state']]) ) {
                $result['state_txt'] = $this->aState[$result['state']];
            }
        }

        return $result;
    }

    /* ===================================================================
        Select Functions
    =================================================================== */
    /**
     * 회원 정보
     */
    public function getInfo(): array
    {
        $this->dbs->setBind(['idx' => $this->idx]);

        return $this->dbs->get("tbl_member AS tme", "tme.*", "tme.`idx`=:idx:");
    }

    /**
     * 총 회원 카운트
     *
     * @param $where
     * @return int
     */
    public function getTotalCount($where): int
    {
        $table = "tbl_member AS tme INNER JOIN tbl_member_info AS tmein ON tme.idx = tmein.member_idx";
        $this->dbs->setBind($this->aBind);

        return $this->dbs->getCount($table, $where);
    }

    /**
     * 회원 리스트
     *
     * @param $where
     * @param $offset
     * @param $lpp
     * @return array
     */
    public function getList($where, $offset = 0, $lpp = 20): array
    {
        $table   = "tbl_member AS tme INNER JOIN tbl_member_info AS tmein ON tme.idx = tmein.member_idx";
        $field   = "tme.idx, tme.state, tme.id, tme.nick, tme.reg_ip, tme.reg_date";
        $orderby = "tme.`idx` DESC";

        $this->dbs->setBind($this->aBind);
        $lists = $this->dbs->getList($table, $field, $where, null, $orderby, $offset, $lpp);

        $result = [];
        foreach ( $lists as $k => $v ) {
            $result[$k] = $this->_remakeInfo($v);
        }

        return $result;
    }

    /* ===================================================================
        Insert / Update / Delete Functions
    =================================================================== */

    /**
     * 회원 추가
     */
    public function insertMember(string $id = null): int
    {
        // 파라미터로 받은 ID값 우선
        if ( empty($id) === false ) {
            $this->setId($id);
        }
        // ID값 없으면 입력진행 안함
        if ( empty($this->id) === true ) {
            return false;
        }
        $transKey = "member_insert";        // 트랜잭션 키
        $this->dbm->transBegin($transKey);  // 트랜잭션 시작

        // 회원 테이블 데이터 입력
        $aMem             = [];
        $aMem['id']       = $this->id;
        $aMem['pw']       = $this->enc_pw;
        $aMem['state']    = $this->state ?? 1;
        $aMem['nick']     = $this->nick;
        $aMem['reg_date'] = TIME_NOW;

        $memIdx = $this->dbm->insert("tbl_member", $aMem);
        if ( empty($memIdx) === true ) {
            return false;
        }

        // 회원정보 테이블 데이터 입력
        $aInfo                = [];
        $aInfo['member_idx']  = $memIdx;
        $aInfo['login_count'] = 0;
        $aInfo['login_last']  = '0000-00-00 00:00:00';
        $aInfo['login_ip']    = '';
        $aInfo['join_ip']     = $this->getIp();
        $aInfo['join_agent']  = empty($this->getAgent()->isMobile()) === true ? "w" : "m";

        $infoIdx = $this->dbm->insert("tbl_member_info", $aInfo);
        if ( empty($infoIdx) === false && $this->dbm->transStatus($transKey) ) {
            $this->dbm->transCommit($transKey); // 트랜잭션 커밋

            return $memIdx;
        }
        else {
            $this->dbm->transRollback($transKey);   // 트랜잭션 롤백

            return false;
        }
    }
}