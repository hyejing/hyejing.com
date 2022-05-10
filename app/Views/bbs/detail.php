<script type="text/javascript" src="{C.URL_DOMAIN}{=getAssetPath('/assets/vendor/hls.js')}"></script>
<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">{LIST.title}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <table class="table mb-4">
        <tbody>
        <tr>
            <th class="text-nowrap" scope="row">아이디</th>
            <td>{LIST.id}</td>
            <th class="text-nowrap" scope="row">닉네임</th>
            <td>{LIST.nick}</td>
        </tr>
        <tr>
            <th class="text-nowrap" scope="row">가격</th>
            <td>{=number_format(LIST.cash,2)} USD</td>
            <th class="text-nowrap" scope="row">등록일</th>
            <td>{LIST.reg_date}</td>
        </tr>
        <tr>
            <th class="text-nowrap" scope="row">파일명</th>
            <td colspan="3">
                {@ DATA.file}
                    <div>{.filename}</div>
                {/}
            </td>
        </tr>

        <tr>
            <td colspan="4">
                <div class="text-center"><video height ='300px' controls muted autoplay id="video-player"></video></div>
                    <div class="m-2 text-center">
                        {@ DATA.image}
                        <a href="{.img_url}" target="_blank"><img height="110" src='{.img_url}' class="m-2 rounded-3 shadow "></a>
                        {/}
                    </div>

                <div class="mt-4">
                    {=nl2br(DATA.detail.detail)}
                </div>
            </td>
        </tr>

        </tbody>
    </table>
</div>
