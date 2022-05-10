<?php namespace App\Controllers\Admin;

/* ===================================================================
	내 정보 - 우측 상단
=================================================================== */

use App\Controllers\BaseController;
use App\Models\AdminModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Mypage extends BaseController
{
    protected UserModel $user;
    protected AdminModel $admin;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true);

        // 유저 관련 모델링
        $this->user = new UserModel();

        // 정보 변경용 관리자 모델링
        $this->admin = new AdminModel();
    }

    /**
     * 기본 출력 페이지
     *
     * @return void
     */
    public function index()
    {
        try {
            $this->setDefaultView();

            // 출력 데이터
            $this->data_set['DATA']          = $this->user->getInfo();
            $this->data_set['HTML']['title'] = "내 정보";
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
        }

        $this->displayPop();
    }

    /**
     * 내 비밀번호 수정
     *
     * @return void
     */
    public function modifyPw()
    {
        try {
            // set parameters
            $params = [
                'pw_old' => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => '기존 비밀번호를 입력해주세요.'
                ],
                'pw'     => [
                    'default' => '',
                    'rules'   => 'required|min_length[6]|max_length[20]',
                    'error'   => '변경할 비밀번호를 6자 이상으로 입력해주세요.'
                ],
                'pw_re'  => [
                    'default' => '',
                    'rules'   => 'required|min_length[6]|max_length[20]',
                    'error'   => '비밀번호 확인란을 6자 이상으로 입력해주세요.'
                ],
            ];
            $param  = $this->chkParam($params, 'post');

            // 패스워드 일치 여부 확인
            if ( $param['pw'] != $param['pw_re'] ) {
                throw new Exception('변경할 비밀번호가 서로 일치하지 않습니다.', 5116);
            }

            // 입력받은 기존 비밀번호로 정보조회
            $this->user->setPw($param['pw_old']);
            $aInfo = $this->user->getInfoForLogin();
            if ( isset($aInfo['idx']) === false || $aInfo['idx'] <= 0 ) {
                throw new Exception('비밀번호가 일치하지 않습니다.', 5115);
            }

            // 비밀번호 수정 프로세스
            $this->admin->setIdx($aInfo['idx']);
            $this->admin->setPw($param['pw']);
            if ( $this->admin->modifyPw() === false ) {
                $rtv = $this->admin->getResultData();
                throw new Exception($rtv['message']);
            }

            // 수정 성공
            $this->alert('성공적으로 수정 되었습니다.', 'move', URL_DOMAIN . '/admin/mypage');
        }
        catch ( Exception $e ) {
            $this->alert($e->getMessage(), 'back');
            exit;
        }
    }

    /**
     * 내 이름 수정
     *
     * @return void
     */
    public function modifyName()
    {
        try {
            // set parameters
            $params = [
                'name' => [
                    'default' => '',
                    'rules'   => 'required|min_length[2]|max_length[20]',
                    'error'   => '이름은 2~20자의 한글 및 영문으로 입력해주세요.',
                ],
            ];
            $param  = $this->chkParam($params, 'post');

            // 현재 로그인된 정보조회
            $aInfo = $this->user->getInfo();
            if ( isset($aInfo['idx']) === false || $aInfo['idx'] <= 0 ) {
                throw new Exception('일치하는 정보가 없습니다.', 5501);
            }

            // 이름 수정 프로세스
            $this->admin->setIdx($aInfo['idx']);
            $this->admin->setName($param['name']);
            if ( $this->admin->modifyName() === false ) {
                $rtv = $this->admin->getResultData();
                throw new Exception($rtv['message']);
            }

            // 수정 성공
            $this->alert('성공적으로 수정 되었습니다.', 'move', URL_DOMAIN . '/admin/mypage');
        }
        catch ( Exception $e ) {
            $this->alert($e->getMessage(), 'back');
            exit;
        }
    }

    /**
     * view page 셋팅
     *
     * @return void
     */
    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body' => 'admin/mypage',
        ];
    }
}
