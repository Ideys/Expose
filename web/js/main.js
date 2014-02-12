!function($) {
$(function(){
  $('body')
    .on('click', '[data-click]', function() {
        var target = $($(this).data('click'));
        target.click();
    })
    .on('click', '[data-display]', function(event) {
        event.stopImmediatePropagation();
        var target = $(this).data('display');
        $(target).toggleClass('hidden');
        return false;
    })
    ;
});
}(window.jQuery);
