<?php

$app->post('/replace', function () use ($app) {

    if ((!array_key_exists('loggedin', $_SESSION)) || ($_SESSION['loggedin'] !== \TRUE)) {
        $app->redirect('/');
    }
    
    $clean_change_records = \filter_var_array(($app->request()->post('change_record')), \FILTER_SANITIZE_STRING);
    $clean_new_answer = \filter_var(($app->request()->post('new_answer')), \FILTER_SANITIZE_STRING);
        
    if ($clean_new_answer === "") {
        $_SESSION['error'][] = "No new answer was entered. Please try again!";
        $app->redirect('/results');
    }
    
    $_SESSION['api']->replaceAnswer($clean_new_answer, $clean_change_records);
        
    $app->redirect('/results');
    
});