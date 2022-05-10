<div class="col-lg-12 mt-3">
    <div class="card">
        <div class="card-header">
            <h6 class="font-weight-bold text-primary">회원 목록 ({=number_format(DATA.total_count)}건)</h6>
        </div>
        <div class="card-body row">
            {=form_open('/member/lists', 'method="get" id="member_lists" class="d-flex bd-highlight mb-3 gx-3"')}
            <div class="p-1 bd-highlight row gx-3">
                <div class="col-auto">
                    <!-- 상태 -->
                    <select name="state" class="form-select form-select-sm">
                        <option value="">상태</option>
                        {@ HTML.state}
                        <option value="{.key_}" {? HTML.param.state == .key_}selected{/}>{.value_}</option>
                        {/}
                    </select>
                </div>
                <div class="col-auto">
                    <!-- 기준일 -->
                    <select name="date_type" class="form-select form-select-sm">
                        <option value="login_last" {? HTML.param.date_type == 'login_date'}selected{/}>로그인 기준</option>
                        <option value="reg_date" {? HTML.param.date_type == 'reg_date'}selected{/}>가입일 기준</option>
                    </select>
                </div>
                <div class="col-auto">
                    <!-- 시작일 -->
                    <input type="text" name="start_date" class="form-control form-control-sm mr-2 sel_date" placeholder="시작일" value="{HTML.param.start_date}">
                </div>
                <div class="col-auto">
                    <!-- 종료일 -->
                    <input type="text" name="end_date" class="form-control form-control-sm mr-2 sel_date" placeholder="종료일" value="{HTML.param.end_date}">
                </div>
                <div class="col-auto">
                    <!-- 검색타입 -->
                    <select name="search_type" class="form-select form-select-sm">
                        <option value="">상태</option>
                        {@ HTML.search_type}
                        <option value="{.key_}" {? HTML.param.search_type == .key_}selected{/}>{.value_}</option>
                        {/}
                    </select>
                </div>
                <div class="col-auto">
                    <!-- 검색어 -->
                    <input type="text" name="search_text" class="form-control form-control-sm mr-2" placeholder="" value="{HTML.param.search_text}">
                </div>
                <div class="col-auto">
                    <!-- 검색버튼 -->
                    <button onclick="member.search();" class="btn btn-sm btn-primary float-left">검색</button>
                </div>
            </div>
            <div class="ms-auto bd-highlight">
                <div class="d-flex flex-row-reverse bd-highlight">
                    <div class="px-2 bd-highlight">
                        <select name="view_count" class="form-select form-select-sm">
                            {@ HTML.view_count}
                            <option value="{.value_}" {? .value_ == HTML.param.view_count}selected{/}>{.value_}개</option>
                            {/}
                        </select>
                    </div>
                    <div class="bd-highlight">
<!--                        <button onclick="member.excel();" class="btn btn-sm btn-primary float-left">엑셀 다운로드</button>-->
                    </div>
                </div>
            </div>
            {=form_close()}

            <!-- 리스트 -->
            <div class="col-12">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                    <tr>
                        <th scope="col">Idx</th>
                        <th scope="col">상태</th>
                        <th scope="col">아이디</th>
                        <th scope="col">닉네임</th>
                        <th scope="col">마지막 로그인</th>
                        <th scope="col">활동 IP</th>
                        <th scope="col">가입일</th>
                    </tr>
                    </thead>
                    <tbody>
                    {@ LIST}
                    <tr>
                        <td class="test-left">{.idx}</td>
                        <td class="test-left">{.state_txt}</td>
                        <td class="test-left"><a href="/member/details?idx={.idx}" target="_blank">{.id}</a></td>
                        <td class="test-left">{.nick}</td>
                        <td class="test-left">{.login_last}</td>
                        <td class="test-left">{.login_ip}</td>
                        <td class="test-left">{.reg_date}</td>
                    </tr>
                    {/}
                    </tbody>
                </table>

                {# paging}
            </div>
        </div>
    </div>
</div>
