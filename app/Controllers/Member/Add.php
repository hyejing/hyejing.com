<?php namespace App\Controllers\Member;

/* ===================================================================
	회원 등록/수정 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\MemberModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use mysql_xdevapi\Exception;
use Psr\Log\LoggerInterface;

class Add extends BaseController
{
	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		parent::initController($request, $response, $logger);

		// 로그인 체크
		$this->chkLogin(true, 4, '접근권한이 없습니다.');

		$this->member = new MemberModel();
	}

	/**
	 * 회원 등록 폼
	 */
	public function index(int $idx = null)
	{
		try {
			$this->setDefaultView();
			$this->chkPub();

			$params = [
				'idx' => '',
			];
			$param  = $this->chkParam($params, 'get');

			// URL로 넘어오는 파라미터 우선
			if ( empty($idx) ) {
				$idx = $param['idx'];
			}

			// 회원번호가 존재하면 회원정보 조회/출력
			if ( empty($idx) === false ) {
				$this->member->setIdx($idx);
				$aMember = $this->memeber->getInfo();

				$this->data_set['MEMBER'] = $aMember;
			}
		}
		catch ( Exception $e ) {
			$this->data_set['ERROR']['FILE']    = $e->getFile();
			$this->data_set['ERROR']['LINE']    = $e->getLine();
			$this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
			$this->displayError();
			exit;
		}

		$this->display();
	}

	/**
	 * 회원 등록 처리
	 */
	public function exec()
	{
		try {
			$params = [
				'id'       => '',
				'password' => '',
				'state'    => '',
				'nick'     => '',
			];
			$param  = $this->chkParam($params, 'post');

			$id = $param['id'];
			if ( empty($id) ) {
				throw new Exception("회원ID를 확인해주세요.");
			}

			// 회원ID 설정 및 유효성 검사
			$this->member->setId($id);
			if ( empty($this->member->getId()) ) {
				throw new Exception("회원ID(E-mail) 형식을 확인해주세요.");
			}

			// 추가 회원정보 설정
			$this->member->setPw($param['password']);
			$this->member->setStatus($param['state']);
			$this->member->setNick($param['nick']);

			$result = $this->member->insertMember();    // 회원등록 처리 실행
			if ( empty($result) === true ) {
				throw new Exception('등록 처리가 실패했습니다.', '3001');
			}
			else {
				$this->alert('회원등록 처리에 성공했습니다.', URL_DOMAIN . '/member/Lists');
			}
		}
		catch ( Exception $e ) {
			$this->data_set['ERROR']['FILE']    = $e->getFile();
			$this->data_set['ERROR']['LINE']    = $e->getLine();
			$this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
			$this->displayError();
			exit;
		}
	}

	protected function setDefaultView()
	{
		// HTML
		$this->view_page = [
			'body'      => 'member/add',
			// 'import_js' => 'member/add.js',
		];
	}
}