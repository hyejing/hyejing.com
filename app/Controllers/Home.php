<?php namespace App\Controllers;

/* ===================================================================
	메인 페이지
=================================================================== */

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class Home extends BaseController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true);
    }

    public function index()
    {
        try {
            $this->setDefaultView();
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
        }

        $this->display();
    }

    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body' => 'home',
        ];
    }
}
