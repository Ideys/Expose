!function($) {
$(function(){
    $('[data-sortable]').sortable({
        handle: ".handle",
        update: function(){
            var result = $(this).sortable('toArray', {attribute: 'data-id'})
              , sortUrl = $(this).data('sortable')
              ;
            $.post(sortUrl, {hierarchy: result}, function(json) {console.log(json)} );
        }
    });
});
}(window.jQuery);
