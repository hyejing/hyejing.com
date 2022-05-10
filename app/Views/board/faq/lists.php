<div class="col-lg-12 mt-3">
    <div class="card">
        <div class="card-header">
            <h6 class="font-weight-bold text-primary">FAQ</h6>
        </div>
        <div class="card-body row">
            {=form_open('/board/faq/lists', 'method="get" id="faq_lists" class="d-flex bd-highlight mb-3 gx-3"')}
            <div class="p-1 bd-highlight row gx-3">
                <div class="col-auto">
                    <!-- 카테고리 -->
                    <select name="category" class="form-select form-select-sm" onchange="faq_list.search();">
                        <option value="" {? HTML.param.category == ''}selected{/}>전체</option>
                        {@ HTML.category_list}
                        <option value="{.key_}" {? HTML.param.category == .key_}selected{/}>{.value_}</option>
                        {/}
                    </select>
                </div>
                <div class="col-auto">
                    <!-- 상태 -->
                    <select name="state" class="form-select form-select-sm"  onchange="faq_list.search();">
                        <option value="" {? HTML.param.state === ''}selected{/}>상태</option>
                        {@ HTML.state}
                        <option value="{.key_}" {? strval(HTML.param.state) === strval(.key_)}selected{/}>{.value_}</option>
                        {/}
                    </select>
                </div>
                <div class="col-auto">
                    <!-- 검색타입 -->
                    <select name="search_type" class="form-select form-select-sm">
                        <option value="" {? HTML.param.search_type == ''}selected{/}>검색타입</option>
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
                    <button onclick="faq_list.search();" class="btn btn-sm btn-primary float-left">검색</button>
                </div>
            </div>
            <div class="ms-auto bd-highlight">
                <div class="d-flex flex-row-reverse bd-highlight">
                    {? HTML.param.category == ''}
                    <div class="px-2 bd-highlight">
                        <select name="view_count" class="form-select form-select-sm" onchange="faq_list.search();">
                            {@ HTML.view_count}
                            <option value="{.value_}" {? .value_ == HTML.param.view_count}selected{/}>{.value_}개</option>
                            {/}
                        </select>
                    </div>
                    {:}
                    <button class="float-right mb-2 btn btn-sm btn-success sort_btn" id="sort_btn" type="button" style="margin-right:10px;">
                        <i class="fa fa-arrows-alt-v"></i> 현재 순서를 저장
                    </button>
                    {/}
                </div>
            </div>
            {=form_close()}

            <div class="form-inline">
                <!-- 리스트 카운트 -->
                <div class="alert alert-success" role="alert">
                    <strong>검색결과 : </strong>{=number_format(DATA.total_count)}건
                </div>
                <!-- 신규입력 -->
                <div class="form-group mx-sm-3 mb-2">
                    <button type="button" class="btn btn-primary btn-sm" onclick="faq_list.regist();">신규입력</button>
                </div>
            </div>

            <!-- 리스트 -->
            <div class="col-12">
                <table class="table table-striped table-sorted">
                    <thead class="table-dark">
                    <tr>
                        <th scope="col">Idx</th>
                        {? HTML.param.category !== ''}
                        <th scope="col">sort</th>
                        {/}
                        <th scope="col">카테고리</th>
                        <th scope="col">제목</th>
                        <th scope="col">상태</th></th>
                        <th scope="col">작성일</th>
                        <th scope="col">수정</th>
                    </tr>
                    </thead>
                    <tbody>
                    {@ LIST}
                    <tr>
                        <td class="test-left" id="faq_idx">{.idx}</td>
                        {? HTML.param.category !== ''}
                        <td class="text-center handle" id="faq_sort" ><i class="fa fa-arrows-alt-v"></i>{.sort}</td>
                        {/}
                        <td class="test-left">{.category_txt}</td>
                        <td class="test-left">{.title}</td>
                        <td class="test-left">{.state_txt}</td>
                        <td class="test-left">{.reg_date}</td>
                        <td class="test-left"> <button type="button" class="btn btn-warning btn-sm" onclick="faq_list.modify({.idx})">수정</button></td>
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
