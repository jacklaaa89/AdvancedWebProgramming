function init(url)
$(document).ready(function() {
    $('.button').click(function() {
        var checked = {};
        $('.permission').each(function() {
            checked[$(this).data('key')] = $(this).attr('data-checked');
        });
        var r = $.ajax({
            'url': '/oauth/background/authorize/' + url,
            'dataType': 'JSON',
            'data': {
                'auth': $(this).data('auth'),
                'userID': '<?php echo $userID; ?>',
                'checked': JSON.stringify(checked)
            },
            'type': 'POST',
            'error': function() {
                alert('An error occured');
            }
        });
        r.done(function(response) {
            window.location = response.url;
        });
    });
    $('.permission').change(function() {
        $(this).attr('data-checked', $(this).attr('data-checked') == '1' ? '0' : '1');
    });
});


