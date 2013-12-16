!function($) {
$(function(){
    $('body').on('click', '[data-inject-call]', function() {
        var url = $(this).data('inject-call')
          , target = $(this).data('inject-in')
          ;
        $.ajax({
            url: url
        })
        .done(function(response) {
            $(target).html(response);
        })
        .fail(function() {
            console.warn('AJAX injection error.');
        });
    });
    $('body').on('click', '[data-submit-ajax]', function(event) {
        event.stopImmediatePropagation();
        var form = $(this).parents('form')
          , url = form.prop('action')
          , formData = form.serialize()
          , target = $(this).data('inject-in')
          ;
        $.ajax({
            url: url,
            type: 'POST',
            data: formData
        })
        .done(function(response) {
            $(target).html(response);
        })
        .fail(function() {
            console.warn('AJAX injection error.');
        });
        return false;
    });
});
}(window.jQuery);
