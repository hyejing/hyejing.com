<?php use App\Models\UserModel;

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
 *  'hyejing.com' 도메인 fix해버렸음..
 */
function set_cookies($key, $val, $expire = 0)
{
    if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ) {
        $cookie_options = [
            'expires'  => $expire,
            'path'     => '/',
            //'domain'   => URL_COOKIE,
            'domain'   => 'hyejing.com',
            'secure'   => true,   // false
            'samesite' => 'None',  // None | Lax | Strict
            'httponly' => true,   // false
        ];
        setcookie($key, $val, $cookie_options);
    }
    else {
        //setcookie($key, $val, $expire, '/', URL_COOKIE);
        setcookie($key, $val, $expire, '/', 'hyejing.com');
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
    $pool = '';

    switch ( $type ) {
        case 'basic':
            return mt_rand();

        case 'unique': // remove in 3.1+
        case 'md5':
            return md5(uniqid(mt_rand()));

        case 'encrypt': // remove in 3.1+
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
 * 텔레그램 푸쉬 API
 * https://api.telegram.org/bot1989561465:AAF8KLK9ni-qUOuOMZZkh0_NeI7XOSHkT4g/getUpdates
 */
function push_alarm($msg = '', $tg_id = 'ALL'): bool
{
    if ( empty($msg) === true ) {
        return false;
    }

    // 메시지 정제
    if ( is_array($msg) === true ) {
        $msg_txts = [];
        foreach ( $msg as $k => $v ) {
            if ( is_numeric($k) === false ) {
                $msg_txts[] = '[' . $k . '] : ' . var_export($v, true);
            }
            else {
                $msg_txts[] = $v;
            }
        }
        $msg = @implode("\n", $msg_txts);
    }

    // 텔레그램 발송 대상 아이디 가져오기
    $config   = config('Common');
    $aTgAdmin = $config->aTgAdmin;

    if ( empty($tg_id) === false ) {
        // 한명만 발송
        foreach ( $aTgAdmin as $k => $iTgAdmin ) {
            if ( $tg_id == $k ) {
                // 푸시발송
                $parameters = [
                    'chat_id' => $iTgAdmin,
                    'text'    => mb_substr($msg, 0, 3700),
                ];

                $url = 'https://api.telegram.org/bot' . TG_TOKEN . '/sendMessage?' . http_build_query($parameters);
                shell_exec('php ' . PATH_PUBLIC . 'index.php tg send_cli "' . urlencode(urlencode($url)) . '" > /dev/null 2>/dev/null &');
            }
        }
    }
    else {
        // 전체 발송
        foreach ( $aTgAdmin as $k => $iTgAdmin ) {
            if ( $k == 'ALL' ) {
                // 푸시발송
                $parameters = [
                    'chat_id' => $iTgAdmin,
                    'text'    => mb_substr($msg, 0, 3700),
                ];

                $url = 'https://api.telegram.org/bot' . TG_TOKEN . '/sendMessage?' . http_build_query($parameters);
                //shell_exec('php ' . PATH_PUBLIC . 'index.php tg send_cli "' . urlencode(urlencode($url)) . '" > /dev/null 2>/dev/null &');
                $result = shell_exec('php ' . PATH_PUBLIC . 'index.php tg send_cli "' . urlencode(urlencode($url)) . '" ');
                v($result);
            }
        }
    }

    return true;
}

/**
 * 언어 함수 확장
 */
function langs($key, $args = [], $locale = null)
{
    $text = lang($key, $args, $locale);
    if ( $text == $key ) {
        $request = Config\Services::request();
        $text    = lang($key, $args, $request->config->defaultLocale);
    }

    return $text;
}