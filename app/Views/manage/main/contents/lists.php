<div class="col-lg-12 mt-3">
    <div class="card">
        <div class="card-header">
            <h6 class="font-weight-bold text-primary">컨텐츠 ({=number_format(DATA.total_count)}건)</h6>
        </div>
        <div class="card-body row">
            <div class="ms-auto bd-highlight">
                <div class="d-flex flex-row-reverse bd-highlight">
                    <div class="px-2 bd-highlight">
                        <button class="float-right mb-2 btn btn-sm btn-primary add-btn" type="button" id="add_btn" onclick="sectionContent.regist_layer()">
                            <i class="fa fa-pen"></i> 컨텐츠 등록
                        </button>
                        <button class="float-right mb-2 btn btn-sm btn-success section_sort_btn" id="section_sort_btn" type="button" style="margin-right:10px;">
                            <i class="fa fa-arrows-alt-v"></i> 현재 순서를 저장
                        </button>
                        <button class="float-right mb-2 btn btn-sm btn-info" type="button" id="list_btn" onclick="location.href='{C.URL_DOMAIN}/manage/main/lists'" style="margin-right:10px;">
                            <i class="fa fa-list"></i> 목록
                        </button>
                    </div>
                    <div class="bd-highlight">
                    </div>
                </div>
            </div>
            <!-- 리스트 -->
            <div class="table-responsive">
                <table class="table table-striped table-sorted text-center">
                    <thead class="table-dark">
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">컨텐츠 IDX</th>
                        <th scope="col">제목</th>
                        <th scope="col">상태</th>
                        <th scope="col">등록일</th>
                        <th scope="col">관리</th>
                    </tr>
                    </thead>
                    <tbody>
                    {@ LIST}
                    <tr>
                        <td class="text-center handle"><i class="fa fa-arrows-alt-v"></i>{.sort}</td>
                        <td>{.bbs_idx}</td>
                        <td class="text-left">{.title}</td>
                        <td><div class="badge {? .state=='1'}badge-success{:}badge-danger{/}">{.state_txt}</div></td>
                        <td>{.reg_date}</td>
                        <td class="text-center">
                            <button onclick="sectionContent.modify_info('{.idx}');" class="btn btn-sm btn-info edit_btn" data-json='{.idx}'>컨텐츠 관리</button>
                        </td>
                    </tr>
                    {:}
                    <tr>
                        <td colspan="6" class="text-center">검색된 데이터가 없습니다.</td>
                    </tr>
                    {/}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <input type="hidden" id="section_idx" value="{HTML.param.section_idx}">
</div>
