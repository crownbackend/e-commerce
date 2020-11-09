$(document).ready(function() {
var email = $('#email_error')
    $('.email').on('change', function () {
        $.ajax({
            url: email.data('url'),
            type: 'POST',
            data: {'email': this.value},
            dataType: 'json',
            success: function (data) {
                console.log(data)
                if(data.success === 1) {
                    email.html(data.message)
                } else if(data.error === 0) {
                    email.html(data.message)
                } else if(data.taken === 1) {
                    email.html(data.message)
                }
            },
            error: function (data) {
                console.error(data)
            }
        })
    })

})