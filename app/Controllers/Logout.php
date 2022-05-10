<?php namespace App\Controllers;

/* ===================================================================
	로그아웃 페이지
=================================================================== */

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Logout extends BaseController
{
    /**
     * 로그아웃 페이지
     *
     * @return void
     */
    public function index()
    {
        $this->user->logout();

        $this->redirect('login');
    }
}