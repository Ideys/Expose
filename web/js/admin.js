!function($) {
$(function(){
  $('body')
    .on('mouseenter', '[data-sortable]', function() {
        var list = $(this);

        if (!list.hasClass('sort-active')) {
            list.sortable({
                handle: ".handle",
                update: function(){
                    var result = $(this).sortable('toArray', {attribute: 'data-id'})
                      , sortUrl = $(this).data('sortable')
                    ;
                    $.post(sortUrl, {hierarchy: result}, function(json) {console.log(json)} );
                }
            });
            list.addClass('sort-active');
        }
    })
    .on('click', '[data-id]', function() {
        var item = $(this)
          , id = item.data('id')
          , list = item.parent('[data-selectable]')
          , stackActionPanel = $(list.data('selectable'))
          , counterInfo = stackActionPanel.find('.selectable-counter')
          ;

        item.toggleClass('selected');

        var selectedEntities = list.find('.selected')
          , totalSelection = selectedEntities.length
          , selectedIds = new Array()
          ;

        selectedEntities.each(function(){
            selectedIds.push($(this).data('id'));
        });
        list.data('selected', selectedIds.concat(','));

        counterInfo.text(totalSelection);
        if (totalSelection > 0) {
            stackActionPanel.removeClass('hidden');
        } else {
            stackActionPanel.addClass('hidden');
        }
    })
    .on('click', '[data-delete]', function(event) {
        event.stopPropagation();
        var list = $($(this).data('target'))
          , stackActionPanel = $(list.data('selectable'))
          , deleteUrl = $(this).data('delete')
          , selection = list.data('selected')
          ;

        $.post(deleteUrl, {items: selection}, function(json) {
            for (i in json) {
                $('[data-id="'+json[i]+'"]').remove();
                list.data('selected', '');
                stackActionPanel.addClass('hidden');
            }
            console.log(json);
        } );
        return false;
    });
});
}(window.jQuery);
