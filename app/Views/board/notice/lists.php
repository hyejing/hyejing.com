<div class="col-lg-12 mt-3">
    <div class="card">
        <div class="card-header">
            <h5 class="font-weight-bold text-primary float-left">{HTML.title}</h5>
            <button type="button" class="btn btn-primary btn-sm float-right" onclick="notice_list.regist();">공지사항 등록</button>
        </div>
        <div class="card-body row">

            <!-- 리스트 -->
            <div class="col-12">
                <!-- 검색 폼 -->
                {=form_open('/board/notice/lists', 'method="get" id="notice_lists" class="d-flex bd-highlight gx-3"')}
                <table class="table m-0">
                    <tr>
                        <td>
                            <div class="row p-0">
                                <div class="input-group input-group-sm col-auto m-1 p-0 col-3" >
                                    <span class="form-control input-group-text text-center">작성일</span>
                                    <!-- 시작일 -->
                                    <input type="text" name="start_date" class="form-control form-control-sm text-center w-25 sel_date" placeholder="시작일" value="{HTML.param.start_date}">
                                    <!-- 종료일 -->
                                    <input type="text" name="end_date" class="form-control form-control-sm text-center w-25 sel_date" placeholder="종료일" value="{HTML.param.end_date}">
                                </div>

                                <div class="col-auto m-1 p-0">
                                    <!-- 상단고정 -->
                                    <select name="is_top" class="form-select form-select-sm" onchange="notice_list.search();">
                                        <option value="" {? HTML.param.state === ''}selected{/}>고정여부</option>
                                        {@ HTML.is_top_type}
                                        <option value="{.key_}" {? strval(HTML.param.is_top) === strval(.key_)}selected{/}>{.value_}</option>
                                        {/}
                                    </select>
                                </div>

                                <div class="col-auto m-1 p-0">
                                    <!-- 출력 -->
                                    <select name="state" class="form-select form-select-sm" onchange="notice_list.search();">
                                        <option value="" {? HTML.param.state === ''}selected{/}>상태 전체</option>
                                        {@ HTML.state}
                                        <option value="{.key_}" {? strval(HTML.param.state) === strval(.key_)}selected{/}>{.value_}<i class="fa-solid fa-eye"></i></option>
                                        {/}
                                    </select>
                                </div>

                                <div class="col-auto input-group input-group-sm m-1 p-0 col-4">
                                    <select class="form-control form-select" name="search_type" id="search_type">
                                        <option value="">검색조건</option>
                                        {@ HTML.search_type}
                                        <option value="{.key_}" {? HTML.param.search_type == .key_}selected{/}>{.value_}</option>
                                        {/}
                                    </select>
                                    <!-- 검색어 -->
                                    <input type="text" name="search_text" class="form-control form-control-sm text-center" placeholder="검색어" value="{HTML.param.search_text}" style="width:30%;">

                                    <input type="radio" class="btn-check" name="match_type" id="match_type_match" value="match" {? HTML.param.match_type == "" || HTML.param.match_type == "match"}checked{/}>
                                    <label class="btn btn-outline-secondary" for="match_type_match">일치</label>
                                    <input type="radio" class="btn-check" name="match_type" id="match_type_include" value="include" {? HTML.param.match_type == "include"}checked{/}>
                                    <label class="btn btn-outline-secondary" for="match_type_include">포함</label>
                                </div>

                                <div class="col-auto btn-group btn-group-sm m-1 p-0">
                                    <!-- 검색버튼 -->
                                    <button class="btn btn-success pr-3 pl-3" onclick="notice_list.search();">검색</button>
                                    <!-- 초기화버튼 -->
                                    <button type="button" class="btn btn-outline-success" onclick="notice_list.reset();" title="검색조건 초기화"><i class="fa-solid fa-rotate-left"></i></button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-inline p-0">
                                <div class="float-left form-control-sm">
                                    <strong>검색결과 : </strong>{=number_format(DATA.total_count)}건
                                </div>
                                <div class="float-right">
                                    <select class="form-select form-select-sm text-center" name="view_count" onchange="notice_list.search();">
                                        {@ HTML.view_count}
                                        <option value="{.value_}" {? .value_ == HTML.param.view_count}selected{/}>{.value_}개</option>
                                        {/}
                                    </select>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
                {=form_close()}

                <table class="table table-hover">
                    <thead class="table-dark">
                    <tr class="text-center">
                        <th scope="col" style="width:30px;"><i class="fa-solid fa-thumbtack"></th>
                        <th scope="col" style="width:80px;">Idx</th>
                        <th scope="col" style="width:30px;">출력</th>
                        <th scope="col">제목</th>
                        <th scope="col" style="width:150px;">작성일</th>
                    </tr>
                    </thead>
                    <tbody>
                    {@ LIST}
                    <tr {? .is_top > 0}class="table-warning"{/}>
                        <td class="text-center">{? .is_top > 0}<i class="fa-solid fa-thumbtack"></i>{/}</td>
                        <td class="text-center" id="notice_idx">{.idx}</td>
                        <td class="text-center" style="width:50px;">
                            {? .state > 0}
                            <i class="fa-solid fa-eye"></i>
                            {:}
                            <i class="fa-solid fa-eye-slash opacity-25"></i>
                            {/}
                        </td>
                        <td class="text-left"><a href="javascript: notice_list.modify({.idx});">{.title}</a></td>
                        <td class="text-center">{.reg_date}</td>
                    </tr>
                    {:}
                    <tr>
                        <td colspan="7" class="text-center">검색된 데이터가 없습니다.</td>
                    </tr>
                    {/}
                    </tbody>
                </table>
                {# paging}
            </div>
        </div>
    </div>
</div>
