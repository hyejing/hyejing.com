<?php namespace App\Controllers\Bbs;

/* ===================================================================
    컨텐츠 리스트 페이지
=================================================================== */

use App\Controllers\BaseController;
use App\Models\BbsModel;
use CodeIgniter\Files\File;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;
use App\Libraries\S3test;

class Test extends BaseController
{
    protected S3test $s3;
    protected BbsModel $oBbs;
    protected array $param;
    protected array $where;


    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true);

        // 컨텐츠 모델 로드
        $this->oBbs = new BbsModel();

        //S3 파일업로드
        $this->s3   = new S3test();
    }



    public function test()
    {
        header('Access-Control-Allow-Origin: *');
        $callback = ['a'=>1,'b'=>2];
        //echo $_GET['callback'].'('.json_encode($callback).')';
        echo json_encode($callback);
        exit;
        vv($this->s3->ccopy());
        vv( $this->s3->getBucketList('imgs.mafiatv.co.kr'));
        vv( $this->s3->getObject('imgs.mafiatv.co.kr',"/dev_tmp_img09c8d4e21cdacb43e2534a3e7932ebe3.jpg"));
    }


    public function imgv()
    {
     echo form_open_multipart('/bbs/test/img')."
<input type='file' name='userfile'>
<input type='submit' value='업로드'>
</form>";
     exit;
    }

    public function img()
    {

        //vv($_FILES);

        try {
            //$this->setDefaultView();

            // 이미지 검증 룰 적용
            $validationRule = [
                'userfile' => [
                    'label' => 'Image File',
                    //'rules' => 'uploaded[userfile]|is_image[userfile]|mime_in[userfile,image/jpg,image/jpeg,image/gif,image/png,image/webp]|max_size[userfile,100]|max_dims[userfile,1024,768]',
                    'rules' => 'uploaded[userfile]|is_image[userfile]|mime_in[userfile,image/jpg,image/jpeg,image/gif,image/png,image/webp]',
                ],
            ];

            // 이미지 검증
            if ( !$this->validate($validationRule) ) {
                if ( empty($_FILES) ) {
                    $this->redirect('test');
                }
                $this->data_set['ERROR']['MESSAGE'] = var_export($this->validator->getErrors(), true);
                $this->displayError();
            }

            // 업로드 이미지 정보
            $img = $this->request->getFile('userfile');
            if ( !$img->hasMoved() ) {
                // 임시 파일 저장 처리
                $filepath = $img->store('tmp_img', $img->getClientName());

                // 파일 패스 가져오기
                $file_info                          = new File($filepath);
                $this->data_set['DATA']['flleinfo'] = $file_info->getBasename();

                // 임시 이미지 S3 업로드 후 삭제
                $temp_fileName = PATH_WRITABLE . 'uploads/tmp_img/' . $file_info->getBasename();;
                $s3_fileName = 'stg_test/' . randomString('unique') . $file_info->getExtension();
               vv( $this->s3->upload($temp_fileName, $s3_fileName));
                unlink($temp_fileName);
            }
            else {
                $this->data_set['DATA']['flleinfo'] = 'The file has already been moved.';
            }
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
        }

        //$this->redirect('test');
        $this->display();
    }
}