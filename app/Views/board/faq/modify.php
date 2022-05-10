<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">FAQ 수정</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="col-md-12">
            <div class="form-group row border-bottom pb-3 pt-2">
                <label class="col-md-4 pt-2 text-center">제목</label>
                <div class="col-md-8 col-sm-9 pt-1">
                    <input type="text" name="title" id="title" class="form-control" value="{DATA.title}" placeholder="제목을 입력해주세요.">
                </div>
            </div>
            <div class="form-group row border-bottom pb-3">
                <label class="col-md-4 pt-2 text-center">카테고리</label>
                <input type="hidden" name="oricategory" id="oricategory" value="{DATA.category}" />
                <div class="col-md-8 col-sm-9 pt-1">
                    <!-- 카테고리 -->
                    <select name="category" id="category" class="form-select form-select-sm">
                        {@ HTML.category_list}
                        <option value="{.key_}" {? DATA.category == .key_}selected{/}>{.value_}</option>
                        {/}
                    </select>
                </div>
            </div>
            <div class="form-group row border-bottom pb-3">
                <label class="col-md-4 pt-2 text-center">노출여부</label>
                <div class="col-md-8 col-sm-9 pt-1">
                    <!-- 상태 -->
                    {@ HTML.state}
                    <input type="radio" class="btn-check" name="state" id="state{.key_}" value="{.key_}" autocomplete="off" {? DATA.state== .key_}checked{/}>
                    <label class="btn-sm btn-outline-primary" for="state{.key_}">{.value_}</label>
                    {/}
                </div>
            </div>
            <div class="form-group row border-bottom pb-3">
                <div class="col pt-1">
                    <textarea name="ckeditor" id="ckeditor" class="form-control" rows="15">{DATA.contents}</textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
        <button type="button" class="btn btn-primary" onclick="faq_modify.modify({DATA.idx});">수정</button>
    </div>
    <!-- 에디터 이미지 업로드 시작 @_ckeditor4_add -->
    {# editor_js}
    {# editor}
    <!-- 에디터 이미지 업로드 끝 -->
</div>
