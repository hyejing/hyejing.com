<?php namespace App\Models;

/* ===================================================================
	사용자 모델링
=================================================================== */
use App\Libraries\Protect;
use Exception;

class UserModel extends MyModel
{
    protected Protect $protect;
    protected AdminModel $admin;
    protected MenuModel $menu;

    protected bool $isLogin;

    protected int $idx;
    protected int $level;
    protected int $status;
    protected string $id;
    protected string $pw;
    protected string $name;
    protected string $login_ip;
    protected string $login_date;
    protected string $reg_ip;
    protected string $reg_date;

    public function __construct()
    {
        parent::__construct();

        $this->protect = new Protect();
        $this->admin   = new AdminModel();
        $this->menu    = new MenuModel();

        $this->clear();
        $this->init();
    }

    /* ===================================================================
        Set Functions
    =================================================================== */
    /**
     * attribute 초기화
     */
    protected function clear()
    {
        $this->isLogin    = false;
        $this->idx        = 0;
        $this->id         = '';
        $this->pw         = '';
        $this->status     = 0;
        $this->level      = 0;
        $this->name       = '';
        $this->login_ip   = '';
        $this->login_date = '';
        $this->reg_ip     = '';
        $this->reg_date   = '';
    }

    /**
     * 로그인 정보 가져온 후 쿠키 재셋팅
     */
    protected function init()
    {
        $token_str = $_COOKIE[COOKIE_ACCESS_TOKEN] ?? '';
        if ( strpos($token_str, '.') !== false ) {
            $token_arr = explode('.', $token_str);
            $this->protect->setIv(base64_decode($token_arr[1]));
            $token = $this->protect->decryptArr($token_arr[0]);

            if ( empty($token['uid']) === false && $token['expire'] >= TIME_STAMP ) {
                $this->id = $token['uid'];
                $this->setAttribute();
            }
        }
    }

    /**
     * 로그인 정보 셋팅 및 쿠키 생성
     */
    protected function setAttribute()
    {
        $this->admin->setId($this->id);

        $info = $this->admin->getInfoById();

        if ( empty($info['idx']) === false ) {
            $this->isLogin    = true;
            $this->idx        = $info['idx'];
            $this->id         = $info['id'];
            $this->status     = $info['state'];
            $this->level      = $info['level'];
            $this->name       = $info['name'];
            $this->login_ip   = $info['login_ip'];
            $this->login_date = $info['login_date'];
            $this->reg_ip     = $info['reg_ip'];
            $this->reg_date   = $info['reg_date'];

            // set cookie
            $user           = [];
            $user['uidx']   = $this->idx;
            $user['uid']    = $this->id;
            $user['expire'] = intval(TIME_STAMP + COOKIE_TIME);

            // 로그인 인증 쿠키
            $this->protect->createIv();
            $access_token = $this->protect->encryptArr($user);
            set_cookies(COOKIE_ACCESS_TOKEN, $access_token . '.' . base64_encode($this->protect->getIv()), 0);
        }
    }

    public function setIdx($idx)
    {
        if ( $idx > 0 ) {
            $this->idx = $idx;
        }
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setPw($pw)
    {
        $this->pw = $pw;
    }

    /* ===================================================================
        Get Function
    =================================================================== */
    public function getIdx(): int
    {
        return $this->idx ?? 0;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getManagerLevel(): int
    {
        return $this->level ?? 0;
    }

    /**
     * 로그인 여부
     */
    public function isLogin(): bool
    {
        return $this->isLogin;
    }

    /* ===================================================================
        Modules
    =================================================================== */
    /**
     * 로그인 체크
     */
    public function chkLogin(): array
    {
        $result               = [];
        $result['idx']        = $this->idx;
        $result['id']         = $this->id;
        $result['state']      = $this->status;
        $result['level']      = $this->level;
        $result['name']       = $this->name;
        $result['login_ip']   = $this->login_ip;
        $result['login_date'] = $this->login_date;
        $result['reg_ip']     = $this->reg_ip;
        $result['reg_date']   = $this->reg_date;

        return $result;
    }

    /**
     * 로그인 처리
     */
    public function login(): bool
    {
        $result = false;

        try {
            // 로그인 처리할 회원정보 조회
            $aInfo = $this->getInfo();
            if ( isset($aInfo['idx']) === false || $aInfo['idx'] <= 0 ) {
                throw new Exception('일치하는 정보가 없습니다.', 5501);
            }

            // 관리자 상태값에 의해 처리 메시지 설정
            switch ( $aInfo['state'] ) {
                case 0: // 탈퇴
                    throw new Exception('탈퇴된 계정입니다.', 5600);

                case 1: // 정상
                    break;

                case 2: // 승인대기
                    throw new Exception('관리자 승인이 필요합니다. 잠시 대기해주세요.', 5602);

                case 3: // 제재
                    throw new Exception('사용 불가능한 계정입니다.', 5603);

                default:
                    throw new Exception('잘못된 처리입니다. 관리자에게 문의 바랍니다.', 5600);
            }

            // 최종 로그인 정보 갱신
            $this->admin->modifyLoginInfo();    // 로그인 시간 갱신

            // 정보 셋팅 및 쿠키 생성
            $this->setAttribute();

            $result = true;
        }
        catch ( Exception $e ) {
            $rtv            = [];
            $rtv['result']  = false;
            $rtv['code']    = $e->getCode();
            $rtv['file']    = $e->getFile();
            $rtv['line']    = $e->getLine();
            $rtv['message'] = $e->getMessage();
            $this->setResultData($rtv);
        }

        return $result;
    }

    /**
     * 로그아웃 처리
     */
    public function logout()
    {
        set_cookies(COOKIE_ACCESS_TOKEN, '', -1);
    }

    /* ===================================================================
        Sub Functions
    =================================================================== */
    /* --------------------------   Select   -------------------------- */
    /**
     * 회원정보 가져오기 By idx
     *
     * @return array
     */
    public function getInfo(): array
    {
        $this->admin->setIdx($this->idx);

        return $this->admin->getInfo();
    }

    /**
     * 회원정보 가져오기 For Login
     *
     * @return array
     * @throws Exception
     */
    public function getInfoForLogin(): array
    {
        $this->admin->setId($this->id);
        $this->admin->setPw($this->pw);

        return $this->admin->getInfoForLogin();
    }

    /**
     * 회원 메뉴 셋팅
     *
     * @return array
     * @throws Exception
     */
    public function getMenu(): array
    {
        $result = [];

        if ( $this->isLogin ) {
            $where   = [];
            $where[] = "tadme.`level` <= :level:";// 로그인한 계정의 권한
            $where[] = "tadme.`state` = :state:"; // 사용 여부

            $aBindData = [
                'level' => $this->level,
                'state' => 1,
            ];
            $this->menu->addBindData($aBindData);
            $result = $this->menu->getListMenu($where);      // 조건에 맞는 메뉴 추출
        }


        return $result;
    }
}