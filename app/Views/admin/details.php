<div class="col-lg-12 mt-3">
    <button class="btn btn-secondary btn-sm mb-2" onclick="location.href = '{HTML.list_url}';">목록</button>
    <div class="text-center">
        {? HTML.ERROR.message}
        <div class="alert alert-danger" role="alert">{HTML.ERROR.message}</div>
        {/}
    </div>

    {=form_open('admin/details/exec')}
    <input type="hidden" name="idx" value="{DATA.info.idx}" />
    <table class="table">
        <thead>
        <colgroup>
            <col style="width:200px;" />
            <col />
        </colgroup>
        <tr>
            <th class="table-dark" colspan="2">기본 정보</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th class="text-center table-active"><label for="admin_id">ID</label></th>
            <td>{DATA.info.id}</td>
        </tr>
        <tr>
            <th class="table-active text-center">비밀번호</th>
            <td class="p-0 pl-1">
                <button type="button" class="btn btn-sm btn-warning" onclick="js_popup('{C.URL_DOMAIN}/admin/password?idx={DATA.info.idx}', 'modify_password', 500, 400);">변경</button>
            </td>
        </tr>
        <tr>
            <th class="text-center table-active"><label for="admin_name">이름</label></th>
            <td class="p-0 pl-1">
                <input type="text" class="form-control form-control-sm" name="name" id="admin_name" value="{DATA.info.name}" placeholder="이름" required>
            </td>
        </tr>
        <tr>
            <th class="text-center table-active">권한 등급</th>
            <td class="p-0 pl-1">
                <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                    {@ HTML.admin_level}
                    <input type="radio" class="btn-check" name="level" id="level{.key_}" value="{.key_}" autocomplete="off" {? DATA.info.level != '' && DATA.info.level == .key_}checked{/}>
                    <label class="btn btn-sm btn-outline-primary" for="level{.key_}">{.value_}</label>
                    {/}
                </div>
            </td>
        </tr>
        <tr>
            <th class="text-center table-active">상태</th>
            <td class="p-0 pl-1">
                <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                    {@ HTML.admin_state}
                    <input type="radio" class="btn-check" name="state" id="state{.key_}" value="{.key_}" autocomplete="off" {? DATA.info.state != '' && DATA.info.state == .key_}checked{/}>
                    <label class="btn btn-sm btn-outline-{? .key_ :'0'}danger{:'1'}success{:'2'}info{:}warning{/}" for="state{.key_}">{.value_}</label>
                    {/}
                </div>
            </td>
        </tr>
        <tr>
            <th class="text-center table-active">가입 IP</th>
            <td>{DATA.info.reg_ip}</td>
        </tr>
        <tr>
            <th class="text-center table-active">가입 일시</th>
            <td>{DATA.info.reg_date}</td>
        </tr>
        </tbody>
    </table>

    <div class="form-group row">
        <div class="col text-center">
            <button type="submit" class="btn btn-sm btn-success float-right" onClick="if (!confirm('수정하신 내용으로 저장하시겠습니까?')) { return false; }">
                <i class="fa-solid fa-save"></i> 저장
            </button>
        </div>
    </div>
    {=form_close()}

    <form action="">
    <table class="table">
        <thead>
        <colgroup>
            <col style="width:200px;" />
            <col />
        </colgroup>
        <tr>
            <th class="table-dark" colspan="2">최근 로그인 정보</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th class="text-center table-active">로그인 IP</th>
            <td>{DATA.info.login_ip}</td>
        </tr>
        <tr>
            <th class="text-center table-active">로그인 일시</th>
            <td>{DATA.info.login_date}</td>
        </tr>
        </tbody>
    </table>
    </form>

</div>
