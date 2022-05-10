<script type="text/javascript">
    let bbs = {
        regist: function () {
            let myModal = new bootstrap.Modal($('#layer_dialog_lg'), {
                keyboard: false
            });

            $('#section_dialog_lg').load('{C.URL_DOMAIN}/bbs/regist');
            myModal.show();
        },
        search : function() {
            $("#bbs_lists").attr("action", "{C.URL_DOMAIN}/bbs/lists");
            $("#bbs_lists").submit();
        },
        detail: function(idx) {
            let myModal = new bootstrap.Modal($('#layer_dialog_lg'), {
                keyboard: false
            });

            $('#section_dialog_lg').load('{C.URL_DOMAIN}/bbs/detail?idx=' + idx);
            myModal.show();
        },
        modify: function(idx) {
            let myModal = new bootstrap.Modal($('#layer_dialog_lg'), {
                keyboard: false
            });

            $('#section_dialog_lg').load('{C.URL_DOMAIN}/bbs/modify?idx=' + idx);
            myModal.show();
        },
        remove : function (idx) {
            if(confirm('컨텐츠 번호: '+idx+'를 삭제하시겠습니까?') === false){
                return false;
            }
            var oFormData = {
                'idx': idx,
                'detailText': $('#detail_text').val(),
                'title': $('#title').val(),
            };

            $.ajax({
                url: '{C.URL_DOMAIN}/bbs/remove',
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