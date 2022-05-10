<?php use App\Helpers\Common_helper;

/* ===================================================================
	공통함수
=================================================================== */
/**
 * 디렉토리 생성
 */
function exec_mkdir($dir_name = '', $chmod = 0777): bool
{
	if ( empty($dir_name) === true ) {
		return false;
	}

	// 절대경로부터 시작함
	$dirs = explode('/', $dir_name);
	$d    = '/';

	foreach ( $dirs as $v ) {
		if ( $v === '' ) {
			continue;
		}

		$d .= $v . '/';
		if ( !is_dir($d) ) {
			umask(0);

			if ( !mkdir($d, $chmod) ) {
				return false;
			}
		}
	}

	@chmod($dir_name, $chmod);

	return true;
}

/**
 * 파일 로그 기록하기
 */
function write_log($filename, $msg = '', $folder = '', $time_type = 'D'): bool
{
	if ( empty($folder) === false ) {
		exec_mkdir(PATH_LOG . rtrim($folder, DIRECTORY_SEPARATOR));
	}

	switch ( $time_type ) {
		case 'Y':
			$write_path = PATH_LOG . rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename . '_' . date('Y', strtotime('NOW')) . CNF_SUFFIX_LOG;
			break;

		case 'M':
			$write_path = PATH_LOG . rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename . '_' . date('Ym', strtotime('NOW')) . CNF_SUFFIX_LOG;
			break;

		case 'D':
			$write_path = PATH_LOG . rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename . '_' . date('Ymd', strtotime('NOW')) . CNF_SUFFIX_LOG;
			break;

		case 'H':
			$write_path = PATH_LOG . rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename . '_' . date('YmdH', strtotime('NOW')) . CNF_SUFFIX_LOG;
			break;

		default:
			$write_path = PATH_LOG . rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename . CNF_SUFFIX_LOG;
			break;
	}

	$debug = debug_backtrace();
	$file  = $debug[0]['file'];
	$line  = $debug[0]['line'];

	$logs    = [];
	$logs[]  = '===========================================' . "\n";
	$logs[]  = 'TIME : ' . date("Y-m-d H:i:s", strtotime('NOW')) . "\n";
	$logs[]  = 'FILE : ' . $file . "\n";
	$logs[]  = 'LINE : ' . $line . "\n";
	$logs[]  = var_export($msg, true) . "\n";
	$logs[]  = '===========================================' . "\n";
	$log_msg = implode("", $logs);

	return @error_log($log_msg . "\n", 3, $write_path);
}

/**
 * 문자열에서 숫자만 추출
 */
function only_num($str)
{
	return preg_replace("/[^0-9]*/s", "", $str);
}

/**
 * 문자열에서 숫자만 추출 후 전화번호 형식으로 변환
 */
function convert_phone($str)
{
	$num = only_num($str);

	return preg_replace("/(0(?:2|[0-9]{2}))([0-9]+)([0-9]{4}$)/", "\\1-\\2-\\3", $num);
}

/**
 * 이미지 리사이즈
 */
function resize_image($file, $w, $h, $crop = false, $degree = 0)
{
	[
		$width,
		$height,
		$image_type,
	] = getimagesize($file);
	$r = $width / $height;

	$src = null;
	if ( $image_type == IMAGETYPE_JPEG ) {
		$src = imagecreatefromjpeg($file);
	}
	elseif ( $image_type == IMAGETYPE_GIF ) {
		$src = imagecreatefromgif($file);
	}
	elseif ( $image_type == IMAGETYPE_PNG ) {
		$src = imagecreatefrompng($file);
	}

	// 자르기
	if ( $crop ) {
		if ( $width > $height ) {
			$width = ceil($width - ($width * abs($r - $w / $h)));
		}
		else {
			$height = ceil($height - ($height * abs($r - $w / $h)));
		}

		$newwidth  = $w;
		$newheight = $h;
	}
	else {
		$newwidth  = $w;
		$newheight = $w / $r;
	}

	$dst = imagecreatetruecolor($newwidth, $newheight);
	imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

	// 회전
	if ( $degree > 0 ) {
		$dst = imagerotate($dst, $degree, 0);
	}

	if ( $image_type == IMAGETYPE_JPEG ) {
		imagejpeg($dst, $file, 100);
	}
	else {
		if ( $image_type == IMAGETYPE_GIF ) {
			imagegif($dst, $file);
		}
		else {
			if ( $image_type == IMAGETYPE_PNG ) {
				imagepng($dst, $file, 9);
			}
		}
	}

	imagedestroy($src);

	return $dst;
}

/**
 * 기간 배열 생성하기
 */
function getPeriod($sdate, $edate, $interval = 'day'): array
{
	try {
		$start = new DateTime($sdate);
		$end   = new DateTime($edate);

		$interval = DateInterval::createFromDateString('1 ' . $interval);
		$period   = new DatePeriod($start, $interval, $end);

		$date = [];
		foreach ( $period as $dt ) {
			$date[] = $dt->format('Y-m-d');
		}
		$date[] = date('Y-m-d', strtotime($edate));

		return $date;
	}
	catch ( Exception $e ) {
		return [];
	}
}

/**
 * 쿠키 셋팅
 */
function set_cookies($key, $val, $expire)
{
	if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ) {
		$cookie_options = [
			'expires'  => $expire,
			'path'     => '/',
			'domain'   => URL_COOKIE,
			'secure'   => true,   // false
			'samesite' => 'None',  // None | Lax | Strict
			'httponly' => true,   // false
		];
		setcookie($key, $val, $cookie_options);
	}
	else {
		setcookie($key, $val, $expire, '/', URL_COOKIE);
	}
}

/**
 * 디버깅
 */
function v()
{
	echo '<xmp class="text-danger">';
	$var = func_get_args();
	call_user_func_array('var_dump', $var);
	echo '</xmp>';
}

function vv()
{
	echo '<xmp class="text-danger">';
	$var = func_get_args();
	call_user_func_array('var_dump', $var);
	echo '</xmp>';
	exit;
}

/* ===================================================================
	미사용 헬퍼
=================================================================== */
/**
 * 글자수 자르기
 */
function cutString($str, $cnt, $tail = '...'): string
{
	if ( mb_strlen($str, 'UTF-8') > $cnt ) {
		return mb_substr($str, 0, $cnt, 'UTF-8') . $tail;
	}
	else {
		return $str;
	}
}

/**
 * xss 필터링
 */
function xss_clean($data)
{
	// Fix &entity\n;
	$data = str_replace([
		'&amp;',
		'&lt;',
		'&gt;',
	], [
		'&amp;amp;',
		'&amp;lt;',
		'&amp;gt;',
	], $data);
	$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
	$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
	$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

	// Remove any attribute starting with "on" or xmlns
	$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

	// Remove javascript: and vbscript: protocols
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

	// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

	// Remove namespaced elements (we do not need them)
	$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

	do {
		$old_data = $data;
		$data     = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
	}
	while ( $old_data !== $data );

	return $data;
}

/**
 * 랜덤 문자열 생성
 */
function generateStr($cnt): string
{
	$char     = [];
	$char[]   = str_repeat('0123456789', 2);
	$char[]   = 'abcdefghijklmnopqrstuvwxyz';
	$char[]   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$char[]   = '_';
	$char_txt = implode('', $char);

	$loop   = $cnt;
	$result = '';
	while ( $loop-- ) {
		$result .= $char_txt[mt_rand(0, strlen($char_txt) - 1)];
	}

	return $result;
}

/**
 * 바이트 사이즈 변환
 */
function sizeConvert($size)
{
	if ( is_numeric($size) === false || $size <= 0 ) {
		return false;
	}

	$unit = [
		'B',
		'KB',
		'MB',
		'GB',
		'TB',
		'PB',
	];

	return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

/**
 * URL 생성
 *
 * @param string $sPath
 * @return string
 */
function getAssetPath(string $sPath): string
{
	$sFileTime = time();

	if ( is_file(FCPATH . $sPath) ) {
		$sFileTime = filemtime(FCPATH . $sPath);
	}

	return $sPath . '?v=' . $sFileTime;
}

/**
 * Create a "Random" String
 *
 * @param string type of random string.  basic, alpha, alnum, numeric, nozero, unique, md5, encrypt and sha1
 * @param int number of characters
 * @return string
 */
function randomString($type = 'alnum', $len = 8): string
{
	$pool= '';

	switch ( $type ) {
		case 'basic':
			return mt_rand();

		case 'unique': // todo: remove in 3.1+
		case 'md5':
			return md5(uniqid(mt_rand()));

		case 'encrypt': // todo: remove in 3.1+
		case 'sha1':
			return sha1(uniqid(mt_rand(), true));

		case 'numeric':
			$pool = '0123456789';
			break;

		case 'nozero':
			$pool = '123456789';
			break;

		case 'alpha':
			$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;

		case 'alnum':
		default:
			$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
	}

	return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
}

/**
 * 조사 설정
 * @param string $text 문자
 * @param string $josa 조사
 */
function getInsertJosa(string $text, string $josa): string
{
    $rtv_josa = '';
    $alpa_arr = [
        'l',
        'm',
        'n',
        'r',
    ];
    $num_arr  = [
        '0',
        '1',
        '3',
        '6',
        '7',
        '8',
    ];

    $last_char     = strtolower($text);
    $last_char     = mb_substr($last_char, -1);
    $last_char     = in_array($last_char, $num_arr, true) ? '받침있음' : $last_char;
    $last_char     = in_array($last_char, $alpa_arr, true) ? '받침있음' : $last_char;
    $last_char     = mb_substr($last_char, -1);
    $linear_char   = linear_hangul($last_char);
    $last_char_arr = mb_str_split($linear_char);

    $jong = false;
    if ( isset($last_char_arr[2]) ) {
        $jong = true;
    }

    if ( $josa == '을' || $josa == '를' ) {
        $rtv_josa = $jong ? '을' : '를';
    }
    if ( $josa == '이' || $josa == '가' ) {
        $rtv_josa = $jong ? '이' : '가';
    }
    if ( $josa == '은' || $josa == '는' ) {
        $rtv_josa = $jong ? '은' : '는';
    }
    if ( $josa == '와' || $josa == '과' ) {
        $rtv_josa = $jong ? '과' : '와';
    }
    if ( $josa == '으로' || $josa == '로' ) {
        $rtv_josa = $jong ? '으로' : '로';
    }

    return $text . $rtv_josa;
}

function linear_hangul($str): string
{
    $cho    = [
        'ㄱ',
        'ㄲ',
        'ㄴ',
        'ㄷ',
        'ㄸ',
        'ㄹ',
        'ㅁ',
        'ㅂ',
        'ㅃ',
        'ㅅ',
        'ㅆ',
        'ㅇ',
        'ㅈ',
        'ㅉ',
        'ㅊ',
        'ㅋ',
        'ㅌ',
        'ㅍ',
        'ㅎ',
    ];
    $jung   = [
        'ㅏ',
        'ㅐ',
        'ㅑ',
        'ㅒ',
        'ㅓ',
        'ㅔ',
        'ㅕ',
        'ㅖ',
        'ㅗ',
        'ㅘ',
        'ㅙ',
        'ㅚ',
        'ㅛ',
        'ㅜ',
        'ㅝ',
        'ㅞ',
        'ㅟ',
        'ㅠ',
        'ㅡ',
        'ㅢ',
        'ㅣ',
    ];
    $jong   = [
        '',
        'ㄱ',
        'ㄲ',
        'ㄳ',
        'ㄴ',
        'ㄵ',
        'ㄶ',
        'ㄷ',
        'ㄹ',
        'ㄺ',
        'ㄻ',
        'ㄼ',
        'ㄽ',
        'ㄾ',
        'ㄿ',
        'ㅀ',
        'ㅁ',
        'ㅂ',
        'ㅄ',
        'ㅅ',
        'ㅆ',
        'ㅇ',
        'ㅈ',
        'ㅊ',
        'ㅋ',
        ' ㅌ',
        'ㅍ',
        'ㅎ',
    ];
    $result = '';

    for ( $i = 0; $i < mb_strlen($str, 'UTF-8'); $i++ ) {
        $code = ord8(mb_substr($str, $i, 1, 'UTF-8')) - 44032;
        if ( $code > -1 && $code < 11172 ) {
            $cho_idx  = $code / 588;
            $jung_idx = $code % 588 / 28;
            $jong_idx = $code % 28;
            $result   .= $cho[$cho_idx] . $jung[$jung_idx] . $jong[$jong_idx];
        }
        else {
            $result .= mb_substr($str, $i, 1, 'UTF-8');
        }
    }

    return $result;
}

/**
 * 한글 초성, 중성, 종성 분리
 */
function ord8($c)
{
    $len = strlen($c);
    if ( $len <= 0 ) {
        return false;
    }
    $h = ord($c[0]);
    if ( $h <= 0x7F ) {
        return $h;
    }
    if ( $h < 0xC2 ) {
        return false;
    }
    if ( $h <= 0xDF && $len > 1 ) {
        return ($h & 0x1F) << 6 | (ord($c[1]) & 0x3F);
    }
    if ( $h <= 0xEF && $len > 2 ) {
        return ($h & 0x0F) << 12 | (ord($c[1]) & 0x3F) << 6 | (ord($c[2]) & 0x3F);
    }
    if ( $h <= 0xF4 && $len > 3 ) {
        return ($h & 0x0F) << 18 | (ord($c[1]) & 0x3F) << 12 | (ord($c[2]) & 0x3F) << 6 | (ord($c[3]) & 0x3F);
    }

    return false;
}

// 오타검사
function word_check($word){
	$word_encode = urlencode($word);
	$url = "https://m.search.naver.com/p/csearch/ocontent/util/SpellerProxy?q={$word_encode}&color_blindness=0";
	$result = file_get_contents($url);
	$result_decode = json_decode($result,true);
	return $result_decode['message']['result']['notag_html'];
}