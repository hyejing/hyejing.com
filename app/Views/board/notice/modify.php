<div class="modal-content card m-0 p-0">
    <div class="modal-header card-header p-4 align-middle">
        <p class="h5 m-0">공지사항 수정</p>
        <button type="button" class="btn-close float-right" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body card-body">

        <div class="input-group input-group-sm mb-3">
            <span class="input-group-text font-weight-bold col-1 d-table">IDX</span>
            <span class="input-group-text bg-white">{DATA.idx}</span>
        </div>

        <div class="input-group input-group-sm mb-3">
            <span class="input-group-text font-weight-bold col-1 d-table">제목</span>
            <input type="text" name="title" id="title" class="form-control form-control-sm" value="{DATA.title}" placeholder="제목을 입력해주세요.">
        </div>

        <div class="input-group input-group-sm mb-3">
            <span class="input-group-text font-weight-bold col-1 d-table">고정</span>
            {@ HTML.is_top_type}
            <input type="radio" class="btn-check" name="is_top" id="is_top{.key_}" value="{.key_}" autocomplete="off" {? .key_ == DATA.is_top}checked{/}>
            <label class="btn btn-outline-secondary" for="is_top{.key_}">{.value_}</label>
            {/}
        </div>

        <div class="input-group input-group-sm mb-3">
            <span class="input-group-text font-weight-bold col-1 d-table">출력</span>
            {@ HTML.state}
            <input type="radio" class="btn-check" name="state" id="state{.key_}" value="{.key_}" autocomplete="off" {? .key_ == DATA.state}checked{/}>
            <label class="btn btn-outline-secondary" for="state{.key_}">{.value_}</label>
            {/}
        </div>

        <div class="input-group input-group-sm mb-3">
            <span class="input-group-text font-weight-bold col-1 d-table">작성일</span>
            <span class="input-group-text bg-white">{DATA.reg_date}</span>
        </div>

        <div class="input-group input-group-sm">
            <span class="input-group-text font-weight-bold col-1 d-table">내용</span>
            <textarea name="contents" id="contents" class="form-control" rows="15">{DATA.contents}</textarea>
        </div>

    </div>
    <div class="modal-footer card-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
        <button type="button" class="btn btn-primary" onclick="notice_modify.modify({DATA.idx});">수정</button>
    </div>

</div>
