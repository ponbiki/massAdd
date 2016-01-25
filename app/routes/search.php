<?php

use ns1\apiCheat as cheat;

$app->post('/search', function () use ($app) {
    
    if ((!array_key_exists('loggedin', $_SESSION)) || ($_SESSION['loggedin'] !== \TRUE)) {
        $app->redirect('/');
    }
    
    $clean_answer = \filter_var(($app->request()->post('answer')), \FILTER_SANITIZE_STRING);
    
    if ($clean_answer === "") {
        $_SESSION['error'][] = "No answer was entered. Please try again!";
        $app->redirect('/menu');
    }
    
    $_SESSION['api']->getMatches($clean_answer);

    $app->redirect('/menu');

});