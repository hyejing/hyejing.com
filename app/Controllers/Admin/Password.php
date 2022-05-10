<?php namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Password extends BaseController
{
    protected AdminModel $admin;
    protected array $param;
    protected array $where;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 4, '접근권한이 없습니다.');

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

            // set parameters
            $params = [
                'idx' => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => '잘못된 접근 입니다.'
                ],
            ];
            $param  = $this->chkParam($params, 'get');

            // 회원 IDX 설정
            $this->admin->setIdx($param['idx']);
            $aMember = $this->admin->getInfo();
            if ( isset($aMember['idx']) === false || $aMember['idx'] <= 0 ) {
                throw new Exception('일치하는 정보가 없습니다.', 5501);
            }

            // 출력 데이터
            $this->data_set['HTML']['title'] = "비밀번호 변경";
            $this->data_set['DATA']['info']  = $aMember;
        }
        catch ( Exception $e ) {
            $this->alert($e->getMessage(), 'close');
            exit;
        }

        $this->displayPop();
    }

    /**
     * 비밀번호 변경 실행
     *
     * @return void
     */
    public function exec()
    {
        try {
            $this->setDefaultView();

            // set parameters
            $params = [
                'idx'         => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => '잘못된 접근 입니다.'
                ],
                'pw'    => [
                    'default' => '',
                    'rules'   => 'required|min_length[6]|max_length[20]',
                    'error'   => '변경할 비밀번호는 6자 이상으로 입력해주세요.'
                ],
                'pw_re' => [
                    'default' => '',
                    'rules'   => 'required|min_length[6]|max_length[20]',
                    'error'   => '비밀번호 확인란을 6자 이상으로 입력해주세요.'
                ],
            ];
            $param  = $this->chkParam($params, 'post');

            // 패스워드 일치 여부 확인
            if ( $param['pw'] != $param['pw_re'] ) {
                throw new Exception('비밀번호가 서로 일치하지 않습니다.', 5116);
            }

            // 비밀번호 변경 대상
            $this->admin->setIdx($param['idx']);
            $this->admin->setPw($param['pw']);

            if ( $this->admin->modifyPw() === false ) {
                throw new Exception('비밀번호 변경에 실패했습니다', 5502);
            }

            $this->alert('비밀번호 변경에 성공했습니다.', 'close');
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
            'body' => 'admin/password',
        ];
    }
}
