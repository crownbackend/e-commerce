$(document).ready(function() {
    var url = $('#url')
    var email = $('#email_error')
    var password = $('#password_error')
    // check email
    $('.email').on('keyup', function () {
        $.ajax({
            url: url.data('url-email'),
            type: 'POST',
            data: {'email': this.value},
            dataType: 'json',
            success: function (data) {
                if(data.success === 1) {
                    email.html(data.message)
                    setTimeout(function () {
                        email.html('')
                    }, 5000)
                } else if(data.error === 0) {
                    email.html(data.message)
                } else if(data.taken === 1) {
                    email.html(data.message)
                }
            },
            error: function (data) {
                if(data.status === 500 || data.status === 404)
                {
                    alert('Error server')
                }
            }
        })
    })

    // check password
    $('.password').on("keyup", function () {
        $.ajax({
            url: url.data('url-password'),
            type: 'POST',
            data: {'password': this.value},
            dataType: 'json',
            success: function (data) {
                if(data.success === 1) {
                    password.html(data.message)
                    setTimeout(function () {
                        password.html('')
                    }, 5000)
                } else if(data.error === 0) {
                    password.html(data.message)
                }
            },
            error: function (data) {
                console.log(data)
            }
        })
    })

    $('#show_password').on('click', function () {
        if($('.password').attr('type') == "password") {
            $('.password').prop('type', 'text');
        } else {
            $('.password').prop('type', 'password');
        }
    })
})