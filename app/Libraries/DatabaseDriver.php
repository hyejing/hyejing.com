<?php namespace App\Libraries;

/* ===================================================================
	디비 드라이브 라이브러리
=================================================================== */
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\Query;
use Config\Database;

class DatabaseDriver
{
    /**
     * database instance
     */
    protected string $dbiName;
    protected ?BaseConnection $dbi = null;
    protected ?object $builder = null;

    /**
     * build query
     */
    protected ?string $trans_key;
    protected array $aBind = [];
    protected int $num_rows = 0;

    /* ===================================================================
        Connection DB Instance
    =================================================================== */
    /**
     * @param $dbiName
     */
    public function __construct($dbiName)
    {
        $this->dbiName = (empty($dbiName) === true) ? 'main' : $dbiName;
    }

    /**
     * destruct function
     */
    public function __destruct()
    {
        if ( is_object($this->dbi) === false ) {
            return;
        }

        $this->dbi->close();
    }

    /**
     * connection function
     */
    public function conn()
    {
        if ( is_object($this->dbi) === false ) {
            $this->dbi = Database::connect($this->dbiName);
        }
    }

    /**
     * @return void
     */
    public function reconnect()
    {
        if ( is_object($this->dbi) === false ) {
            return;
        }

        $this->dbi->close();
        $this->dbi->reconnect();
    }

    /* ===================================================================
        Set Functions
    =================================================================== */
    /**
     * bind 함수
     *
     * @param $val
     * @return void
     */
    public function setBind($val)
    {
        if ( is_array($val) ) {
            $this->aBind = $val;
        }
        else {
            $this->aBind = [$val];
        }
    }

    /**
     * 코드이그나이터 빌더 함수
     *
     * @param string $table
     * @return BaseBuilder
     */
    public function builder(string $table): BaseBuilder
    {
        $this->conn();

        return $this->dbi->table($table);
    }

    /**
     * 쿼리 실행 함수
     *
     * @param string $q
     * @return bool|BaseResult|Query|mixed
     */
    private function query(string $q)
    {
        $q = '/* ' . $this->getModelFilename() . ' */ ' . $q;
        if ( strpos('update', trim(strtolower($q))) === 0 && strpos('where', strtolower($q)) === false ) {
            $this->setErrorLog();

            return false;
        }
        if ( strpos('delete', trim(strtolower($q))) === 0 && strpos('where', strtolower($q)) === false ) {
            $this->setErrorLog();

            return false;
        }

        $start_time = $this->getMicrotime();
        $this->conn();

        // 쿼리 실행
        if ( !empty($this->aBind) && count($this->aBind) > 0 ) {
            $res = $this->dbi->query($q, $this->aBind);

            $this->aBind = [];
        }
        else {
            $res = $this->dbi->query($q);
        }

        // 쿼리실행시간 체크
        $run_time = $this->getMicrotime() - $start_time;

        // 쿼리 에러
        $aResult = $this->dbi->error();
        if ( $res === false || (isset($aResult['code']) && $aResult['code'] != 0) ) {
            $this->setErrorLog();
        }
        else {
            //write_log('query_exec', $q, 'query_exec');
        }

        // 슬로우쿼리 체크
        if ( $run_time > 1 ) {
            $msg = "[SLOW QUERY : " . round($run_time, 2) . "] " . $q;
            write_log('query_slow', $msg, 'query_slow');
        }

        return $res;
    }

    /* ===================================================================
        Get Functions
    =================================================================== */
    public function getLastQuery(): string
    {
        return 'EXPLAIN ' . $this->dbi->getLastQuery();
    }

    public function getNumRows(): int
    {
        return $this->num_rows;
    }

    /* ===================================================================
        Select
    =================================================================== */
    /**
     * 조회 데이터 가져오기
     *
     * @param        $table
     * @param string $field
     * @param null   $where
     * @return array
     */
    public function get($table, string $field = '*', $where = null): array
    {
        if ( empty($field) === true ) {
            $field = '*';
        }

        $q              = $this->query($this->selectQueryBySQL($table, $field, $where, null, null, 0, 1));
        $this->num_rows = $q->getNumRows();
        $rtv            = $q->getRowArray();
        $q->freeResult();

        if ( is_array($rtv) ) {
            return $rtv;
        }
        else {
            return [];
        }
    }

    /**
     * 조회 리스트 가져오기
     *
     * @param        $table
     * @param string $field
     * @param null   $where
     * @param null   $groupby
     * @param null   $sort
     * @param int    $offset
     * @param int    $lpp
     * @param bool   $type_field
     * @return array
     */
    public function getList($table, string $field = '*', $where = null, $groupby = null, $sort = null, int $offset = 0, int $lpp = 10, bool $type_field = false): array
    {
        if ( empty($field) === true ) {
            $field = '*';
        }

        $q              = $this->query($this->selectQueryBySQL($table, $field, $where, $groupby, $sort, $offset, $lpp));
        $this->num_rows = $q->getNumRows();
        $rtv            = $q->getResultArray();
        $q->freeResult();

        $result = [];
        if ( $type_field === true ) {
            if ( is_array($rtv) === true && count($rtv) > 0 ) {
                foreach ( $rtv as $k => $v ) {
                    foreach ( $v as $vk => $vv ) {
                        $result[$vk][$k] = $vv;
                    }
                }
            }
        }
        else {
            $result = $rtv;
        }

        return $result;
    }

    /**
     * SELECT QUERY 생성 함수
     *
     * @param        $table
     * @param string $field
     * @param        $where
     * @param        $groupby
     * @param        $sort
     * @param int    $offset
     * @param int    $lpp
     * @return string
     */
    private function selectQueryBySQL($table, string $field = '*', $where = null, $groupby = null, $sort = null, int $offset = 0, int $lpp = 10): string
    {
        if ( $field == null || $field == '' ) {
            $field = '*';
        }

        if ( is_array($where) === true ) {
            $where = implode(' AND ', $where);
        }
        $where = $where != null ? ' WHERE ' . $where : null;

        $groupby = ($groupby != null) ? ' GROUP BY ' . $groupby : null;
        $sort    = ($sort != null) ? ' ORDER BY ' . $sort : null;
        $limit   = ($lpp > 0) ? ' LIMIT ' . $offset . ', ' . $lpp : null;

        return 'SELECT ' . $field . ' FROM ' . $table . $where . $groupby . $sort . $limit;
    }

    /* ===================================================================
        Insert
    =================================================================== */
    /**
     * @param string $table
     * @param array  $data
     * @param array  $without
     * @param bool   $bIgnore
     * @return false|int|string
     */
    public function insert(string $table, array $data = [], array $without = [], bool $bIgnore = false)
    {
        if ( empty($data) === true || count($data) <= 0 ) {
            return false;
        }

        // 테이블 선언
        $this->conn();
        $this->builder = $this->dbi->table($table);

        // 데이터 셋팅
        foreach ( $data as $k => $v ) {
            if ( in_array($k, $without) === true ) {
                $this->builder->set($k, $v, false);
            }
            else {
                $this->builder->set($k, $v);
            }
        }

        // 쿼리 생성
        $sQuery = $this->builder->ignore($bIgnore)->getCompiledInsert();
        $this->query($sQuery);

        // return insert id
        return $this->dbi->insertID();
    }

    /**
     * 여러개 입력
     *
     * @param string $table
     * @param array  $data
     * @param bool   $bIgnore
     * @return false|int    입력된 개수 리턴
     */
    public function insertBatch(string $table, array $data = [], bool $bIgnore = false)
    {
        if ( empty($data) === true || count($data) <= 0 ) {
            return false;
        }

        $this->conn();
        $this->builder = $this->dbi->table($table);
        $this->builder->ignore($bIgnore);

        $result = $this->builder->insertBatch($data);
        if ( is_numeric($result) ) {
            $this->num_rows = $result;
        }

        return $this->getNumRows();
    }

    /* ===================================================================
        Update
    =================================================================== */
    /**
     * 데이터 수정
     *
     * @param string $table
     * @param array  $data
     * @param null   $where
     * @param array  $without
     * @return false|int
     */
    public function update(string $table, array $data = [], $where = null, array $without = [])
    {
        if ( $table == null || $table == '' ) {
            return false;
        }
        if ( empty($data) === true || count($data) <= 0 ) {
            return false;
        }
        if ( $where == null || $where == '' ) {
            return false;
        }
        if ( is_array($where) && count($where) <= 0 ) {
            return false;
        }

        // 테이블 선언
        $this->conn();
        $this->builder = $this->dbi->table($table);

        // 데이터 셋팅
        foreach ( $data as $k => $v ) {
            if ( in_array($k, $without) === true ) {
                $this->builder->set($k, $v, false);
            }
            else {
                $this->builder->set($k, $v);
            }
        }

        // 조건절
        $this->builder->where($where);

        // 쿼리 생성
        $sQuery = $this->builder->getCompiledUpdate();
        $this->query($sQuery);

        // return affected rows
        $this->num_rows = $this->dbi->affectedRows();

        return $this->getNumRows();
    }

    /* ===================================================================
        Delete
    =================================================================== */
    /**
     * 데이터 삭제
     *
     * @param string $table
     * @param        $where
     * @return false|int
     */
    public function delete(string $table, $where)
    {
        if ( $table == null || $table == '' ) {
            return false;
        }
        if ( $where == null || $where == '' ) {
            return false;
        }
        if ( is_array($where) && count($where) <= 0 ) {
            return false;
        }

        // 테이블 선언
        $this->conn();
        $this->builder = $this->dbi->table($table);

        // 조건절
        $this->builder->where($where);

        // 쿼리 생성
        $sQuery = $this->builder->getCompiledDelete();
        $this->query($sQuery);

        // return affected rows
        $this->num_rows = $this->dbi->affectedRows();

        return $this->getNumRows();
    }

    /* ===================================================================
        Transactions
    =================================================================== */
    /**
     * 트랜젝션 시작
     */
    public function transBegin($trans_key)
    {
        $this->conn();

        if ( empty($this->trans_key) === true ) {
            $this->trans_key = $trans_key;
            $this->dbi->transBegin();
        }
    }

    /**
     * 트랜젝션 상태
     */
    public function transStatus($trans_key): bool
    {
        if ( $this->trans_key === $trans_key ) {
            return $this->dbi->transStatus();
        }
        else {
            return true;
        }
    }

    /**
     * 트랜젝션 롤백
     */
    public function transRollback($trans_key)
    {
        if ( $this->trans_key === $trans_key ) {
            $this->dbi->transRollback();
            $this->trans_key = null;
        }
    }

    /**
     * 트랜젝션 커밋
     */
    public function transCommit($trans_key)
    {
        if ( $this->trans_key === $trans_key ) {
            $this->dbi->transCommit();
            $this->trans_key = null;
        }
    }

    /* ===================================================================
        Helper Functions
    =================================================================== */
    /**
     * 비정형타입 일반쿼리 실행 1줄 실행
     *
     * @param string $q
     * @return array
     */
    public function getRow(string $q): ?array
    {
        $res = $this->query($q);
        $rtv = $res->getRowArray();
        $res->freeResult();

        if ( is_array($rtv) ) {
            return $rtv;
        }
        else {
            return [];
        }
    }

    /**
     * 비정형타입 일반쿼리 실행 여러줄 실행시
     *
     * @param string $q
     * @return array
     */
    public function getRows(string $q): ?array
    {
        $res = $this->query($q);

        $this->num_rows = $res->getNumRows();
        $rtv            = $res->getResultArray();
        $res->freeResult();

        return $rtv;
    }

    /**
     * 조회 데이터 1개 필드값 바로 가져오기
     *
     * @param        $table
     * @param string $field
     * @param null   $where
     * @return string
     */
    public function getOne($table, string $field = '*', $where = null): string
    {
        if ( empty($field) === true ) {
            $field = '*';
        }

        $q   = $this->query($this->selectQueryBySQL($table, $field, $where, null, null, 0, 1));
        $rtv = $q->getRowArray();
        $q->freeResult();

        if ( is_array($rtv) ) {
            return reset($rtv);
        }
        else {
            return '';
        }
    }

    /**
     * 카운트 가져오기
     *
     * @param      $table
     * @param null $where
     * @return int
     */
    public function getCount($table, $where = null): int
    {
        if ( is_array($where) === true ) {
            $where = implode(' AND ', $where);
        }
        $where = ($where != null) ? ' WHERE ' . $where : null;

        $q   = $this->query('SELECT COUNT(*) AS cnt FROM ' . $table . $where . ' LIMIT 1');
        $rtv = $q->getRow();
        $q->freeResult();

        return (isset($rtv->cnt) && $rtv->cnt > 0) ? (int)$rtv->cnt : 0;
    }

    /**
     * 최대값 가져오기
     *
     * @param        $table
     * @param string $field
     * @param null   $where
     * @return int
     */
    public function getMax($table, string $field, $where = null): int
    {
        if ( is_array($where) === true ) {
            $where = implode(' AND ', $where);
        }
        $where = ($where != null) ? ' WHERE ' . $where : null;

        $q   = $this->query('SELECT MAX(' . $field . ') AS max FROM ' . $table . $where . ' LIMIT 1');
        $rtv = $q->getRow();
        $q->freeResult();

        return $rtv->max ?? 0;
    }

    /**
     * 최소값 가져오기
     *
     * @param        $table
     * @param string $field
     * @param null   $where
     * @return int
     */
    public function getMin($table, string $field, $where = null): int
    {
        if ( is_array($where) ) {
            $where = implode(' AND ', $where);
        }
        $where = ($where != null) ? ' WHERE ' . $where : null;

        $q   = $this->query('SELECT MIN(' . $field . ') AS min FROM ' . $table . $where . ' LIMIT 1');
        $rtv = $q->getRow();
        $q->freeResult();

        return $rtv->min ?? 0;
    }

    /* ===================================================================
        Reference
    =================================================================== */
    /**
     * 마이크로타임값 가져오기
     *
     * @return float
     */
    private function getMicrotime(): float
    {
        [$usec, $sec] = explode(' ', microtime());

        return ((float)$usec + (float)$sec);
    }

    /**
     * 쿼리에러 로그 기록
     *
     * @return void
     */
    private function setErrorLog()
    {
        $sModelFilename = $this->getModelFilename(true);

        $log                  = [];
        $log['FILE']          = trim($sModelFilename);
        $log['ERROR_NUMBER']  = $this->dbi->connID->errno;
        $log['ERROR_MESSAGE'] = $this->dbi->connID->error;
        $log['ERROR_QUERY']   = $this->getLastQuery();
        $log['REGDATE']       = date('Y-m-d H:i:s');
        write_log('query_error', $log, 'db_error');
    }

    /**
     * 실행 모델 파일 가져오기
     *
     * @param bool $isViewAll
     * @return string
     */
    private function getModelFilename(bool $isViewAll = false): string
    {
        $backtrace_ori = debug_backtrace();
        $backtrace_arr = array_reverse($backtrace_ori);

        $file_history = [];
        foreach ( $backtrace_arr as $backtrace ) {
            $history = '';
            if ( strpos($backtrace['file'], 'app/Models') !== false ) {
                if ( $isViewAll === true ) {
                    $history .= "\n\t\t" . 'FILE :: ' . $backtrace['file'];
                    $history .= "\n\t\t" . 'LINE :: ' . $backtrace['line'];

                    if ( isset($backtrace['class']) === true ) {
                        $history .= "\n\t\t" . 'Class :: ' . $backtrace['class'];
                    }
                    if ( isset($backtrace['function']) === true ) {
                        $history .= "\n\t\t" . 'FUNCTION :: ' . $backtrace['function'];
                    }
                }
                else {
                    $file_history = [];
                    $history      = $backtrace['file'];
                    $history      .= ' [' . $backtrace['line'] . ']';
                }
            }

            $file_history[] = $history;
        }
        $file_history = array_unique($file_history);

        if ( $isViewAll === true ) {
            return implode("\n", $file_history);
        }
        else {
            return implode("", $file_history);
        }
    }
}
