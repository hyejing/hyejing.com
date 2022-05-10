<script type="text/javascript">
    var notice_regist = {
        regist: function () {
            var title       = $('#title').val();
            var is_top      = $("input[name=is_top]:checked").val();
            var state       = $("input[name=state]:checked").val();
            var contents    = $('#contents').val();
            var sError      = '';

            if(!title){
                sError = '제목';
            }else if(!is_top){
                sError = '상단 상태값';
            }else if(!state){
                sError = '상태값';
            }else if(!contents){
                sError = '상세내용';
            }

            if(sError){
                alert(sError + '을 입력해 주세요');
                return false;
            }

            var oFormData = {
                'title'     : title,
                'is_top'    : is_top,
                'state'     : state,
                'contents'  : contents,
            };

            $.ajax({
                url: '{C.URL_DOMAIN}/board/notice/regist/proc',
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