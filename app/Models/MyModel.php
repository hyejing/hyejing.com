<?php namespace App\Models;

/* ===================================================================
	모델 확장
=================================================================== */
use App\Libraries\DatabaseDriver;
use App\Libraries\RedisDriver;
use Config\Common;
use Config\Services;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Model;
use CodeIgniter\Validation\Validation;

class MyModel extends Model
{
    protected Common $common;
    protected DatabaseDriver $dbm;
    protected DatabaseDriver $dbs;
    protected IncomingRequest $request;
    protected Validation $ci_valid;

    protected array $result_data;
    protected array $aBind = [];

    public function __construct()
    {
        parent::__construct();

        $this->common   = new Common();
        $this->request  = Services::request();
        $this->ci_valid = Services::validation();

        $this->dbm = $this->dbc('master');
        $this->dbs = $this->dbc('slave');
    }

    /* ===================================================================
        Set Functions
    =================================================================== */
    public function setResultData($data)
    {
        if ( is_array($data) === true ) {
            $this->result_data = $data;
        }
    }

    public function addBindData(array $aBindData)
    {
        if ( empty($this->aBind) === false ) {
            $this->aBind = array_merge($this->aBind, $aBindData);
        }
        else {
            $this->aBind = $aBindData;
        }
    }

    /* ===================================================================
        Get Functions
    =================================================================== */
    public function getIp(): string
    {
        $sGetIp = self::getIpCheck();

        if ( strpos(trim($sGetIp), ',') ) {
            $aWafIps = explode(',', $sGetIp);
            $sGetIp  = $aWafIps[0]; // default
        }

        return $sGetIp;
    }

    public function getAgent(): UserAgent
    {
        return $this->request->getUserAgent();
    }

    public function getResultData(): array
    {
        return $this->result_data;
    }

    /* ===================================================================
        Modules
    =================================================================== */
    /**
     * Return Database Connection Instance
     */
    public function dbc($server_name = 'main'): DatabaseDriver
    {
        return new DatabaseDriver($server_name);
    }

    /**
     * Paging
     */
    public function paginator($page = 1, $total_count = 0, $records = 20, $blocks = 5, $param = []): array
    {
        $is_view  = 'false';
        $is_first = 'false';
        $is_last  = 'false';
        $is_prev  = 'false';
        $is_next  = 'false';

        $total_page  = ceil($total_count / $records);
        $total_block = ceil($total_page / $blocks);
        $now_block   = ceil($page / $blocks);
        $start_page  = ($now_block - 1) * $blocks + 1;
        $end_page    = $start_page + $blocks - 1;

        if ( $end_page > $total_page ) {
            $end_page = $total_page;
        }
        if ( $page != 1 ) {
            $is_first = 'true';
        }
        if ( $now_block < $total_block ) {
            $is_next = 'true';
        }
        if ( $now_block > 1 ) {
            $is_prev = 'true';
        }
        if ( $page < $total_page ) {
            $is_last = 'true';
        }

        $pages = [];
        if ( $end_page > 1 ) {
            for ( $i = $start_page; $i <= $end_page; $i++ ) {
                $pages[$i] = $i;
            }
            $is_view = 'true';
        }

        $result['type']        = 'link';
        $result['view']        = $is_view;
        $result['first']       = $is_first;
        $result['first_page']  = 1;
        $result['prev']        = $is_prev;
        $result['prev_page']   = $start_page - 1;
        $result['now_page']    = $page;
        $result['pages']       = $pages;
        $result['next']        = $is_next;
        $result['next_page']   = $start_page + $blocks;
        $result['last']        = $is_last;
        $result['last_page']   = $total_page;
        $result['current_url'] = current_url();
        unset($param['page']);
        $result['link'] = '?' . http_build_query($param);

        return $result;
    }

    /**
     * Paging By JS Function
     */
    public function paginator_func($page = 1, $total_count = 0, $records = 20, $blocks = 5, $function_name = ''): array
    {
        $is_view  = 'false';
        $is_first = 'false';
        $is_last  = 'false';
        $is_prev  = 'false';
        $is_next  = 'false';

        $total_page  = ceil($total_count / $records);
        $total_block = ceil($total_page / $blocks);
        $now_block   = ceil($page / $blocks);
        $start_page  = ($now_block - 1) * $blocks + 1;
        $end_page    = $start_page + $blocks - 1;

        if ( $end_page > $total_page ) {
            $end_page = $total_page;
        }
        if ( $page != 1 ) {
            $is_first = 'true';
        }
        if ( $now_block < $total_block ) {
            $is_next = 'true';
        }
        if ( $now_block > 1 ) {
            $is_prev = 'true';
        }
        if ( $page < $total_page ) {
            $is_last = 'true';
        }

        $pages = [];
        if ( $end_page > 1 ) {
            for ( $i = $start_page; $i <= $end_page; $i++ ) {
                $pages[$i] = $i;
            }
            $is_view = 'true';
        }

        $result['type']       = 'func';
        $result['view']       = $is_view;
        $result['first']      = $is_first;
        $result['first_page'] = 1;
        $result['prev']       = $is_prev;
        $result['prev_page']  = $start_page - 1;
        $result['now_page']   = $page;
        $result['pages']      = $pages;
        $result['next']       = $is_next;
        $result['next_page']  = $start_page + $blocks;
        $result['last']       = $is_last;
        $result['last_page']  = $total_page;
        $result['func_name']  = $function_name;

        return $result;
    }

    /* ===================================================================
        Functions
    =================================================================== */
    /**
     * @return string
     */
    private static function getIpCheck(): string
    {
        $sIpAddress = '';

        if ( isset($_SERVER['HTTP_CLIENT_IP']) === true ) {
            $sIpAddress = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) === true ) {
            $sIpAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        elseif ( isset($_SERVER['HTTP_X_FORWARDED']) === true ) {
            $sIpAddress = $_SERVER['HTTP_X_FORWARDED'];
        }
        elseif ( isset($_SERVER['HTTP_FORWARDED_FOR']) === true ) {
            $sIpAddress = $_SERVER['HTTP_FORWARDED_FOR'];
        }
        elseif ( isset($_SERVER['HTTP_FORWARDED']) === true ) {
            $sIpAddress = $_SERVER['HTTP_FORWARDED'];
        }

        return $sIpAddress;
    }
}
