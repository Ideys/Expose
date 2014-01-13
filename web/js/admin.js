!function($) {
$(function(){
  $('body')
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
    .on('click', '[data-toggle-section]', function(){
        var section = $(this).parents('.section')
          , toggleUrl = $(this).data('toggle-section')
          ;

        $.post(toggleUrl, {}, function(done) {
            if (done === true) {
                section.toggleClass('hidden-section');
            } else {
                console.warn('Toggle section error');
            }
        } );
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
});
}(window.jQuery);
