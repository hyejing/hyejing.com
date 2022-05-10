<?php namespace App\Models\Board;

use App\Models\Board\BoardModel;
use App\Libraries\S3;
use Exception;

class FaqModel extends BoardModel
{
    protected array $aSearchType;
    protected array $aCategoryList;

    protected int $idx;
    protected int $admin_idx;
    protected int $category;
    protected int $sort;
    protected string $title;
    protected string $contents;
    protected string $reg_date;

    protected S3 $s3;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTableName('tbl_board_faq');
        $this->init();

        $this->s3 = new S3();
    }

    /* ===================================================================
        Set Functions
    =================================================================== */
    protected function init()
    {
        $this->aSearchType = [
            'title' => '제목',
        ];

        $this->aCategoryList = [
            '1' => '창작자',
            '2' => '시청자',
            '3' => '지갑',
            '4' => '기타',
        ];

    }

    public function setIdx(int $iIdx)
    {
        if ( $iIdx > 0 ) {
            $this->idx = $iIdx;
        }
        else {
            $this->idx = 0;
        }
    }

    // 작성자 IDX
    public function setAdminIdx($admin_idx)
    {
        if ( $admin_idx > 0 ) {
            $this->admin_idx = $admin_idx;
        }
        else {
            $this->admin_idx = 0;
        }
    }

    // 카테고리
    public function setCategory($category)
    {
        if ( $category > 0 ) {
            $this->category = $category;
        }
        else {
            $this->category = 0;
        }
    }

    /**
     * 제목
     *
     * @throws Exception
     */
    public function setTitle($title)
    {
        if ( empty($title) === false ) {
            $this->title = $title;
        }
        else {
            throw new Exception('제목을 입력해 주세요.');
        }
    }

    /**
     * 내용
     *
     * @throws Exception
     */
    public function setContents($contents)
    {
        if ( empty($contents) === false ) {
            $this->contents = $contents;
        }
        else {
            throw new Exception('내용을 입력해 주세요.');
        }
    }

    // 정렬
    public function setSort($sort)
    {
        if ( $sort > 0 ) {
            $this->sort = $sort;
        }
    }

    /* ===================================================================
        Get Functions
    =================================================================== */
    public function getIdx(): int
    {
        return $this->idx ?? 0;
    }

    public function getAdminIdx(): int
    {
        return $this->admin_idx;
    }

    public function getCategory(): int
    {
        return $this->category;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function getCategoryList(): array
    {
        return $this->aCategoryList;
    }

    public function getSearchType(): array
    {
        return $this->aSearchType;
    }


    /* ===================================================================
        Modules
    =================================================================== */
    /**
     * FAQ 입력 - editer사용
     *
     * @_ckeditor4_add
     * @throws Exception
     */
    public function registFaqModule(): int
    {
        // ID값 없으면 입력진행 안함
        if ( empty($this->getAdminIdx()) === true ) {
            throw new Exception('입력한 관리자회원 정보가 없습니다.');
        }

        $aRes     = [];
        $transKey = "faq_regist";                         // 트랜잭션 키
        $this->dbm->transBegin($transKey);                // 트랜잭션 시작

        //faq 입력처리
        $aRes[] = $iIdx = $this->registFaq();

        //내용에 이미지 중 s3를 통해 직접 첨부한 이미지 여부 확인 시작
        $aImgSrc    = [];
        $bChkAddImg = false; //동작 설정값
        if ( true ) {
            preg_match_all("/<IMG[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i", stripslashes($this->contents), $matches);//$matches =>[0]:이미지태그 , [1]:이미지주소
            $aImgSrc = $matches[1];

            if ( count($aImgSrc) > 0 ) { // 조건확인
                for ( $i = 0; $i < count($aImgSrc); $i++ ) {
                    $aTmpImgUrl = explode("/", $aImgSrc[$i]);
                    if ( $aTmpImgUrl[2] == URL_IMG && $aTmpImgUrl[3] == S3_TMP_UPLOAD_FOLDER ) {//에디터 업로드를 통해 입력된 파일이라면..
                        $bChkAddImg = true;
                        break;
                    }
                }

                if ( $bChkAddImg === true ) { // 실제 이미지 이동은 트랜젝션 commit 이후 진행
                    $aUploadFolder = explode("_", $this->sTableName);
                    $sUploadFolder = end($aUploadFolder);// 저장될 폴더는 테이블 이름에서 설정
                    if ( empty($sUploadFolder) === true ) {
                        $sUploadFolder = 'faq';
                    }
                    $sNewUrlPath = S3_UPLOAD_FOLDER . '/' . $sUploadFolder . '/' . $iIdx;           // 실제 저장시 이동될 S3 경로

                    //글 내용에서 이미지경로 바꾸기 ()
                    $reContents = str_replace(S3_TMP_UPLOAD_FOLDER, $sNewUrlPath, $this->contents); // 내용에서 치환

                    $this->setIdx($iIdx);
                    $this->setContents($reContents);// 새로 set
                    $aRes[] = $this->modifyFaqContents();
                }
            }
        }
        //내용에 이미지 중 s3를 통해 직접 첨부한 이미지 여부 확인 끝

        if ( in_array(false, $aRes) == false && $this->dbm->transStatus($transKey) ) {
            $this->dbm->transCommit($transKey); // 트랜잭션 커밋

            // 에디터를 통해 첨부한 이미지 S3로 이동처리
            if ( count($aImgSrc) > 0 && $bChkAddImg === true ) {
                $this->copyS3ImgFile($aImgSrc, $sNewUrlPath);
            }

            return $iIdx;
        }
        else {
            $this->dbm->transRollback($transKey);   // 트랜잭션 롤백

            return false;
        }
    }

    /**
     * FAQ 수정 - editer사용
     *
     * @_ckeditor4_add
     * @throws Exception
     */
    public function modifyFaqModule(): int
    {
        // idx값 없으면 입력진행 안함
        if ( empty($this->getIdx()) === true ) {
            throw new Exception('수정할 인덱스가 없습니다.');
        }

        $aRes     = [];
        $transKey = "faq_modify";                              // 트랜잭션 키
        $this->dbm->transBegin($transKey);                     // 트랜잭션 시작

        //내용에 이미지 중 s3를 통해 직접 첨부한 이미지 여부 확인 시작
        $aImgSrc    = [];
        $bChkAddImg = false;// 동작 설정값
        if ( true ) {
            preg_match_all("/<IMG[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i", stripslashes($this->contents), $matches);//$matches =>[0]:이미지태그 , [1]:이미지주소
            $aImgSrc = $matches[1];

            // 에디터에 신규 첨부된 이미지가 있는 경우                                                                                               //동작 설정값
            if ( count($aImgSrc) > 0 ) {
                for ( $i = 0; $i < count($aImgSrc); $i++ ) {
                    $aTmpImgUrl = explode("/", $aImgSrc[$i]);

                    if ( $aTmpImgUrl[2] == URL_IMG && $aTmpImgUrl[3] == S3_TMP_UPLOAD_FOLDER ) {//에디터 업로드를 통해 입력된 파일이라면..
                        $bChkAddImg = true;
                        break;
                    }
                }

                if ( $bChkAddImg === true ) { // 실제 이미지 이동은 트랜젝션 commit 이후 진행
                    $aUploadFolder = explode("_", $this->sTableName);
                    $sUploadFolder = end($aUploadFolder);// 저장될 폴더는 테이블 이름에서 설정
                    if ( empty($sUploadFolder) === true ) {
                        $sUploadFolder = 'faq';
                    }
                    $sNewUrlPath = S3_UPLOAD_FOLDER . '/' . $sUploadFolder . '/' . $this->idx;      // 실제 저장시 이동될 S3 경로

                    //글 내용에서 이미지경로 바꾸기 ()
                    $reContents = str_replace(S3_TMP_UPLOAD_FOLDER, $sNewUrlPath, $this->contents); // 내용에서 치환

                    $this->setContents($reContents);// 새로 set
                }
            }
        }
        //내용에 이미지 중 s3를 통해 직접 첨부한 이미지 여부 확인 끝

        $iReturn = $this->modifyFaq();

        if ( in_array(false, $aRes) == false && $this->dbm->transStatus($transKey) ) {
            $this->dbm->transCommit($transKey); // 트랜잭션 커밋

            // 에디터를 통해 첨부한 이미지 S3로 이동처리
            if ( count($aImgSrc) > 0 && $bChkAddImg === true ) {
                $this->copyS3ImgFile($aImgSrc, $sNewUrlPath);
            }

            //삭제 테스트
            //if (false) {
            //$this->setBoardIdx($this->idx);
            //$this->remove();
            //}

            return $iReturn;
        }
        else {
            $this->dbm->transRollback($transKey);   // 트랜잭션 롤백

            return false;
        }
    }


    /* ===================================================================
        Sub Functions
    =================================================================== */
    /* --------------------------   Select   -------------------------- */
    /**
     * 리스트 카운트
     *
     * @param $where
     * @return int
     */
    public function getTotalCount($where): int
    {
        $this->dbs->setBind($this->aBind);

        return $this->dbs->getCount("`tbl_board_faq` AS `tbofa`", $where);
    }

    /**
     * FAQ 리스트
     *
     * @param     $where
     * @param     $orderby
     * @param int $offset
     * @param int $lpp
     * @return array
     */
    public function getList($where, $orderby, int $offset = 0, int $lpp = 20): array
    {
        $field = "`tbofa`.*";
        $table = "`tbl_board_faq` AS `tbofa`";

        $this->dbs->setBind($this->aBind);
        $lists = $this->dbs->getList($table, $field, $where, null, $orderby, $offset, $lpp);

        $result = [];
        if ( empty($lists) === false && count($lists) > 0 ) {
            foreach ( $lists as $k => $v ) {
                $result[$k] = $this->_remakeInfo($v);
            }
        }

        return $result;
    }

    /**
     *  내용 조회 by idx
     *
     * @return array
     */
    public function getInfo(): array
    {
        $this->dbs->setBind(['idx' => $this->idx]);
        $result = $this->dbs->get("`tbl_board_faq` AS `tbofa`", "`tbofa`.*", "`tbofa`.`idx`=:idx:");

        return $this->_remakeInfo($result);
    }

    /**
     *  카테고리 sort max 값 가져오기
     */
    public function getCategorySortMax(): int
    {
        $this->dbs->setBind(['category' => $this->category]);

        return $this->dbs->getMax("`tbl_board_faq` AS `tbofa`", "sort", "`tbofa`.`category`=:category:");
    }

    /**
     * FAQ 추가 정보
     */
    protected function _remakeInfo($result)
    {
        $result['category_txt'] = '';
        $result['state_txt']    = '';

        if ( isset($result['idx']) === true ) {
            if ( isset($result['category']) && isset($this->aCategoryList[$result['category']]) ) {
                $result['category_txt'] = $this->aCategoryList[$result['category']];
            }
            if ( isset($result['state']) && isset($this->aStatus[$result['state']]) ) {
                $result['state_txt'] = $this->aStatus[$result['state']];
            }
        }

        return $result;
    }

    /**
     * 입력 및 수정시 S3 이미지파일 COPY 처리 - @_ckeditor4_add
     *
     * @param array  $aImgSrc     S3 tmp에서 copy 처리할 이미지파일 배열
     * @param string $sNewUrlPath S3에 저장된 파일 경로
     * @return bool
     */
    protected function copyS3ImgFile($aImgSrc, $sNewUrlPath): bool
    {
        if ( count($aImgSrc) > 0 && empty($sNewUrlPath) === false ) {
            for ( $i = 0; $i < count($aImgSrc); $i++ ) {
                $sS3TempFilePath = '';
                $sS3CopyFilePath = '';
                $sS3FileName     = '';

                $aTmpImgUrl = explode("/", $aImgSrc[$i]);

                if ( $aTmpImgUrl[2] == URL_IMG && $aTmpImgUrl[3] == S3_TMP_UPLOAD_FOLDER ) {//에디터 업로드를 통해 입력된 파일이라면..

                    $sS3TempFilePath = $aTmpImgUrl[3];//복사될 파일의 경로
                    $sS3CopyFilePath = $sNewUrlPath;  //새롭게 저장될 파일의 경로
                    $sS3FileName     = $aTmpImgUrl[4];// 복사대상 파일명

                    $this->s3->copy($sS3FileName, $sS3TempFilePath, $sS3CopyFilePath);// 파일 S3 COPY 처리
                }
            }
        }

        return true;
    }

    /* --------------------------   Insert   -------------------------- */
    /**
     * FAQ insert
     *
     * @return int
     */
    public function registFaq(): int
    {
        $ins              = [];
        $ins['admin_idx'] = $this->admin_idx;
        $ins['category']  = $this->category;
        $ins['title']     = $this->title;
        $ins['contents']  = $this->contents;
        $ins['sort']      = $this->sort;
        $ins['state']     = $this->state;
        $ins['reg_date']  = TIME_NOW;

        return $this->dbm->insert('tbl_board_faq', $ins);
    }

    /* --------------------------   Update   -------------------------- */
    /**
     * FAQ sort 수정
     */
    public function modifySort(): bool
    {
        $upd         = [];
        $upd['sort'] = $this->sort;

        // set bind
        $aBindData = [
            'idx' => $this->idx,
        ];

        $this->dbm->setBind($aBindData);
        $rtv = $this->dbm->update('tbl_board_faq', $upd, "`idx`=:idx: ");

        return $rtv !== false;
    }

    /**
     * FAQ 정보 수정
     *
     * @return int
     */
    public function modifyFaq(): ?int
    {
        $upd              = array();
        $upd['admin_idx'] = $this->admin_idx;
        $upd['category']  = $this->category;
        $upd['title']     = $this->title;
        $upd['contents']  = $this->contents;
        $upd['state']     = $this->state;

        if ( !empty($this->sort) ) {
            $upd['sort'] = $this->sort;
        }

        $aBindData = [
            'idx' => $this->idx,
        ];
        $this->dbm->setBind($aBindData);

        return $this->dbm->update('tbl_board_faq', $upd, "`idx` = :idx:");
    }

    /**
     * FAQ 내용 수정
     *
     * @_ckeditor4_add
     * @return int
     */
    public function modifyFaqContents(): ?int
    {
        $upd             = array();
        $upd['contents'] = $this->contents;

        $aBindData = [
            'idx' => $this->idx,
        ];
        $this->dbm->setBind($aBindData);

        return $this->dbm->update('tbl_board_faq', $upd, "`idx` = :idx:");
    }

}