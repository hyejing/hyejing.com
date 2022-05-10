<!-- 에디터 이미지 업로드 시작 @_ckeditor4_add -->
{=form_open_multipart('', 'id="img_upload_form" style="display:none;"')}
<!--<form id="img_upload_form" enctype="multipart/form-data" method="post" style="display:none;">-->
<input type="hidden" name="up_type" id="up_type" value="faq_img">
<input type='file' id="img_file" multiple="multiple" name='imgfile[]' accept="image/*">
</form>

<div id="ajaxImageModal" style="display:none;">
    <div id="light" style="display: table;position: absolute;top:25%;left:25%;width:50%;height:50%; text-align:center; background-color:transparent; z-index:1002;overflow: auto;">
        <div style="display: table-cell; vertical-align: middle;">
            <img src="/assets/editor/ckeditor4/plugins/ajaximage/loading.gif" style="user-select: none; -ms-user-select: none;">
        </div>
    </div>
</div>
<!-- 에디터 이미지 업로드 끝 -->