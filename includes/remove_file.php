<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $file = $_POST['file'];

    if (empty($file)) {
        http_response_code(400);
        echo 'No file to delete.';
        exit;
    }

    echo 'Hey! File deleted successfully.';
    /*
    if ( unlink( $file ) ) {
        http_response_code(200);
        echo __( 'File deleted successfully.', 'lcmd-tracking-codes' );
    } else {
        http_response_code(500);
        echo __( 'Failed on file delete', 'lcmd-tracking-codes' );
    }
    */
} else {
    http_response_code(403);
    echo 'Request unknow.';
}

 ?>