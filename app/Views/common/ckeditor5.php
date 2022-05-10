<script type="text/javascript" src="{C.URL_DOMAIN}/assets/editor/ckeditor5/build/ckeditor.js"></script>
<script>
    ClassicEditor
    .create( document.querySelector( '.editor' ), {
        licenseKey: '',
    } )
    .then( editor => {
        window.editor = editor;
    } )
    .catch( error => {
        console.error( 'Oops, something went wrong!' );
        console.error( 'Please, report the following error on https://github.com/ckeditor/ckeditor5/issues with the build id and the error stack trace:' );
        console.warn( 'Build id: yxa9ie1z04d4-19xoqmc0tpeo' );
        console.error( error );
    } );
</script>
