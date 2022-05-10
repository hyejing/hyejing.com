<?php namespace App\Libraries;

/* ===================================================================
	암복호화 라이브러리
=================================================================== */

class Protect
{
    protected string $alg;
    protected string $secure_key;
    protected string $secure_iv;

    public function __construct()
    {
        // 암호화 알고리즘
        $this->alg = 'AES-256-CBC';

        // 암호화 키
        $this->secure_key = '9a1D5a16216DdC9c08fC9e7uC2A41x8o5';
    }

    public function getSecureKey(): string
    {
        return $this->secure_key;
    }

    /* ===================================================================
        iv 셋팅
    =================================================================== */
    public function createIv($str = '')
    {
        if ( empty($str) === true ) {
            $this->secure_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->alg));
        }
        else {
            $this->secure_iv = substr(hash('sha256', $str), 0, openssl_cipher_iv_length($this->alg));
        }
    }

    public function setIv($iv)
    {
        if ( empty($iv) === true ) {
            $this->secure_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->alg));
        }
        else {
            $this->secure_iv = substr($iv . str_repeat('#', 15), 0, openssl_cipher_iv_length($this->alg));
        }
    }

    public function getIv(): string
    {
        return $this->secure_iv;
    }

    /* ===================================================================
        암호화 처리
    =================================================================== */
    /**
     * 패스워드 암호화
     *
     * @param null $str
     * @return string
     */
    public function getPasswordHash($str = null): string
    {
        return hash('sha512', hash('md5', $this->secure_key . $str));
    }

    /**
     * 문자 암호화
     */
    public function encrypt($str = null)
    {
        if ( empty($str) === true ) {
            return false;
        }

        return $this->encrypt_proc($str);
    }

    /**
     * 문자 복호화
     */
    public function decrypt($str)
    {
        if ( empty($str) === true ) {
            return false;
        }

        return $this->decrypt_proc($str);
    }

    /**
     * 배열 암호화
     */
    public function encryptArr($arr = [])
    {
        if ( is_array($arr) === false || empty($arr) === true ) {
            return false;
        }

        $shuffle_arr = $this->shuffleArr($arr);
        $str         = json_encode($shuffle_arr);

        return $this->encrypt_proc($str);
    }

    /**
     * 배열 복호화
     */
    public function decryptArr($str = '')
    {
        if ( empty($str) === true ) {
            return false;
        }

        $decoding_string = $this->decrypt_proc($str);

        return json_decode($decoding_string, true);
    }

    /* ===================================================================
        Basic Function
    =================================================================== */
    /**
     * 암호함수
     */
    protected function encrypt_proc($str = null)
    {
        if ( $str == null || $str == '' ) {
            return false;
        }

        $encoding_string = openssl_encrypt($str, $this->alg, $this->secure_key, OPENSSL_RAW_DATA, $this->secure_iv);

        return base64_encode($encoding_string);
    }

    /**
     * 복호함수
     */
    protected function decrypt_proc($str = null)
    {
        if ( $str == null || $str == '' ) {
            return false;
        }

        $decoding_string = base64_decode($str);

        return openssl_decrypt($decoding_string, $this->alg, $this->secure_key, OPENSSL_RAW_DATA, $this->secure_iv);
    }

    /**
     * 배열 랜덤 섞기
     */
    protected function shuffleArr($arr)
    {
        if ( !is_array($arr) ) {
            return $arr;
        }

        $keys = array_keys($arr);
        shuffle($keys);

        $result = [];
        foreach ( $keys as $key ) {
            $result[$key] = $arr[$key];
        }

        return $result;
    }
}
