!function($) {
$(function(){

    $('#messages-list').on('click', '[data-mark-as-read]', function() {

        var link = $(this)
          , url = link.data('mark-as-read')
          , newMessagesCounter = $('#messages-counter')
          , unreadMessagesCounter = $('#unread-counter')
          , newCount = parseInt(unreadMessagesCounter.text()) - 1
          , label = link.find('.label.new')
          ;

        $.ajax({
            url: url,
            type: 'POST'
        })
        .done(function(ok) {
            if (ok) {
                label.remove();
                link.removeAttr('data-mark-as-read');
                newMessagesCounter.text(newCount);
                unreadMessagesCounter.text(newCount);
            }
        })
        .fail(function() {
            console.log('Message mark as read error.');
        });

    });

}); // End on DOM ready.
}(window.jQuery);
