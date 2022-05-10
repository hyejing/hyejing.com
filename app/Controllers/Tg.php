<?php namespace App\Controllers;

/* ===================================================================
	텔레그램 푸시
=================================================================== */

use Exception;

class Tg extends BaseController
{
    /**
     * 푸시 메시지 발송 API
     */
    public function send()
    {
        try {
            // parameters
            $params = [
                'id'  => ['default' => ''],
                'msg' => ['default' => ''],
            ];
            $param  = $this->chkParam($params, 'get');
            push_alarm($param['msg'], $param['id']);
        }
        catch ( Exception $e ) {
            $result            = [];
            $result['file']    = $e->getFile();
            $result['line']    = $e->getLine();
            $result['message'] = $e->getMessage();
            push_alarm($result);
        }
    }

    /**
     * 푸시 메시지 보내기 실행
     */
    public function send_cli($url)
    {
        if ( is_cli() ) {
            $handle = curl_init(urldecode($url));
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($handle, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($handle);

            if ( $response === false ) {
                $errno = curl_errno($handle);
                $error = curl_error($handle);

                // 로그기록
                $log            = [];
                $log['ERROR']   = 'Curl error: ' . $error . ' (' . $errno . ')';
                $log['MESSAGE'] = urldecode($url);
                write_log('push_error', $log, 'telegram');
            }
            curl_close($handle);
        }
    }
}

