<script type="text/javascript">
let member = {
    search : function() {
        $("#member_lists").attr("action", "/member/lists");
        $("#member_lists").submit();
    },
    excel : function() {
        $("#member_lists").attr("action", "/member/lists/excel");
        $("#member_lists").submit();
    },
}

$(document).ready(function() {

});
</script>