!function($) {
$(function(){
    $('body').on('click', '[data-inject-call]', function() {
        var url = $(this).data('inject-call')
          , target = $(this).data('target')
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
          , target = $(this).data('target')
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
    $('body').on('click', '[data-delete-ajax]', function(event) {
        event.stopImmediatePropagation();
        var url = $(this).data('delete-ajax')
          , target = $(this).data('target')
          ;
        $.ajax({
            url: url,
            type: 'POST'
        })
        .done(function(response) {
            $(target).remove();
        })
        .fail(function() {
            console.warn('AJAX deletion error.');
        });
        return false;
    });
});
}(window.jQuery);
