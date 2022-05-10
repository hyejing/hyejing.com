<?php namespace App\Libraries;

/* ===================================================================
	검증 라이브러리
=================================================================== */

class Validation
{
	private string $pattern_id;
	private string $pattern_email;
	private string $pattern_name;
	private string $pattern_nickname;
	private string $pattern_numeric;

	public function __construct()
	{
		$this->pattern_id       = "/^[a-z]+[a-z0-9]{3,19}$/";
		$this->pattern_email    = "/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/";
		$this->pattern_name     = "/^[\x{AC00}-\x{D7AF}a-zA-Z\-\.\x20]{2,20}$/u";
		$this->pattern_nickname = "/^[\w\x{1100}-\x{11FF}\x{3130}-\x{318F}\x{AC00}-\x{D7AF}]{4,20}$/u";
		$this->pattern_numeric  = "/^[0-9]+$/";
	}

	/**
	 * 아이디 유효성 체크
	 */
	public function id($str): bool
	{
		return (bool)preg_match($this->pattern_id, $str);
	}

	/**
	 * 이메일 유효성 체크
	 */
	public function email($str): bool
	{
		return (bool)preg_match($this->pattern_email, $str);
	}

	/**
	 * 이름 유효성 체크
	 */
	public function name($str): bool
	{
		return (bool)preg_match($this->pattern_name, $str);
	}

	/**
	 * 닉네임 유효성 체크
	 */
	public function nickname($str): bool
	{
		return (bool)preg_match($this->pattern_nickname, $str);
	}

	/**
	 * 숫자 유효성 체크
	 */
	public function numeric($str): bool
	{
		return (bool)preg_match($this->pattern_numeric, $str);
	}
}

