<?php

$app->post('/del_orphan', function () use ($app) {

    if ((!array_key_exists('loggedin', $_SESSION)) || ($_SESSION['loggedin'] !== \TRUE)) {
        $app->redirect('/');
    }

    if (is_array($app->request()->post('del_records'))) {
        $clean_del_records = \filter_var_array(($app->request()->post('del_records')), \FILTER_SANITIZE_STRING);
    } else {
        $_SESSION['error'][] = 'No PTR records to delete!';
        $clean_del_records = [];
        $app->redirect('/menu');
    }

    $_SESSION['api']->delPtr($clean_del_records);

    $app->redirect('/menu');

});