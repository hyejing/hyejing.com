<script type="text/javascript">

    var faq_regist = {
        regist: function () {

            //ckeditor textarea 내용을 hidden 값에 입력처리 - @_ckeditor4_add
            var _content = CKEDITOR.instances.ckeditor.getData();//내용 가져오기

            let oFormData = {
                'title': $('#title').val(),
                'category': $('#category').val(),
                'state': $("input[name=state]:checked").val(),
                'contents': _content,
            };

            $.ajax({
                url: '{C.URL_DOMAIN}/board/faq/regist/proc',
                method: 'POST',
                data: oFormData,
                dataType: 'json',
                success: function (oRes) {
                    if (oRes.success === true) {
                        alert(oRes.msg);
                        location.reload();
                    } else {
                        alert(oRes.msg);
                        console.log(oRes.data);
                        console.log(oRes.code);
                    }
                }
            });
        },
    }
</script>