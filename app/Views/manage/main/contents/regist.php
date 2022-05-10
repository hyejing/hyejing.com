<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">컨텐츠 등록</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="col-md-12">
            <div class="form-group row border-bottom pb-3 pt-2">
                <label class="col-md-4 pt-2 text-center">컨텐츠 idx(bbs_idx)</label>
                <div class="col-md-8 col-sm-9 pt-1">
                    <input id="search_input" type="text" name="bbs_idx" list="bbs_idx" class="form-control bbs_idx" value="">
                    <datalist id ="bbs_idx">
                        {@ HTML.bbs_idx_list}
                        <option value="{.idx}">{.title}</option>
                        {/}
                    </datalist>
                </div>
            </div>
            <div class="form-group row border-bottom pb-3">
                <label class="col-md-4 pt-2 text-center">상태</label>
                <div class="col-md-8 col-sm-9 pt-1">
                    {@ HTML.states}
                    <label class="radio pr-2">
                        <input type="radio" name="state" value="{.key_}" {? .key_=='1'}checked{/}> {.value_}
                    </label>
                    {/}
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
        <button type="button" class="btn btn-primary" onclick="sectionContent.regist()">저장</button>
    </div>
</div>
