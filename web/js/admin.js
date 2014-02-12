!function($) {
$(function(){
  $('body')
    .on('click', '[data-inject-call]', function() {
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
    })
    .on('click', '[data-gallery-upload]', function(){
        var uploadForm = $($(this).data('gallery-upload'))
          , uploadProgress = uploadForm.find('.progress .meter')
          , uploadGrid = uploadForm.find('.upload-grid')
          , uploadInfo = uploadForm.find('.upload-info')
          , uploadProgressText = uploadInfo.data('upload-info')
          , picsCounter = 0
          ;

        if (!uploadForm.hasClass('upload-active')) {
            uploadForm.fileupload({
                dropZone: uploadForm,
                dataType: 'json',
                progressall: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    uploadProgress.css('width', progress + '%');
                },
                add: function (e, data) {
                    picsCounter++;
                    var uploadText = uploadProgressText.replace('%i', picsCounter);
                    uploadInfo.text(uploadText);
                    data.submit();
                },
                done: function (e, data) {
                    $.each(data.result, function (index, file) {
                        var item = $('<li class="new"></li>')
                          , slide = $('<img/>')
                            .prop('src', basePath + '/gallery/220/' + file.path)
                            .addClass('th handle');
                        item.append(slide);
                        item.appendTo(uploadGrid);
                    });
                }
            });
            uploadForm.addClass('upload-active');
        }
    })
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
          , section = item.parents('.content-section')
          ;

        item.toggleClass('selected');

        itemsSelection(section);
    })
    .on('click', '[data-select]', function(){
        var section = $(this).parents('.content-section')
          , items = section.find('[data-selectable]')
          , mode = $(this).data('select')
          ;

        if ('all' === mode) {
            items.addClass('selected');
        } else if ('none' === mode) {
            items.removeClass('selected');
        }

        itemsSelection(section);
    })
    // Foundation dropdown fix after ajax injection
    .on('click', '[data-dropdown-ajax]', function(){
        event.stopPropagation();
        var link = $(this)
          , menu = $('#'+link.data('dropdown-ajax'))
          ;

        menu.toggleClass('open');
        if (menu.hasClass('open')) {
            menu.css('top', '50px');
        } else {
            menu.css('top', '-99999px');
        }
        return false;
    })
    .on('click', '[data-move]', function(event) {
        event.stopPropagation();

        var link = $(this)
          , list = $(link.data('target'))
          , moveUrl = link.attr('href')
          , selection = list.data('selected')
          ;

        $.post(moveUrl, {items: selection}, function(items) {
            removeEditedItems(items);
            resetStackSelection(list);
        } );
        return false;
    })
    .on('click', '[data-delete]', function(event) {
        event.stopPropagation();

        var button = $(this)
          , list = $(button.data('target'))
          , deleteUrl = button.data('delete')
          , selection = list.data('selected')
          ;

        $.post(deleteUrl, {items: selection}, function(items) {
            removeEditedItems(items);
            resetStackSelection(list);
        } );
        return false;
    })
    .on('click', '[data-delete-ajax]', function(event) {
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
    })
    .on('click', '[data-submit-ajax]', function(event) {
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
    })
    .on('click', '[data-select-video-provider]', function(event) {
        var container = $(this).parents('.content')
          , form = container.find('form')
          , selector = container.find('.provider-selector')
          , formSelector = container.find('#form_provider')
          , provider = $(this).data('select-video-provider')
          ;
        form.removeClass('hidden');
        selector.addClass('hidden');
        formSelector.val(provider);
    })
    ;

    $('#content-sections, .dir-sections .accordion').sortable({
        handle: ".handle-section",
        placeholder: "section-sort-placeholder",
        update: function(){
            var result = $(this).sortable('toArray', {attribute: 'data-id'})
              , sortUrl = $('#content-sections').data('sort-url')
            ;
            $.post(sortUrl, {hierarchy: result}, function(json) {console.log(json)} );
        }
    });

  $('#new-section')
    .on('change', '#form_type', function() {
        var field = $(this)
          , type = $(this).val()
          , form = field.parents('form')
          , sectionRelatedBlocks = form.find('.section-fields')
          ;

        if ('dir' === type) {
            sectionRelatedBlocks.addClass('hidden');
        } else {
            sectionRelatedBlocks.removeClass('hidden');
        }
    });
});

var itemsSelection = function(section) {
    var list = section.find('[data-selected]')
      , stackActionPanel = section.find('[data-selectable-actions]')
      , counterInfo = stackActionPanel.find('.selectable-counter')
      , selectedEntities = section.find('.selected')
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
};

var removeEditedItems = function(items) {
    for (var i in items) {
        $('[data-id="'+items[i]+'"]').remove();
    }
    console.log(items);
};

var resetStackSelection = function(list) {
    var stackActionPanel = list
            .parents('.content')
            .find('[data-selectable-actions]');

    list.data('selected', '');
    stackActionPanel.addClass('hidden');
};

}(window.jQuery);
