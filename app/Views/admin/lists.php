<div class="col-lg-12 mt-3">
    <div class="card">
        <div class="card-header">
            <h6 class="font-weight-bold text-primary float-left">{HTML.title} ({=number_format(DATA.total_count)}건)</h6>
            <button type="button" class="btn btn-primary btn-sm float-right" onclick="js_popup('{C.URL_DOMAIN}/admin/regist', 'admin_regist', 600, 550);">신규 등록</button>
        </div>
        <div class="card-body row">
            {=form_open('/admin/lists', 'method="get" id="member_lists" class="d-flex bd-highlight mb-3 gx-3"')}
            <div class="p-1 bd-highlight row gx-3">
                <div class="col-auto">
                    <!-- 상태 -->
                    <select name="state" class="form-select form-select-sm">
                        <option value="">상태 전체</option>
                        {@ HTML.admin_state}
                        <option value="{.key_}" {? HTML.param.state != '' && HTML.param.state == .key_}selected{/}>{.value_}</option>
                        {/}
                    </select>
                </div>
                <div class="col-auto">
                    <!-- 상태 -->
                    <select name="level" class="form-select form-select-sm">
                        <option value="">등급 전체</option>
                        {@ HTML.admin_level}
                        <option value="{.key_}" {? HTML.param.level != '' && HTML.param.level == .key_}selected{/}>{.value_}</option>
                        {/}
                    </select>
                </div>
                <!-- 검색 -->
                <div class="col-auto">
                    <select name="search_type" class="form-select form-select-sm">
                        <option value="">검색</option>
                        {@ HTML.search_type}
                        <option value="{.key_}" {? HTML.param.search_type == .key_}selected{/}>{.value_}</option>
                        {/}
                    </select>
                </div>
                <div class="col-auto">
                    <!-- 검색어 -->
                    <input type="text" name="search_text" class="form-control form-control-sm mr-2" placeholder="검색어" value="{HTML.param.search_text}">
                </div>
                <div class="col-auto">
                    <!-- 검색버튼 -->
                    <button class="btn btn-sm btn-success float-left">검색</button>
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
                    </div>
                </div>
            </div>
            {=form_close()}

            <!-- 리스트 -->
            <div class="col-12">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr class="text-center">
                            <th scope="col">Idx</th>
                            <th scope="col">상태</th>
                            <th scope="col">등급</th>
                            <th scope="col">아이디</th>
                            <th scope="col">이름</th>
                            <th scope="col">로그인 일시</th>
                            <th scope="col">로그인 IP</th>
                            <th scope="col">가입 일시</th>
                        </tr>
                    </thead>
                    <tbody>
                        {@ LIST}
                        <tr>
                            <td class="text-center">{.idx}</td>
                            <td class="text-center"><span class="badge bg-{? .state :'0'}danger{:'1'}success{:'2'}info{:}warning{/}">{.state_txt}</span></td>
                            <td class="text-center">{.level_txt}</td>
                            <td class="text-left"><a href="javascript: location.href = '{C.URL_DOMAIN}/admin/details?idx={.idx}';">{.id}</a></td>
                            <td class="text-left">{.name}</td>
                            <td class="text-center">{.login_date}</td>
                            <td class="text-center">{.login_ip}</td>
                            <td class="text-center">{.reg_date}</td>
                        </tr>
                        {/}
                    </tbody>
                </table>

                {# paging}
	        </div>
        </div>
    </div>
</div>

