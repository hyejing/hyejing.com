<?php namespace App\Controllers;

class Kjw extends BaseController
{

    public function index()
    {
        $this->setDefaultView();

        $this->displayPop();
    }

    public function exec()
    {
        $file = $this->request->getFile('imgfile');
        v($file->getName());
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
            'body' => 'kjw',
        ];
    }
}