<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">섹션 등록</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="col-md-12">
            <div class="form-group row border-bottom pb-3 pt-2">
                <label class="col-md-4 pt-2 text-center">이름</label>
                <div class="col-md-8 col-sm-9 pt-1">
                    <input type="text" name="name" class="form-control" value="">
                </div>
            </div>
            <div class="form-group row border-bottom pb-3">
                <label class="col-md-4 pt-2 text-center">타입</label>
                <div class="col-md-8 col-sm-9 pt-1">
                    {@ HTML.types}
                    <label class="radio pr-2">
                        <input type="radio" name="type" value="{.key_}" {? .key_=='1'}checked{/}> {.value_}
                    </label>
                    {/}
                </div>
            </div>
            <div class="form-group row">
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
        <button type="button" class="btn btn-primary" onclick="regist_main_section()">저장</button>
    </div>
</div>
