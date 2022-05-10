<?php namespace App\Models;

/* ===================================================================
	컨텐츠 메타 모델링
=================================================================== */
use App\Libraries\Validation;
use App\Libraries\Protect;
use Exception;

class MetaModel extends MyModel
{
    protected Validation $valid;
    protected Protect $protect;

    protected array $aStatus;
    protected array $aSearchType;
    protected array $aManagerLevel;

    protected int $idx;
    protected int $status;
    protected int $level;
    protected string $id;
    protected string $password;
    protected string $enc_password;
    protected string $name;

    public function __construct()
    {
        parent::__construct();

        $this->valid   = new Validation();
        $this->protect = new Protect();

        $this->init();
    }

    /* ===================================================================
        Set Functions
    =================================================================== */
    protected function init()
    {
        $this->aManagerLevel = [
            '0' => '일반',
            '1' => '아르바이트',
            '2' => '운영팀',
            '3' => '마스터',
            '4' => '마스터2',
            '9' => '백엔드',
        ];

        $this->aSearchType = [
            'id'   => '아이디',
            'name' => '이름',
            'ip'   => '아이피',
        ];

        $this->aStatus = [
            '1' => '정상',
            '2' => '승인대기',
            '3' => '제재',
            '0' => '탈퇴',
        ];
    }

    public function setIdx($idx)
    {
        if ( $idx > 0 ) {
            $this->idx = $idx;
        }
        else {
            $this->idx = 0;
        }
    }

    public function setId($id)
    {
        if ( $this->valid->id($id) ) {
            $this->id = $id;
        }
        else {
            throw new Exception('ID는 4~16자의 영문소문자와 숫자로만 입력이 가능합니다.');
        }
    }

    public function setPw($pw)
    {
        if ( empty($pw) === false ) {
            $this->password     = $pw;
            $this->enc_password = $this->protect->getPasswordHash($pw);
        }
        else {
            throw new Exception('비밀번호를 입력해주세요.');
        }
    }

    public function setName($name)
    {
        if ( $this->valid->name($name) ) {
            $this->name = $name;
        }
        else {
            throw new Exception('이름은 2자 이상의 한글과 영문으로만 입력이 가능합니다.');
        }
    }

    public function setLevel($managerLevel)
    {
        if ( array_key_exists($managerLevel, $this->aManagerLevel) === true ) {
            $this->level = $managerLevel;
        }
        else {
            $this->level = 0;
        }
    }

    public function setStatus($status)
    {
        if ( array_key_exists($status, $this->aStatus) === true ) {
            $this->status = $status;
        }
        else {
            $this->status = 0;
        }
    }

    /* ===================================================================
        Get Functions
    =================================================================== */
    public function getEncPw(): string
    {
        return $this->enc_password;
    }

    public function getStatusList(): array
    {
        return $this->aStatus;
    }

    public function getManagerLevel(): array
    {
        return $this->aManagerLevel;
    }

    public function getSearchType(): array
    {
        return $this->aSearchType;
    }

    /* ===================================================================
        Select Functions
    =================================================================== */
    /**
     * 관리자 카운트
     */
    public function getTotalCount($where): int
    {
        $table = "tbl_admin AS tad";
        $this->dbs->setBind($this->aBind);

        return $this->dbs->getCount($table, $where);
    }

    /**
     * 관리자 리스트
     */
    public function getList($where, $offset = 0, $lpp = 20): array
    {
        $field   = "tad.*";
        $table   = "tbl_admin AS tad";
        $orderby = "tad.`idx` DESC";

        $this->dbs->setBind($this->aBind);
        $lists = $this->dbs->getList($table, $field, $where, null, $orderby, $offset, $lpp);

        $result = [];
        foreach ( $lists as $k => $v ) {
            $result[$k] = $this->_remakeInfo($v);
        }

        return $result;
    }

    /**
     * 관리자 정보
     */
    public function getInfoForLogin(): array
    {
        $where   = [];
        $where[] = "tad.`id`=:id:";
        $field   = "tad.*";
        $table   = "tbl_admin AS tad";

        // set bind
        $aBindData = [
            'id' => $this->id,
        ];
        $this->dbs->setBind($aBindData);

        return $this->dbs->get($table, $field, $where);
    }

    /**
     * 관리자 정보
     */
    public function getInfoById()
    {
        $where   = [];
        $where[] = "tad.`id`=:id:";
        $table   = "tbl_admin AS tad";
        $field   = "tad.*";

        // set bind
        $aBindData = [
            'id' => $this->id,
        ];
        $this->dbs->setBind($aBindData);
        $result = $this->dbs->get($table, $field, $where);

        return $this->_remakeInfo($result);
    }

    /**
     * 관리자 정보
     */
    public function getInfo()
    {
        $where   = [];
        $where[] = "tad.`idx`=:idx:";
        $table   = "tbl_admin AS tad";
        $field   = "tad.*";

        // set bind
        $aBindData = [
            'idx' => $this->idx,
        ];
        $this->dbs->setBind($aBindData);
        $result = $this->dbs->get($table, $field, $where);

        return $this->_remakeInfo($result);
    }

    /**
     * 관리자 추가 정보
     */
    protected function _remakeInfo($result)
    {
        $result['level_txt'] = '';
        $result['state_txt'] = '';
        if ( isset($result['idx']) === true ) {
            if ( isset($result['level']) && isset($this->aManagerLevel[$result['level']]) ) {
                $result['level_txt'] = $this->aManagerLevel[$result['level']];
            }
            if ( isset($result['state']) && isset($this->aStatus[$result['state']]) ) {
                $result['state_txt'] = $this->aStatus[$result['state']];
            }
        }

        return $result;
    }

    /**
     * 패스워드 확인
     */
    public function confirmPw(): bool
    {
        $where   = [];
        $where[] = "tad.`idx`=:idx:";
        $where[] = "tad.`password`=:password:";
        $table   = "tbl_admin AS tad";

        // set bind
        $aBindData = [
            'idx'      => $this->idx,
            'password' => $this->enc_password,
        ];
        $this->dbs->setBind($aBindData);
        $result = $this->dbs->getCount($table, $where);

        return $result > 0;
    }

    /* ===================================================================
        Insert / Update / Delete Functions
    =================================================================== */
    /**
     * 관리자 등록
     */
    public function regist(): bool
    {
        $ins               = [];
        $ins['id']         = $this->id;
        $ins['password']   = $this->enc_password;
        $ins['state']      = 2;
        $ins['level']      = 0;
        $ins['name']       = $this->name;
        $ins['ip']         = $this->getIp();
        $ins['login_date'] = '0000-00-00 00:00:00';
        $ins['reg_date']   = TIME_NOW;

        $rtv = $this->dbm->insert('tbl_admin', $ins);
        if ( $rtv === false || $rtv <= 0 ) {
            $this->setResultData(['message' => '가입이 실패하였습니다.']);
        }

        return true;
    }

    /**
     * 패스워드 변경
     */
    public function modifyPw(): bool
    {
        $upd             = [];
        $upd['password'] = ":password:";
        $where           = "`idx`=:idx: ";
        $table           = "tbl_admin";

        // set bind
        $aBindData = [
            'idx'      => $this->idx,
            'password' => $this->enc_password,
        ];
        $this->dbm->setBind($aBindData);
        $rtv = $this->dbm->update($table, $upd, $where, ['password']);

        return $rtv !== false;
    }

    /**
     * 패스워드 초기화
     */
    public function resetPw(): bool
    {
        $upd             = [];
        $upd['password'] = $this->protect->getPasswordHash("test1234");
        $where           = "`idx`=:idx: ";
        $table           = "tbl_admin";

        // set bind
        $aBindData = [
            'idx' => $this->idx,
        ];
        $this->dbm->setBind($aBindData);
        $rtv = $this->dbm->update($table, $upd, $where, ['password']);

        return $rtv !== false;
    }

    /**
     * 등급 변경
     */
    public function modifyLevel(): bool
    {
        $upd          = [];
        $upd['level'] = $this->level;
        $where        = "`idx`=:idx: ";
        $table        = "tbl_admin";

        // set bind
        $aBindData = [
            'idx' => $this->idx,
        ];
        $this->dbm->setBind($aBindData);
        $rtv = $this->dbm->update($table, $upd, $where);

        return $rtv !== false;
    }

    /**
     * 상태 변경
     */
    public function modifyStatus(): bool
    {
        $upd          = [];
        $upd['state'] = $this->status;
        $where        = "`idx`=:idx: ";
        $table        = "tbl_admin";

        // set bind
        $aBindData = [
            'idx' => $this->idx,
        ];
        $this->dbm->setBind($aBindData);
        $rtv = $this->dbm->update($table, $upd, $where);

        return $rtv !== false;
    }

    /**
     * 로그인 시간 갱신
     */
    public function updateLoginDate(): bool
    {
        $upd               = [];
        $upd['login_date'] = date("Y-m-d H:i:s");
        $where             = "`idx`=:idx: ";
        $table             = "tbl_admin";

        // set bind
        $aBindData = [
            'idx' => $this->idx,
        ];
        $this->dbm->setBind($aBindData);
        $rtv = $this->dbm->update($table, $upd, $where);

        return $rtv !== false;
    }

    /**
     * 로그인 IP 갱신
     */
    public function updateIp(): bool
    {
        $upd       = [];
        $upd['ip'] = $this->getIp();
        $where     = "`idx`=:idx: ";
        $table     = "tbl_admin";

        // set bind
        $aBindData = [
            'idx' => $this->idx,
        ];
        $this->dbm->setBind($aBindData);
        $rtv = $this->dbm->update($table, $upd, $where);

        return $rtv !== false;
    }
}
