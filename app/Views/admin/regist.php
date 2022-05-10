<div class="container-fluid p-3">

    <div class="text-center">
        {? HTML.ERROR.message}
        <div class="alert alert-danger" role="alert">{HTML.ERROR.message}</div>
        {/}
    </div>

    {=form_open('admin/regist/exec')}
    <table class="table">
        <thead>
        <colgroup>
            <col style="min-width: 120px;" />
        </colgroup>
        <tr>
            <th class="table-primary" colspan="2">{HTML.title}</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th class="text-center"><label for="admin_id">ID</label></th>
            <td class="p-0 pl-2">
                <input type="text" class="form-control form-control-sm" name="id" id="admin_id" value="{HTML.param.id}" placeholder="ID" required>
            </td>
        </tr>
        <tr>
            <th class="text-center"><label for="admin_pw">비밀번호</label></th>
            <td class="p-0 pl-2">
                <input type="password" class="form-control form-control-sm" name="pw" id="admin_pw" value="" placeholder="비밀번호" required>
            </td>
        </tr>
        <tr>
            <th class="text-center"><label for="admin_pw_re">비밀번호 확인</label></th>
            <td class="p-0 pl-2">
                <input type="password" class="form-control form-control-sm" name="pw_re" id="admin_pw_re" value="" placeholder="비밀번호 확인" required>
            </td>
        </tr>
        <tr>
            <th class="text-center"><label for="admin_name">이름</label></th>
            <td class="p-0 pl-2">
                <input type="text" class="form-control form-control-sm" name="name" id="admin_name" value="{HTML.param.name}" placeholder="이름" required>
            </td>
        </tr>
        <tr>
            <th class="text-center">권한 등급</th>
            <td class="p-1 pl-2">
                <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                    {@ HTML.admin_level}
                    <input type="radio" class="btn-check" name="level" id="level{.key_}" value="{.key_}" autocomplete="off">
                    <label class="btn btn-sm btn-outline-primary" for="level{.key_}">{.value_}</label>
                    {/}
                </div>
            </td>
        </tr>
        <tr>
            <th class="text-center">상태</th>
            <td class="p-0 pl-2">
                <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                    {@ HTML.admin_state}
                    <input type="radio" class="btn-check" name="state" id="state{.key_}" value="{.key_}" autocomplete="off">
                    <label class="btn btn-sm btn-outline-{? .key_ :'0'}danger{:'1'}success{:'2'}info{:}warning{/}" for="state{.key_}">{.value_}</label>
                    {/}
                </div>
            </td>
        </tr>
        </tbody>
    </table>

    <div class="form-group row">
        <div class="col text-center">
            <button type="button" class="btn btn-sm btn-secondary" onclick="self.close();">닫기</button>
            <button type="submit" class="btn btn-sm btn-success" onClick="if (!confirm('입력하신 내용으로 등록하시겠습니까?')) { return false; }">등록</button>
        </div>
    </div>
    {=form_close()}
</div>


