<?php namespace App\Models\Board;

use App\Libraries\S3;
use App\Models\MyModel;
use Exception;

class BoardModel extends MyModel
{
    // 허용할 테이블 리스트
    protected array $aTableName = ['tbl_board_faq', 'tbl_board_notice'];
    protected array $aStatus = ['0' => '숨김', '1' => '출력'];

    protected int $iBoardIdx;       // 게시물 번호
    protected int $state;
    protected string $sTableName;   // 자식 테이블명
    // protected S3 $s3;

    public function __construct()
    {
        parent::__construct();
    }

    /* ===================================================================
        Set Functions
    =================================================================== */
    /**
     * @throws Exception
     */
    public function setBoardIdx(int $iIdx)
    {
        if ( $iIdx > 0 ) {
            $this->iBoardIdx = $iIdx;
        }
        else {
            throw new Exception('게시물 번호를 정확히 입력해주세요.');
        }
    }

    /**
     * 상태값
     *
     * @throws Exception
     */
    public function setState($state)
    {
        if ( array_key_exists($state, $this->aStatus) === true ) {
            $this->state = $state;
        }
        else {
            throw new Exception('유효하지 않은 상태 정보입니다.');
        }
    }

    /**
     * 테이블명 설정
     *
     * @throws Exception
     */
    public function setTableName($sTableName)
    {
        if ( empty($sTableName) === false && in_array($sTableName, $this->aTableName) === true ) {
            $this->sTableName = $sTableName;
        }
        else {
            throw new Exception('접근 에러입니다.');
        }
    }

    /* ===================================================================
        Get Functions
    =================================================================== */
    public function getState(): int
    {
        return $this->state;
    }

    public function getStatusList(): array
    {
        return $this->aStatus;
    }

    /* ===================================================================
        Sub Functions
    =================================================================== */
    /* --------------------------   Delete   -------------------------- */

    /**
     * board 관련 삭제 - 현재 삭제는 상태값 변경만 진행됨으로 호출되지 않음
     * 삭제시 해당 테이블 및 idx 이용하여 s3 이미지 삭제 처리 추가
     * 리턴값 여부 확인 필요
     */
    protected function remove()
    {
        // Idx값 없으면 삭제진행 안함
        if ( empty($this->sTableName) === true ) {
            throw new Exception('삭제할 인덱스의 테이블이 설정되이 않았습니다.');
        }
        // Idx값 없으면 삭제진행 안함
        if ( empty($this->iBoardIdx) === true ) {
            throw new Exception('삭제할 인덱스가 없습니다.');
        }

        $this->dbm->setBind(['iIdx' => $this->iBoardIdx]);
        $this->dbm->delete($this->sTableName, "idx=:iIdx:");

        // 삭제시 s3 이미지 삭제처리
        if ( true ) {
            //인덱스 삭제시
            $aUploadFolder = explode("_", $this->sTableName);
            $sUploadFolder = end($aUploadFolder);                                           // 저장될 폴더는 테이블 이름에서 설정되었음
            if ( empty($sUploadFolder) === true ) {
                throw new Exception('삭제 진행 시 폴더설정 장애 발생');
            }
            //필요시 해당 폴더가 있는지 체크로직 추가하여 삭제요청 할 것
            $sSaveFolder = S3_UPLOAD_FOLDER . '/' . $sUploadFolder . '/' . $this->iBoardIdx;// 실제 저장된 S3 경로
            $this->s3->deleteFolder($sSaveFolder);                           // 파일폴더 삭제처리
        }
    }
}