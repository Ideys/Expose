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
    .on('click', '[data-dir-toggle]', function(){
        var contentSection = $(this).parents('.content-section')
          , dirContent = contentSection.find('.dir-content')
          , toggleIndicator = contentSection.find('.dir-toggle-indicator')
          ;

        dirContent.toggleClass('hidden');
        $({deg: 0}).animate({deg: 180}, {
            duration: 300,
            step: function(now) {
                toggleIndicator.css({
                    transform: 'rotate(' + now + 'deg)'
                });
            }
        });
        toggleIndicator
                .toggleClass('fi-plus')
                .toggleClass('fi-minus');
    })
    .on('click', '[data-gallery-upload]', function(){
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
    .on('click', '[data-click-link]', function() {
        var link = $($(this).data('click-link'));

        link.click();

        return false;
    })
    .on('click', '[data-confirm-action]', function(event) {
        event.stopImmediatePropagation();
        var message = $(this).data('confirm-action');

        return confirm(message);
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
        var button = $(this)
          , form = button.parents('form')
          , url = form.prop('action')
          , formData = form.serialize()
          , target = button.data('target')
          , btnMessages = button.find('.btn-msg')
          , submitMessage = button.find('.btn-ajax-submit')
          , processMessage = button.find('.btn-ajax-process')
          , savedMessage = button.find('.btn-ajax-saved')
          , failedMessage = button.find('.btn-ajax-failed')
          ;

        btnMessages.addClass('hidden');
        processMessage.removeClass('hidden');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData
        })
        .done(function(response) {
            if (target.length) {
                $(target).html(response);
            }
            btnMessages.addClass('hidden');
            savedMessage.removeClass('hidden');
        })
        .fail(function() {
            btnMessages.addClass('hidden');
            failedMessage.removeClass('hidden');
        })
        .always(function() {
            window.setTimeout(function(){
                btnMessages.addClass('hidden');
                submitMessage.removeClass('hidden');
            }, 2000);
        });
        return false;
    })
    .on('keyup', '#section-settings #form_title', function() {
        var sectionTitle = $(this).parents('.section').find('.section-title')
          , newTitle = $(this).val()
          ;

        sectionTitle.text(newTitle);
    })
    .on('change', '#form_maintenance', function() {
        if ('1' === $(this).val()) {
            $(this).addClass('active1');
            $('#maintenance-indicator').removeClass('hidden');
        } else {
            $(this).removeClass('active1');
            $('#maintenance-indicator').addClass('hidden');
        }
    })
    .on('change', '#form_contactSection', function() {
        var contactContentWrapper = $('#contact-content-wrapper')
          , sendToWrapper = $('#contact-sendto-wrapper')
          ;
        contactContentWrapper.removeClass('hidden');
        sendToWrapper.removeClass('hidden');

        switch ($(this).val()) {
            case 'disabled':
                contactContentWrapper.addClass('hidden');
            break;
            case 'no.form':
                sendToWrapper.addClass('hidden');
        }
    })
    .on('change', '#form_visibility', function() {
        var section = $(this).parents('.section')
          , stateIcon = section.find('.state-icon')
          , visibility = $(this).val()
          , visibilityIcons = {
                'homepage': 'home',
                'public': 'link',
                'private': 'lock',
                'hidden': 'unlink',
                'closed': 'prohibited'
            }
          ;
        section.prop('class', 'section active '+visibility+'-section');
        stateIcon.prop('class', 'state-icon fi-'+visibilityIcons[visibility]);
    })
    .on('change', '#form_gallery_mode', function() {
        var mode = $(this).val()
          , form = $(this).parents('form')
          ;

        form.find('.for-mode').addClass('hidden');
        form.find('.for-'+mode).removeClass('hidden');
    })
    .on('focus', 'input[data-date-format]', function(){
        if (!$(this).hasClass(('active'))) {
            var dateformat = $(this).data('date-format');

            $(this)
                .fdatepicker({format: dateformat})
                .addClass('active')
                ;
        }
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

    // Open panel from url hash tag.
    var url = window.location.href.split('#');
    if (url[1] != undefined) {
        var sectionLink = $('[href=#'+url[1]+']')
          , sectionDir = sectionLink.parents('.dir-sections')
          ;

        if (sectionDir != undefined) {
            sectionDir.find('[data-dir-toggle]').click();
        }
        sectionLink.click();
    }

    // New messages counter
    var messagesCounterWrapper = $('#messages-counter')
      , messagesCounterUrl = messagesCounterWrapper.data('url')
      ;

    $.ajax({
        url: messagesCounterUrl,
        type: 'GET'
    })
    .done(function(count) {
        if (count > 0) {
            messagesCounterWrapper
                .text(count)
                .removeClass('hidden');
        }
    })
    .fail(function() {
        console.log('Messages counter error.');
    });

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

/**
 * Insert content using AJAX into a modal then display it.
 * @param url
 */
var callUniversalModalContent = function(url) {

    $.ajax({
        url: url,
        type: 'GET'
    })
    .done(function(html) {
        $('#universal-modal-content').html(html);
        $('#universal-modal').foundation('reveal', 'open');
    })
    .fail(function() {
        console.log('Universal modal AJAX error.');
    });
};

var smartUpload = function(section) {
    var uploadForm = $(section)
      , sectionSlidesCounter = uploadForm.parents('.section').find('.counter')
      , noticeEmpty = uploadForm.find('.notice-empty')
      , currentCount = parseInt(sectionSlidesCounter.text())
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
                if (undefined != noticeEmpty) {
                    noticeEmpty.remove();
                }
                data.submit();
            },
            done: function (e, data) {
                currentCount += 1;
                sectionSlidesCounter.text(currentCount);
                $.each(data.result, function (index, file) {
                    var item = $('<li data-selectable data-id="'+file.id+'" class="active"></li>')
                        , slide = $('<img/>')
                            .prop('src', basePath + '/gallery/220/' + file.path)
                            .addClass('th handle');
                    item.append(slide);
                    item.append('<i class="fi-check"></i><i class="fi-prohibited slide-state"></i>');
                    item.appendTo(uploadGrid);
                });
            }
        });
        uploadForm.addClass('upload-active');
    }
};
