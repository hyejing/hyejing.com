<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">자료등록</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <from id="uploadForm">
    <table class="table mb-4">
        <tbody>
        <tr>
            <th class="text-nowrap" scope="row">제목</th>
            <td colspan="3">
                <input id="title" class="form-control text-left">
            </td>
        </tr>
        <tr>
            <th class="text-nowrap" scope="row">카테고리</th>
            <td colspan="3">
                <select id="cate" name="cate"  class="form-control form-select col-sm-3">
                    {@ CATE}
                    <option value="{.key_}">{.value_}</option>
                    {/}
                </select>
            </td>
        </tr>
        <tr>
            <th class="text-nowrap" scope="row">아이디</th>
            <td>{LIST.id}</td>
            <th class="text-nowrap" scope="row">닉네임</th>
            <td>{LIST.nick}</td>
        </tr>
        <tr>
            <th class="text-nowrap" scope="row">가격</th>
            <td>
                <div class="col-sm-4 float-left">
                    <select id="free" name="free" class="form-control form-select" onchange="regist.isFree();">
                        <option value="pay">유료</option>
                        <option value="free">무료</option>
                    </select>
                </div>
                <div class="col-sm-3 float-left" id="cashgroup"><input id="cash" class="form-control text-left" value="1.00" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></div><div class="align-self-center" >USD</div></td>

        </tr>
        <tr>
            <th class="text-nowrap" scope="row">업로드파일</th>
            <td colspan="3">
                <div class="input-group mb-3">
                    <button class="btn btn-outline-primary" type="button" onclick="regist.selectFile();">파일선택</button>
                    <input type="text" class="form-control" id="file_path" onclick="regist.selectFile();">
                </div>
            </td>
        </tr>
        </tbody>
    </table>


    <div class="mt-4">
        <textarea id="detail_text" class="form-control text-left" rows="10" >{DATA.detail.detail}</textarea>
    </div>
    </from>
    <div class="p-4 text-center"><button onclick="regist.regist();"  class="btn btn-sm btn-primary">저장</button></div>
</div>
