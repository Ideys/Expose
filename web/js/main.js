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
});
}(window.jQuery);
