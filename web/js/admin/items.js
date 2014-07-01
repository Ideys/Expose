!function($) {
$(function(){
  $('body')
    .on('mouseenter', '[data-sortable]', function() {
        var list = $(this);

        if (!list.hasClass('sort-active')) {
            list.sortable({
                handle: ".handle",
                placeholder: "item-sort-placeholder",
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
    .on('click', '[data-selectable]', function() {
        var item = $(this)
          , section = item.parents('.items-holder')
          ;

        item.toggleClass('selected');

        itemsSelection(section);
    })
    .on('click', '[data-select]', function(){
        var section = $(this).parents('.items-holder')
          , items = section.find('[data-selectable]')
          , state = $(this).data('select')
          , title = $(this).attr('title')
          , altTitle = $(this).data('alt-title')
          ;

        if ('all' === state) {
            items.removeClass('selected');
            $(this).data('select', 'none');
        } else {
            items.addClass('selected');
            $(this).data('select', 'all');
        }

        $(this)
            .attr('title', altTitle)
            .data('alt-title', title);

        itemsSelection(section);
    })
    .on('click', '[data-move]', function(event) {
        event.stopPropagation();

        var link = $(this)
          , list = $(link.data('target'))
          , moveUrl = link.attr('href')
          , selection = list.data('selected')
          ;

        $.post(moveUrl, {items: selection}, function(items) {
            removeEditedItems(list, items);
            resetStackSelection(list);
        } );
        return false;
    })
    .on('click', '[data-toggle-visibility]', function(event) {
        event.stopPropagation();

        var link = $(this)
          , list = $(link.data('target'))
          , toggleUrl = link.attr('href')
          , selection = list.data('selected')
          ;

        $.post(toggleUrl, {items: selection}, function(items) {
            for (var i in items) {
                $('[data-id="'+items[i]+'"]').toggleClass('active');
            }
            resetStackSelection(list);
        } );
        return false;
    })
    .on('click', '[data-insert-picture]', function(event) {
        event.stopPropagation();

        var link = $(this)
          , listId = link.data('target')
          , list = $(listId)
          , id = listId.replace(/[^0-9]/g, '')
          , textareaId = '#section-form-'+id+' .markItUpEditor'
          , selection = list.data('selected')
          ;

        for (var i in selection) {
            var picSrc = $('[data-id="'+selection[i]+'"]')
                .find('img')
                .attr('src')
                .replace('/220/', '/1200/');
            $.markItUp({
                target: textareaId,
                replaceWith: "\n"+'<img src="'+picSrc+'" />'+"\n"
            });
        }

        resetStackSelection(list);
        $('#universal-modal').foundation('reveal', 'close');
    })
    .on('click', '[data-delete]', function(event) {
        event.stopPropagation();

        var button = $(this)
          , list = $(button.data('target'))
          , deleteUrl = button.data('delete')
          , confirmMessage = button.data('confirm-message')
          , selection = list.data('selected')
          ;

        if (confirm(confirmMessage)) {
            $.post(deleteUrl, {items: selection}, function(items) {
                removeEditedItems(list, items);
                resetStackSelection(list);
            } );
        }
        return false;
    })
    .on('click', '[data-delete-ajax]', function(event) {
        event.stopImmediatePropagation();
        var url = $(this).data('delete-ajax')
          , target = $(this).data('target')
          , message = $(this).data('confirm')
          ;

        if (false === confirm(message)) {
            return false;
        }

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
    })
    .on('click', '[data-attach]', function(event) {
        event.stopImmediatePropagation();
        var url = $(this).data('attach')
          , container = $(this).parent('.panel')
          ;

        $.ajax({
            url: url,
            type: 'POST'
        })
        .done(function(response) {
            $(container).toggleClass('active');
        })
        .fail(function() {
            console.warn('AJAX attach error.');
        });
        return false;
    })
    ;

}); // End on DOM ready.

var itemsSelection = function(section) {
    var list = section.find('[data-selected]')
      , stackActionPanel = section.find('[data-selectable-actions]')
      , counterInfo = stackActionPanel.find('.selectable-counter')
      , selectedEntities = section.find('.selected')
      , totalSelection = selectedEntities.length
      , selectedIds = []
      ;

    selectedEntities.each(function(){
        selectedIds.push($(this).data('id'));
    });
    list.data('selected', selectedIds.concat());

    counterInfo.text(totalSelection);

    if (totalSelection > 0) {
        stackActionPanel.removeClass('hidden');
    } else {
        stackActionPanel.addClass('hidden');
    }
};

var removeEditedItems = function(list, items) {

    var sectionSlidesCounter = list.parents('.section').find('.counter')
      , currentCount = parseInt(sectionSlidesCounter.text())
      , newCount = (currentCount - items.length)
      ;

    for (var i in items) {
        $('[data-id="'+items[i]+'"]').remove();
    }
    sectionSlidesCounter.text(newCount);
};

var resetStackSelection = function(list) {
    var stackActionPanel = list
            .parents('.content')
            .find('[data-selectable-actions]')
      , items = list.find('[data-selectable]')
      ;

    list.data('selected', '');
    items.removeClass('selected');
    stackActionPanel.addClass('hidden');
};

}(window.jQuery);
