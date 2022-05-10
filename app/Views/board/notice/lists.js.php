<script type="text/javascript">
    let notice_list = {
        search: function () {
            $("#notice_lists").attr("action", "{C.URL_DOMAIN}/board/notice/lists");
            $("#notice_lists").submit();
        },
        regist: function () {
            let myModal = new bootstrap.Modal($('#layer_dialog_lg'), {
                keyboard: false
            });

            $('#section_dialog_lg').load('{C.URL_DOMAIN}/board/notice/regist');
            myModal.show();
        },
        modify: function (idx) {
            let myModal = new bootstrap.Modal($('#layer_dialog_lg'), {
                keyboard: false
            });

            $('#section_dialog_lg').load('{C.URL_DOMAIN}/board/notice/modify?idx=' + idx);
            myModal.show();
        },
        reset: function () {
            $("#notice_lists INPUT").val('');
            $("#notice_lists SELECT").not("SELECT[name='view_count']").val('');
        }
    }
</script>