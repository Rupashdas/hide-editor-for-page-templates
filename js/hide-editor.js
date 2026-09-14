(function($){
    $(document).ready(function(){
        $('.page-templates').select2({
            placeholder: 'Select a template',
            multiple: true,
            width: '100%',
            allowClear: true,
        });
        $('.user-roles').select2({
            placeholder: 'All roles',
            multiple: true,
            width: '100%',
            allowClear: true,
        });
    });
}(jQuery));
