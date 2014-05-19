!function($) {
$(function(){
  $('body')
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
          , sendToWrapper = $('#contact-send-to-wrapper')
          ;
        contactContentWrapper.removeClass('hidden');
        sendToWrapper.removeClass('hidden');

        switch ($(this).val()) {
            case 'disabled':
                contactContentWrapper.addClass('hidden');
                sendToWrapper.addClass('hidden');
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
    ;

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

}); // End on DOM ready.

}(window.jQuery);
