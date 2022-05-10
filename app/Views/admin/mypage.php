<div class="col-lg-12 mt-3">
    <table class="table">
        <thead>
        <colgroup>
            <col style="width:140px;" />
            <col />
        </colgroup>
        <tr>
            <th class="table-dark" colspan="2">기본 정보</th>
        </tr>
        </thead>
        <tr>
            <th class="text-center table-active"><label for="admin_id">ID</label></th>
            <td>{DATA.id}</td>
        </tr>
        <tr>
            <th class="table-active text-center">비밀번호</th>
            <td class="p-1 position-relative">
                {=form_open('admin/mypage/modifyPw', 'id="frm_modify_pw"')}
                <input type="password" class="form-control form-control-sm" name="pw_old" placeholder="기존 비밀번호" >
                <input type="password" class="form-control form-control-sm" name="pw" placeholder="수정할 비밀번호" >
                <input type="password" class="form-control form-control-sm" name="pw_re" placeholder="수정할 비밀번호 확인" >
                <button class="btn btn-sm btn-success position-absolute" id="modify_pw" style="right: 0.25rem; bottom: 0.25rem;"
                        onclick="if (!confirm('비밀번호를 수정하시겠습니까?')) { return false; }">수정</button>
                {=form_close()}
            </td>
        </tr>
        <tr>
            <th class="text-center table-active"><label for="admin_name">이름</label></th>
            <td class="p-1 position-relative">
                {=form_open('admin/mypage/modifyName')}
                <input type="text" class="form-control form-control-sm" name="name" id="admin_name" value="{DATA.name}" placeholder="이름" required>
                <button class="btn btn-sm btn-success position-absolute" id="modify_name" style="right: 0.25rem; bottom: 0.25rem;"
                        onclick="if (!confirm('이름을 수정하시겠습니까?')) { return false; }">수정</button>
                {=form_close()}
            </td>
        </tr>
        <tr>
            <th class="text-center table-active">가입 IP</th>
            <td>{DATA.reg_ip}</td>
        </tr>
        <tr>
            <th class="text-center table-active">가입 일시</th>
            <td>{DATA.reg_date}</td>
        </tr>
        <tr>
            <th class="text-center table-active">최근 로그인 IP</th>
            <td>{DATA.login_ip}</td>
        </tr>
        <tr>
            <th class="text-center table-active">최근 로그인 일시</th>
            <td>{DATA.login_date}</td>
        </tr>
    </table>

    <div class="form-group row">
        <div class="col text-center">
            <button type="button" class="btn btn-sm btn-secondary" onclick="self.close();">닫기</button>
        </div>
    </div>
</div>
