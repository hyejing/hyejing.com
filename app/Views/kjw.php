
{=form_open_multipart('kjw/exec', 'id="frm"')}
<input type="file" name="imgfile" id="imgfile" class="form-control" />
<button class="btn btn-success">Go!</button>
{=form_close()}

<script>
    $('#imgfile').on("change", function(e) {
        $("#frm").submit();
    });
</script>