<div class="col-lg-12 mt-3">
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">메인_섹션</h6>
        </div>
        <div class="card-body row">
            {=form_open('/manage/main/lists', 'method="get" id="main_section_lists" class="d-flex bd-highlight mb-3 gx-3"')}
            <div class="p-1 bd-highlight row gx-3">
                <div class="col-auto">
                    <!-- 상태 -->
                    <select name="type" class="form-select form-select-sm">
                        <option value="">타입</option>
                        {@ HTML.type}
                        <option value="{.key_}" {? HTML.param.type== .key_}selected{/}>{.value_}</option>
                        {/}
                    </select>
                </div>
                <div class="col-auto">
                    <!-- 상태 -->
                    <select name="state" class="form-select form-select-sm">
                        <option value="">오픈 여부</option>
                        {@ HTML.state}
                        <option value="{.key_}" {? HTML.param.state== .key_}selected{/}>{.value_}</option>
                        {/}
                    </select>
                </div>
                <div class="col-auto">
                    <!-- 검색어 -->
                    <input type="text" name="search_text" class="form-control form-control-sm mr-2" placeholder="" value="{HTML.param.search_text}">
                </div>
                <div class="col-auto">
                    <!-- 검색버튼 -->
                    <button type="submit" onclick="member.search();" class="btn btn-sm btn-success float-left">검색</button>
                </div>
            </div>
            <div class="ms-auto bd-highlight">
                <div class="d-flex flex-row-reverse bd-highlight">
                    <div class="px-2 bd-highlight">
                        <button class="float-right mb-2 btn btn-sm btn-primary add-btn" type="button" id="add_btn">
                            <i class="fa fa-pen"></i>섹션 등록
                        </button>
                        {? HTML.param.state === '' && HTML.param.type === '' && HTML.param.search_text === ''}
                        <button class="float-right mb-2 btn btn-sm btn-success section_sort_btn" id="section_sort_btn" type="button" style="margin-right:10px;">
                            <i class="fa fa-arrows-alt-v"></i> 현재 순서를 저장
                        </button>
                        {/}
                    </div>
                    <div class="bd-highlight">
                    </div>
                </div>
            </div>
            {=form_close()}

            <div class="table-responsive">
                <table class="table table-striped table-sorted">
                    <thead class="table-dark">
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">이름</th>
                        <th class="text-center">타입</th>
                        <th class="text-center">개수</th>
                        <th class="text-center">오픈 여부</th>
                        <th class="text-center">컨텐츠 등록</th>
                        <th class="text-center">섹션 관리</th>
                    </tr>
                    </thead>
                    <tbody>
                    {@ LIST}
                    <tr>
                        <td class="text-center handle"><i class="fa fa-arrows-alt-v"></i>{.sort}</td>
                        <td class="text-left">{.name}</td>
                        <td class="text-center">{.type_txt}</td>
                        <td class="text-right">{=number_format(.content_cnt)} 개</td>
                        <td class="text-center">{.state_txt}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-primary" onclick="location.href='{C.URL_DOMAIN}/manage/main/contents/lists?section_idx={.idx}'">컨텐츠 등록</button>
                        </td>
                        <td class="text-center">
                            <button onclick="sectionMain.modify('{.idx}');" class="btn btn-sm btn-warning edit_btn" data-json='{.idx}'>수정</button>
                        </td>
                    </tr>
                    {:}
                    <tr>
                        <td colspan="8" class="text-center">검색된 데이터가 없습니다.</td>
                    </tr>
                    {/}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
