jQuery(document).ready(function($) {

    function delete_verification_file( filename ) {
        $.ajax({
            type: 'post',
            url:  '',
            data: {'filename': filename}

        }).done(function(response) {
            $('#form-msg').text(response);
            $('#name').val('');
            $('#email').val('');

        }).fail(function(data) {
            if (data.response !== '') {
                $('#form-msg').text(data.response);
            } else {
                $('#form-msg').text('Erro no envio da mensagem.');
            }
        });
    });

});
