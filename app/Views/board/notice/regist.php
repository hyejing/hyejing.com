<div class="modal-content card m-0 p-0">
    <div class="modal-header card-header p-4 align-middle">
        <p class="h5 m-0">공지사항 등록</p>
        <button type="button" class="btn-close float-right" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body card-body">

        <div class="input-group input-group-sm mb-3">
            <span class="input-group-text pl-4 pr-4 font-weight-bold">제목</span>
            <input type="text" name="title" id="title" class="form-control form-control-sm" value="" placeholder="제목을 입력해주세요.">
        </div>

        <div class="input-group input-group-sm mb-3">
            <span class="input-group-text pl-4 pr-4 font-weight-bold">고정</span>
            {@ HTML.is_top_type}
            <input type="radio" class="btn-check" name="is_top" id="is_top{.key_}" value="{.key_}" autocomplete="off" {? .key_ == '0'}checked{/}>
            <label class="btn btn-outline-secondary" for="is_top{.key_}">{.value_}</label>
            {/}
        </div>

        <div class="input-group input-group-sm mb-3">
            <span class="input-group-text pl-4 pr-4 font-weight-bold">출력</span>
            {@ HTML.state}
            <input type="radio" class="btn-check" name="state" id="state{.key_}" value="{.key_}" autocomplete="off" {? .key_ == '0'}checked{/}>
            <label class="btn btn-outline-secondary" for="state{.key_}">{.value_}</label>
            {/}
        </div>

        <div class="input-group input-group-sm">
            <span class="input-group-text pl-4 pr-4 font-weight-bold">내용</span>
            <textarea name="contents" id="contents" class="form-control editor" rows="15"></textarea>
        </div>

    </div>
    <div class="modal-footer card-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
        <button type="button" class="btn btn-success" onclick="notice_regist.regist();">등록</button>
    </div>

</div>

{# editor}