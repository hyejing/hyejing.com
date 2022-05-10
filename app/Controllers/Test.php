<?php namespace App\Controllers;

/* ===================================================================
	메인 페이지
=================================================================== */

use App\Libraries\RedisDriver;
use App\Libraries\S3;
use App\Models\TestModel;
use CodeIgniter\Files\File;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Psr\Log\LoggerInterface;

/**
 * https://cms.onslick.com/test
 */
class Test extends BaseController
{
    protected TestModel $test;

    //protected S3 $s3;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->test = new TestModel();
        //$this->s3   = new S3();
    }

    public function index()
    {
        try {
            $this->setDefaultView();

            $this->test->test();
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
        }

        $this->display();
    }

    // https://fiverse-admin.devlabs.co.kr/test/lang_diff?file=validation
    public function lang_diff()
    {
        try {
            // parameters
            $params = [
                'file' => [
                    'default' => '',
                    'rules'   => 'required',
                    'error'   => '파일명이 필요합니다..',
                ],
            ];
            $param  = $this->chkParam($params, 'get');

            $supportedLocales = $this->request->config->supportedLocales;

            $path = [];
            foreach ( $supportedLocales as $v ) {
                $path[$v] = require PATH_APP . "Language/" . $v . "/" . ucfirst($param['file']) . ".php";
            }

            $chk = 0;
            foreach ( $path as $k => $v ) {
                $aVals     = [];
                $aDupleVal = [];

                foreach ( $v as $vk => $vv ) {
                    // 중복 값 체크
                    if ( in_array($vv, $aVals) === true ) {
                        $aDupleVal[] = $vv;
                    }
                    else {
                        $aVals[] = $vv;
                    }
                }

                if ( count($aDupleVal) > 0 ) {
                    echo '##################################################################################### ' . "<br>";
                    echo '[value 중복 체크]' . "<br>";
                    echo '==> 언어 -> ' . $k . "<br>";
                    v($aDupleVal);
                }

                if ( $k != $this->request->config->defaultLocale ) {
                    if ( count($aDupleVal) == 0 ) {
                        echo '##################################################################################### ' . "<br>";
                        echo '[체크 언어]' . "<br>";
                        echo $k . "<br>";
                    }

                    // 기본 설정 파일만 존재하는 키값
                    $diff = array_diff_key($path[$this->request->config->defaultLocale], $v);
                    if ( count($diff) > 0 ) {
                        echo '============================================================ ' . "<br>";
                        echo ':::DIFF::: ' . $this->request->config->defaultLocale . ' <=> ' . $k . "<br>";
                        v($diff);
                        echo '============================================================ ' . "<br>";
                        $chk++;
                    }

                    // 다른 언어 파일에만 존재하는 키값
                    $diff2 = array_diff_key($v, $path[$this->request->config->defaultLocale]);
                    if ( count($diff2) > 0 ) {
                        echo '============================================================ ' . "<br>";
                        echo ':::DIFF::: ' . $k . ' <=> ' . $this->request->config->defaultLocale . "<br>";
                        v($diff2);
                        echo '============================================================ ' . "<br>";
                        $chk++;
                    }

                    echo '##################################################################################### ' . "<br>";
                }
            }

            if ( $chk == 0 ) {
                echo '정상';
            }
        }
        catch ( Exception $e ) {
            echo $e->getMessage();
        }
    }

    public function img()
    {
        try {
            $this->setDefaultView();

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
                $this->s3->upload($temp_fileName, $s3_fileName);
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

    protected function setDefaultView()
    {
        // HTML
        $this->view_page = [
            'body' => 'test',
        ];
    }

    public function redis()
    {
        try {
            $this->setDefaultView();

            if ( true ) {
                $oRedis       = RedisDriver::getInstance('redis_primary');
                $oRedisReader = RedisDriver::getInstance('redis_reader');

                $aData = [
                    'prefix'  => 'test',
                    'key'     => 'rediskey',
                    'expires' => 60,
                    'data'    => ['test'],
                ];
                $oRedis->setRedis($aData);
                v($oRedis->getRedis('test', 'rediskey'));
            }
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
        }

        $this->display();
    }

    public function excel()
    {
        try {
            // 엑셀 라이브러리 로드
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->getTabColor()->setRGB('FF0000');               // 탭칼라
            $sheet->getDefaultRowDimension()->setRowHeight(15);    // 기본행 높이 설정

            // 제목
            $sheet->setCellValue('A1', '번호');
            $sheet->setCellValue('B1', '타입');
            $sheet->setCellValue('C1', '카테고리');
            $sheet->setCellValue('D1', '금지어');
            $sheet->setCellValue('E1', '메모');
            $sheet->setCellValue('F1', '저작권자');
            $sheet->setCellValue('G1', '등록일');
            $sheet->setCellValue('H1', '사용');

            // 셀너비 자동설정
            foreach ( $sheet->getColumnIterator() as $column ) {
                $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
            }

            $writer   = new Xlsx($spreadsheet);
            $filename = 'list_' . date('ymd_his');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;
        }
        catch ( Exception $e ) {
            $this->data_set['ERROR']['FILE']    = $e->getFile();
            $this->data_set['ERROR']['LINE']    = $e->getLine();
            $this->data_set['ERROR']['MESSAGE'] = $e->getMessage();
            $this->displayError();
        }

        $this->display();
    }
}
