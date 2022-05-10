<div class="col-lg-12 mt-3">
    <div class="card">
        <div class="card-header">
            <h6 class="font-weight-bold text-primary">전체 자료 ({=number_format(DATA.total_count)}건)</h6>
        </div>
        <div class="card-body row">
            {=form_open('/bbs/lists', 'method="get" id="bbs_lists" class="d-flex bd-highlight mb-3 gx-3"')}
            <div class="p-1 bd-highlight row gx-3">
                <div class="col-auto">
                    <!-- 상태 -->
                    <select name="state" class="form-select form-select-sm">
                        <option value="">상태</option>
                        {@ HTML.state}
                        <option value="{.key_}" {? HTML.param.state !== '' && HTML.param.state == .key_}selected{/}>{.value_}</option>
                        {/}
                    </select>
                </div>
                <div class="col-auto">
                    <!-- 기준일 -->
                    <select name="date_type" class="form-select form-select-sm">
                        <option value="reg_date" {? HTML.param.date_type == 'reg_date'}selected{/}>등록일 기준</option>
                    </select>
                </div>
                <div class="col-sm">
                    <!-- 시작일 -->
                    <input type="text" name="start_date" class="form-control form-control-sm mr-2 sel_date" placeholder="시작일" value="{HTML.param.start_date}">
                </div>
                <div class="col-sm">
                    <!-- 종료일 -->
                    <input type="text" name="end_date" class="form-control form-control-sm mr-2 sel_date" placeholder="종료일" value="{HTML.param.end_date}">
                </div>
                <div class="col-auto">
                    <!-- 검색타입 -->
                    <select name="search_type" class="form-select form-select-sm">
                        {@ HTML.search_type}
                        <option value="{.key_}" {? HTML.param.search_type == .key_}selected{/}>{.value_}</option>
                        {/}
                    </select>
                </div>
                <div class="col-sm">
                    <!-- 검색어 -->
                    <input type="text" name="search_text" class="form-control form-control-sm mr-2" placeholder="" value="{HTML.param.search_text}">
                </div>
                <div class="col-auto">
                    <!-- 검색버튼 -->
                    <button type="button" onclick="bbs.search();" class="btn btn-sm btn-success float-left">검색</button>
                </div>
                <div class="col-auto">
                    <!-- 검색버튼 -->
                    <button type="button" onclick="bbs.regist();" class="btn btn-sm btn-primary float-left">등록</button>
                </div>
                <div class="col-auto">
                    <select name="view_count" class="form-select form-select-sm" onchange="bbs.search();">
                        {@ HTML.view_count}
                        <option value="{.value_}" {? .value_ == HTML.param.view_count}selected{/}>{.value_}개</option>
                        {/}
                    </select>
                </div>
            </div>

            {=form_close()}

            <!-- 리스트 -->
            <div class="col-12">
                <table class="table table-striped table-hover text-center">
                    <thead class="table-dark">
                    <tr>
                        <th scope="col">Idx</th>
                        <th scope="col">상태</th>
                        <th scope="col">제목</th>
                        <th scope="col">아이디</th>
                        <th scope="col">닉네임</th>
                        <th scope="col">가격(USD)</th>
                        <th scope="col">등록일</th>
                        <th scope="col">수정</th>
                        <th scope="col">삭제</th>
                    </tr>
                    </thead>
                    <tbody>
                    {@ LIST}
                    <tr>
                        <td>{.idx}</td>
                        <td><span class="badge bg-{.state_bg}">{.state_txt}</span></td>
                        <td class="text-left" > <a href='javascript: bbs.detail({.idx});' > {.title}</a></td>
                        <td class="text-left">{.id}</td>
                        <td class="text-left">{.nick}</td>
                        <td class="text-end">{=number_format(.cash,2)}</td>
                        <td>{.reg_date}</td>
                        <td><button onclick="bbs.modify({.idx})" class="btn btn-sm btn-warning">수정</button></td>
                        <td>
                            {?.state != 100}
                                <button onclick="bbs.remove({.idx})" class="btn btn-sm btn-danger">삭제</button>
                            {/}
                        </td>
                    </tr>
                    {:}
                    <tr>
                        <td colspan="7">검색결과가 없습니다.</td>

                    </tr>
                    {/}

                    </tbody>
                </table>

                {# paging}
            </div>
        </div>
    </div>
</div>
