<?php namespace App\Controllers;

/* ===================================================================
	ckeditor4 이미지 업로드 처리 - @_ckeditor4_add
=================================================================== */

use App\Libraries\RedisDriver;
use App\Libraries\S3;
use CodeIgniter\Files\File;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Psr\Log\LoggerInterface;

class ImageUp extends BaseController
{
    protected S3 $s3;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 로그인 체크
        $this->chkLogin(true, 2, '접근권한이 없습니다.', true);

        $this->s3 = new S3();
    }

    /**
     * ckeditor 이미지 s3 tmp 업로드 처리
     */
    public function editerTmpImgUpProc()
    {
        try {
            $params = [
                'up_type' => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => 'empty_type',
                ],
            ];
            // 파라미터 체크
            $aParams = $this->chkParam($params, 'post');

            // 이미지 검증 룰 적용
            $validationRule = [
                'imgfile' => [
                    'label' => 'Image File',
                    'rules' => 'uploaded[imgfile]|is_image[imgfile]|mime_in[imgfile,image/jpg,image/jpeg,image/gif,image/png,image/webp]',
                ],
            ];

            // 이미지 검증 (plugin.js에서 검증하지만,, 다시 검증)
            if ( !$this->validate($validationRule) ) {
                throw new Exception(var_export($this->validator->getErrors(), true), '9998');
            }

            // 멀티 업로드 이미지 정보
            //$img = $this->request->getFileMultiple();
            if ( $imagefile = $this->request->getFiles() ) {
                $aData = [];
                foreach ( $imagefile['imgfile'] as $img ) {
                    if ( $img->isValid() && !$img->hasMoved() ) {
                        // 임시 파일 저장 처리
                        $filepath = $img->store('tmp_img', $img->getClientName());

                        // 파일 정보 가져오기
                        $file_info = new File($filepath);

                        // 임시 파일 S3 업로드 후 삭제
                        $temp_fileName = PATH_WRITABLE . 'uploads/tmp_img/' . $file_info->getBasename();;
                        $s3_fileName = S3_TMP_UPLOAD_FOLDER . '/' . randomString('unique') . '.' . $file_info->getExtension();
                        $this->s3->upload($temp_fileName, $s3_fileName);

                        unlink($temp_fileName);

                        array_push($aData, URL_IMG_DOMAIN . '/' . $s3_fileName);// 리턴용 데이터 설정
                    }
                }

                $aOutPut = [
                    'success' => true,
                    'code'    => '0001',
                    'msg'     => 'success',
                    'data'    => $aData
                ];
            }
            else {
                $aOutPut = [
                    'success' => false,
                    'code'    => '9997',
                    'msg'     => 'The files are empty.',
                    'data'    => []
                ];
            }
        }
        catch ( Exception $e ) {
            $aOutPut = [
                'success' => false,
                'code'    => $e->getCode(),
                'msg'     => $e->getMessage(),
                'data'    => $e->getFile() . '=' . $e->getLine(),
                //'data'    => $e,
            ];
        }

        $this->displayJson($aOutPut);
    }

}
