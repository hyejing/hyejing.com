<?php namespace App\Models;

/* ===================================================================
	테스트 모델링
----------------------------------------------------------------------
CREATE TABLE `tbl_test` (
  `idx` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'INDEX',
  `vint` INT(10) NOT NULL DEFAULT 0 COMMENT '숫자',
  `vtxt` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '텍스트',
  `vdate` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '날짜',
  PRIMARY KEY (`idx`),
  KEY `ik_vint` (`vint`),
  KEY `ik_vtxt` (`vtxt`),
  KEY `ik_vdate` (`vdate`)
) ENGINE=INNODB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COMMENT='2022-01-15 테스트';

CREATE TABLE `tbl_test2` (
  `idx` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'INDEX',
  `test_idx` BIGINT(20) UNSIGNED NOT NULL COMMENT 'tbl_test.idx',
  `vint` INT(10) NOT NULL DEFAULT 0 COMMENT '숫자',
  `vtxt` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '텍스트',
  `vdate` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '날짜',
  PRIMARY KEY (`idx`),
  KEY `test_idx` (`test_idx`),
  KEY `ik_vint` (`vint`),
  KEY `ik_vtxt` (`vtxt`),
  KEY `ik_vdate` (`vdate`)
) ENGINE=INNODB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='2022-01-15 테스트';
=================================================================== */
use App\Libraries\RedisDriver;
use Exception;

class TestModel extends MyModel
{
    protected RedisDriver $redis;

    public function __construct()
    {
        parent::__construct();

        $this->redis = new RedisDriver();
    }

    public function test()
    {
        //$this->insert1();exit;// 멀티 insert test
        //$this->insert2();exit;// two tables inserts test

        //$this->update1();exit;// update test

        //$this->delete1();exit;// delete test

        //$this->select1();exit;// select row test
        //$this->select2();exit;// select rows test
        //$this->select3();exit;// getOne test
        //$this->select4();exit;// getCount/getMin/getMax test
        //$this->select5();exit;// getRows test
        //$this->select6();exit;// getRow test

        $this->redis1();
    }

    public function redis1()
    {
        $aData = [
            'prefix'  => 'test',
            'key'     => 'rediskey',
            'expires' => 60,
            'data'    => ['test'],
        ];
        $this->oRedis->setRedis($aData);
        v($this->oRedisReader->getRedis('test', 'rediskey')[0]);
    }

    public function select1()
    {
        // select 테스트
        $where     = [];
        $where[]   = "t.idx = :idx:";
        $where[]   = "t.vint >= :vint:";
        $table     = 'tbl_test AS t LEFT JOIN tbl_test2 AS t2 ON t.idx=t2.test_idx';
        $aBindData = [
            'idx'  => 13,
            'vint' => 1,
        ];
        $this->dbs->setBind($aBindData);
        $row = $this->dbs->get($table, 't.vtxt', $where);
        v($this->dbs->getNumRows(), $this->dbs->getLastQuery(), $row);

        // 쿼리 빌드로 처리할 경우
        $builder = $this->dbs->builder('tbl_test as t');
        $builder->join('tbl_test2 as t2', "t.idx=t2.test_idx", 'left');
        $builder->select('t.vtxt');
        $builder->where('t.idx', 13);
        $builder->where('t.vint >=', 1);
        $query = $builder->get();
        v($query->getNumRows(), $this->dbs->getLastQuery(), $query->getRowArray());

        // select 테스트
        $where     = [];
        $where[]   = "t.idx = :idx:";
        $where[]   = "t.vint >= :vint:";
        $table     = 'tbl_test AS t LEFT JOIN tbl_test2 AS t2 ON t.idx=t2.test_idx';
        $aBindData = [
            'idx'  => 13,
            'vint' => 1,
        ];
        $this->dbs->setBind($aBindData);
        $row = $this->dbs->get($table, 't.vtxt', $where);
        v($this->dbs->getNumRows(), $this->dbs->getLastQuery(), $row);

        // select 테스트
        $where     = [];
        $where[]   = "t.idx = :idx: AND t.vint >= :vint:";
        $where[]   = "t.vtxt = :vtxt:";
        $aBindData = [
            'idx'  => 13,
            'vint' => 1,
            'vtxt' => '--;"\'select * from sys_cron;as13323f',
        ];
        $this->dbs->setBind($aBindData);
        $table = 'tbl_test AS t LEFT JOIN tbl_test2 AS t2 ON t.idx=t2.test_idx';
        $row   = $this->dbs->get($table, 't.vtxt', $where);
        v($this->dbs->getNumRows(), $this->dbs->getLastQuery(), $row);
    }

    public function select2()
    {
        // select 테스트
        $filed     = "date_format(`vdate`, '%Y-%m-%d'), count(*) as cnt ";
        $where     = "vint >= :vint:";
        $groupby   = "date_format(`vdate`, '%Y-%m-%d') HAVING cnt > 0";
        $orderby   = "vdate asc";
        $aBindData = [
            'vint' => 1,
        ];
        $this->dbs->setBind($aBindData);
        $rows = $this->dbs->getList('tbl_test', $filed, $where, $groupby, $orderby, 0, 20);
        v($this->dbs->getNumRows(), $this->dbs->getLastQuery(), $rows);
    }

    public function select3()
    {
        // select field 1개의 값 가져오기 테스트
        $where     = [];
        $where[]   = "idx = :idx:";
        $where[]   = "vint >= :vint:";
        $aBindData = [
            'idx'  => 19,
            'vint' => 0,
        ];
        $this->dbs->setBind($aBindData);
        $result = $this->dbs->getOne('tbl_test', 'vtxt', $where);
        v($this->dbs->getLastQuery(), $result);
    }

    public function select4()
    {
        $where   = [];
        $where[] = "vint >= :vint:";
        $this->dbs->setBind(['vint' => 1]);
        $result1 = $this->dbs->getCount('tbl_test', $where);

        $this->dbs->setBind(['vint' => 1]);
        $result2 = $this->dbs->getMin('tbl_test', 'vint', $where);

        $this->dbs->setBind(['vint' => 1]);
        $result3 = $this->dbs->getMax('tbl_test', 'vint', $where);
        v($this->dbs->getLastQuery(), $result1, $result2, $result3);
    }

    public function select5()
    {
        // 비정형타입 일반쿼리 실행 여러 데이터 구할 때
        $query = "SELECT `vtxt` FROM `tbl_test` WHERE `vint` >= :vint:";
        $this->dbs->setBind(['vint' => 1]);
        $rows = $this->dbs->getRows($query);
        v($this->dbs->getNumRows(), $this->dbs->getLastQuery(), $rows);
    }

    public function select6()
    {
        // 비정형타입 일반쿼리 실행 1 row 구할 때
        $query = "SELECT `vtxt` FROM `tbl_test` WHERE `idx` = :idx: AND `vint` >= :vint:";
        $this->dbs->setBind(['idx' => 5, 'vint' => 0]);
        $rows = $this->dbs->getRow($query);
        v($this->dbs->getNumRows(), $this->dbs->getLastQuery(), $rows);
    }

    /* ===================================================================
        insert test
    =================================================================== */
    public function insert1()
    {
        // 멀티 insert 테스트
        $data = [
            ['vint' => 'My title', 'vtxt' => 'My Name', 'vdate' => TIME_NOW],
            ['vint' => 'Another title', 'vtxt' => 'Another Name', 'vdate' => TIME_NOW],
            ['vint' => 'Another title2', 'vtxt' => 'Another Name2', 'vdate' => TIME_NOW],
            ['vint' => 'Another title3', 'vtxt' => 'Another Name2', 'vdate' => TIME_NOW],
            ['vint' => 'Another title4', 'vtxt' => 'Another Name2', 'vdate' => TIME_NOW],
            ['vint' => 'Another title5', 'vtxt' => 'Another Name2', 'vdate' => TIME_NOW],
        ];

        $q = $this->dbm->insertBatch('tbl_test', $data);
        v($q, $this->dbm->getLastQuery());
    }

    public function insert2()
    {
        $this->dbm->transBegin('test2');

        // insert 테스트
        $data          = [];
        $data['vint']  = "(SELECT max(t.idx) from tbl_test as t) + 1";
        $data['vtxt']  = '--;"\'select * from sys_cron;as13323f';
        $data['vdate'] = TIME_NOW;

        $q = $this->dbm->insert('tbl_test', $data, ['vint'], true);
        v($q, $this->dbm->getLastQuery());

        $data2             = [];
        $data2['test_idx'] = $q;
        $data2['vint']     = "(SELECT max(t.idx) from tbl_test2 as t) + 1";
        $data2['vtxt']     = '--;#!"\'SELECT * FROM ';
        $data2['vdate']    = TIME_NOW;
        $q2                = $this->dbm->insert('tbl_test2', $data2, ['vint'], true);
        v($q2, $this->dbm->getLastQuery());

        if ( $this->dbm->transStatus('test2') ) {
            $this->dbm->transCommit('test2');
            v('transCommit');
        }
        else {
            $this->dbm->transRollback('test2');
            v('transRollback');
        }
    }

    /* ===================================================================
        update test
    =================================================================== */
    public function update1()
    {
        // update 테스트
        $data         = [];
        $data['vint'] = 'vint + :vint:';
        $data['vtxt'] = ':vtxt:';
        $where        = "idx = :idx:";
        $aBindData    = [
            'idx'  => 19,
            'vint' => 5,
            'vtxt' => TIME_NOW,
        ];
        $this->dbm->setBind($aBindData);
        $q = $this->dbm->update('tbl_test', $data, $where, ['vint', 'vtxt']);
        v($q, $this->dbm->getLastQuery());
    }

    /* ===================================================================
        delete test
    =================================================================== */
    public function delete1()
    {
        // delete 테스트
        $where = "idx = :idx:";
        $this->dbm->setBind(['idx' => 2]);
        $q = $this->dbm->delete('tbl_test', $where);
        v($q, $this->dbm->getLastQuery());
    }
}
