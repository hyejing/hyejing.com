<?php namespace App\Models;

/* ===================================================================
	관리자 모델링
=================================================================== */
use App\Libraries\Validation;
use App\Libraries\Protect;
use Exception;

class AdminModel extends MyModel
{
    protected Validation $valid;
    protected Protect $protect;

    protected array $aStatus;
    protected array $aSearchType;
    protected array $aManagerLevel;
    protected array $aViewCount;

    protected int $idx;
    protected int $state;
    protected int $level;
    protected string $id;
    protected string $pw;
    protected string $enc_pw;
    protected string $name;

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
        $this->aManagerLevel = [
            '0' => '일반',
            '1' => '아르바이트',
            '2' => '운영팀',
            '3' => '마스터',
            '4' => '마스터2',
            '9' => '백엔드',
        ];

        $this->aSearchType = [
            'id'       => '아이디',
            'name'     => '이름',
            'login_ip' => '로그인 IP',
            'reg_ip'   => '가입 IP',
        ];

        $this->aStatus = [
            '1' => '정상',
            '2' => '승인대기',
            '3' => '제재',
            '0' => '탈퇴',
        ];

        $this->aViewCount = [20, 50, 100];
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
        if ( empty($pw) === false && strlen($pw) >= 6 ) {
            $this->pw     = $pw;
            $this->enc_pw = $this->protect->getPasswordHash($pw);
        }
        else {
            throw new Exception('비밀번호는 6자 이상 입력해주세요.');
        }
    }

    public function setName($name)
    {
        if ( $this->valid->name($name) ) {
            $this->name = $name;
        }
        else {
            throw new Exception('이름은 2~20자의 한글과 영문으로만 입력이 가능합니다.');
        }
    }

    public function setLevel($managerLevel)
    {
        if ( array_key_exists($managerLevel, $this->aManagerLevel) === true ) {
            $this->level = $managerLevel;
        }
        else {
            throw new Exception('유효하지 않은 권한 등급입니다.');
        }
    }

    public function setStatus($state)
    {
        if ( array_key_exists($state, $this->aStatus) === true ) {
            $this->state = $state;
        }
        else {
            throw new Exception('유효하지 않은 상태 정보입니다.');
        }
    }

    public function setViewCount($view_count)
    {
        if ( empty($view_count) === false ) {
            if ( is_array($view_count) === false ) {
                $view_count = [$view_count];
            }
            $this->aViewCount = $view_count;
        }
    }

    public function addViewCount($view_count)
    {
        if ( is_array($view_count) === false ) {
            $view_count = [$view_count];
        }
        $aResult = array_unique(array_merge($this->aViewCount, $view_count));
        sort($aResult);

        $this->setViewCount($aResult);
    }

    /* ===================================================================
        Get Functions
    =================================================================== */
    public function getIdx(): int
    {
        return $this->idx ?? 0;
    }

    public function getEncPw(): string
    {
        return $this->enc_pw;
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

    public function getViewCount(): array
    {
        return $this->aViewCount;
    }

    /* ===================================================================
        Modules
    =================================================================== */
    /**
     * 관리자 등록
     *
     * @return bool
     * @throws Exception
     */
    public function registAdmin(): bool
    {
        // 아이디 중복체크
        $aInfo = $this->getInfoById();
        if ( isset($aInfo['idx']) && $aInfo['idx'] > 0 ) {
            $this->setResultData(['message' => '이미 존재하는 아이디입니다.']);

            return false;
        }

        // 디폴트 값 설정
        if ( isset($this->state) === false ) {
            $this->setStatus(2);    // 디폴트 회원상태 : 승인대기
        }
        if ( isset($this->level) === false ) {
            $this->setLevel(0); // 디폴트 권한등급 : 일반
        }

        // 관리자 정보 insert
        $rtv = $this->regist();
        if ( $rtv === false || $rtv <= 0 ) {
            $this->setResultData(['message' => '가입이 실패하였습니다.']);

            return false;
        }

        return true;
    }

    /* ===================================================================
        Sub Functions
    =================================================================== */
    /* --------------------------   Select   -------------------------- */
    /**
     * 관리자 카운트
     *
     * @param $where
     * @return int
     */
    public function getTotalCount($where): int
    {
        $this->dbs->setBind($this->aBind);

        return $this->dbs->getCount("tbl_admin AS tad", $where);
    }

    /**
     * 관리자 리스트
     *
     * @param $where
     * @param $offset
     * @param $lpp
     * @return array
     */
    public function getList($where, $offset = 0, $lpp = 20): array
    {
        $this->dbs->setBind($this->aBind);
        $lists = $this->dbs->getList("tbl_admin AS tad", "tad.*", $where, null, "tad.`idx` DESC", $offset, $lpp);

        $result = [];
        foreach ( $lists as $k => $v ) {
            $result[$k] = $this->_remakeInfo($v);
        }

        return $result;
    }

    /**
     * 로그인 용 관리자 정보 조회
     *
     * @return array
     */
    public function getInfoForLogin(): array
    {
        $where = "tad.`id`=:id: AND tad.`pw`=:pw:";

        // set bind
        $aBindData = [
            'id' => $this->id,
            'pw' => $this->enc_pw,
        ];
        $this->dbs->setBind($aBindData);
        $result = $this->dbs->get("tbl_admin AS tad", "tad.*", $where);

        return $this->_remakeInfo($result);
    }

    /**
     * 관리자 정보 조회 by ID
     *
     * @return array
     */
    public function getInfoById(): array
    {
        $this->dbs->setBind(['id' => $this->id]);
        $result = $this->dbs->get("tbl_admin AS tad", "tad.*", "tad.`id`=:id:");

        return $this->_remakeInfo($result);
    }

    /**
     * 관리자 정보 조회 by idx
     *
     * @return array
     */
    public function getInfo(): array
    {
        $this->dbs->setBind(['idx' => $this->idx]);
        $result = $this->dbs->get("tbl_admin AS tad", "tad.*", "tad.`idx`=:idx:");

        return $this->_remakeInfo($result);
    }

    /**
     * 조회된 관리자 정보 가공
     *
     * @param array $result
     * @return array
     */
    protected function _remakeInfo(array $result): array
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

    /* --------------------------   Insert   -------------------------- */
    /**
     * 관리자 정보 insert
     *
     * @return bool
     */
    public function regist(): bool
    {
        $ins               = [];
        $ins['id']         = $this->id;
        $ins['pw']         = $this->enc_pw;
        $ins['state']      = $this->state;
        $ins['level']      = $this->level;
        $ins['name']       = $this->name;
        $ins['login_ip']   = '';
        $ins['login_date'] = '0000-00-00 00:00:00';
        $ins['reg_ip']     = $this->getIp();
        $ins['reg_date']   = TIME_NOW;

        return $this->dbm->insert('tbl_admin', $ins);
    }

    /* --------------------------   Update   -------------------------- */
    /**
     * 비밀번호 변경
     *
     * @return bool
     */
    public function modifyPw(): bool
    {
        $upd       = [];
        $upd['pw'] = ":pw:";

        // set bind
        $aBindData = [
            'idx' => $this->idx,
            'pw'  => $this->enc_pw,
        ];
        $this->dbm->setBind($aBindData);
        $rtv = $this->dbm->update("tbl_admin", $upd, "`idx` = :idx:", ['pw']);

        return $rtv !== false;
    }

    /**
     * 관리자 이름 변경
     *
     * @return bool
     */
    public function modifyName(): bool
    {
        $upd         = [];
        $upd['name'] = ":name:";

        // set bind
        $aBindData = [
            'idx'  => $this->idx,
            'name' => $this->name,
        ];
        $this->dbm->setBind($aBindData);
        $rtv = $this->dbm->update("tbl_admin", $upd, "`idx` = :idx:", ['name']);

        return $rtv !== false;
    }

    /**
     * 관리자 정보 변경
     *
     * @return bool
     */
    public function modifyAdmin(): bool
    {
        $upd          = [];
        $upd['name']  = $this->name;
        $upd['level'] = $this->level;
        $upd['state'] = $this->state;

        $this->dbm->setBind(['idx' => $this->idx]);
        $rtv = $this->dbm->update("tbl_admin", $upd, "`idx` = :idx:");

        return $rtv !== false;
    }

    /**
     * 로그인 정보 갱신
     *
     * @return bool
     */
    public function modifyLoginInfo(): bool
    {
        $upd               = [];
        $upd['login_date'] = date("Y-m-d H:i:s");           // 로그인 시간
        $upd['login_ip']   = $this->getIp();                // 로그인 IP

        $this->dbm->setBind(['idx' => $this->idx]);
        $rtv = $this->dbm->update("tbl_admin", $upd, "`idx` = :idx:");

        return $rtv !== false;
    }
}
