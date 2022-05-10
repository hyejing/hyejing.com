<div class="container-fluid p-3">
    {=form_open('admin/password/exec')}
    <input type="hidden" name="idx" value="{DATA.info.idx}" />
    <table class="table">
        <thead>
        <colgroup>
            <col style="min-width: 120px;" />
        </colgroup>
        <tr>
            <th class="table-warning" colspan="2">{HTML.title}</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th class="text-center table-active"><label for="admin_id">ID</label></th>
            <td>{DATA.info.id}<input type="hidden" name="id" value="{DATA.info.id}" /></td>
        </tr>
        <tr>
            <th class="text-center table-active"><label for="admin_pw">비밀번호</label></th>
            <td class="p-0 pl-2">
                <input type="password" class="form-control form-control-sm" name="pw" id="admin_pw" value="" placeholder="비밀번호" required>
            </td>
        </tr>
        <tr>
            <th class="text-center table-active"><label for="admin_pw_re">비밀번호 확인</label></th>
            <td class="p-0 pl-2">
                <input type="password" class="form-control form-control-sm" name="pw_re" id="admin_pw_re" value="" placeholder="비밀번호 확인" required>
            </td>
        </tr>
        </tbody>
    </table>

    <div class="form-group row">
        <div class="col text-center">
            <button type="button" class="btn btn-sm btn-secondary" onclick="self.close();">닫기</button>
            <button type="submit" class="btn btn-sm btn-success" onClick="if (!confirm('비밀번호를 변경하시겠습니까?')) { return false; }">변경</button>
        </div>
    </div>
    {=form_close()}
</div>


