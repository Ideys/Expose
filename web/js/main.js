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

    // Top link animation
    $("#to-top").click(function() {
        $("html, body").animate({scrollTop: 0}, 300);
        return false;
    });
    function displayTopLink() {
        if ($(window).scrollTop() > 800){
            $('#to-top').removeClass('hidden');
        } else {
            $('#to-top').addClass('hidden');
        }
    }
    displayTopLink();
    $(window).scroll(function() {
        displayTopLink();
    });
});
    $(window).load(function() {
        $('.hold-on').remove();
    });
}(window.jQuery);
