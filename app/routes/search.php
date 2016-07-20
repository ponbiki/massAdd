<?php

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

    if (isset($_SESSION['api']->matches_array)) {
        $_SESSION['info'][] = "Found ". \count($_SESSION['api']->matches_array) . " records containing {$_SESSION['api']->search_answer}";
        $app->redirect('/results');
    } else {
        $app->redirect('/menu');
    }

});