<div class="container-fluid p-3">
    {=form_open('menu/regist/proc')}
    <input type="hidden" name="parent" value="{HTML.param.parent}">
    <table class="table">
        <thead class="table-primary">
            <tr>
                <th colspan="2">{HTML.title}</th>
            </tr>
        </thead>
        <tbody>
        <tr>
            <th><label for="menu_name">이름</label></th>
            <td>
                <input type="text" class="form-control form-control-sm" name="name" id="menu_name" value="">
            </td>
        </tr>
        <tr>
            <th><label for="menu_link">링크</label></th>
            <td>
                <input type="text" class="form-control form-control-sm" name="link" id="menu_link" value="">
            </td>
        </tr>
        <tr>
            <th>권한</th>
            <td>
                <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                    {@ HTML.admin_level}
                    <input type="radio" class="btn-check" name="level" id="level{.key_}" value="{.key_}" autocomplete="off" {? MENU.level== .key_}checked{/}>
                    <label class="btn btn-sm btn-outline-primary" for="level{.key_}">{.value_}</label>
                    {/}
                </div>
            </td>
        </tr>
        <tr>
            <th>사용 여부</th>
            <td>
                <div class="form-check form-switch" style="padding: 0; margin: 0;">
                    <input class="form-check-input" type="checkbox" name="state" id="state" value="1" {? MENU.state== '1'}checked{/} style="margin-left:0;">
                </div>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="form-group row">
        <div class="col text-center">
            <button type="button" class="btn btn-sm btn-secondary" onclick="self.close();">닫기</button>
            <button type="button" class="btn btn-sm btn-success" onClick="if (confirm('입력하신 내용으로 등록하시겠습니까?')) { form.submit(); }">등록</button>
        </div>
    </div>
    {=form_close()}
</div>
