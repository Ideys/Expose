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
    $('[data-selectable]').on('click', '[data-id]', function() {
        var item = $(this)
          , id = item.data('id')
          , list = item.parent('[data-selectable]')
          , stackActionPanel = $(list.data('selectable'))
          , counterInfo = stackActionPanel.find('.selectable-counter')
          ;

        item.toggleClass('selected');

        var totalSelection = list.find('.selected').length;
        counterInfo.text(totalSelection);
        if (totalSelection > 0) {
            stackActionPanel.removeClass('hidden');
        } else {
            stackActionPanel.addClass('hidden');
        }
    });
});
}(window.jQuery);
