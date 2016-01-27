<?php

use ns1\apiCheat as cheat;

$app->get('/results', function() use ($app) {
    
    if ((!array_key_exists('loggedin', $_SESSION)) || ($_SESSION['loggedin'] !== \TRUE)) {
        $app->redirect('/');
    }
    
    $records = [];
    
    foreach ($_SESSION['api']->matches_array as $match) {
        $records[] = $match->domain;
    }
    
    $answer = $_SESSION['api']->fieldset;
    $status = $_SESSION['api']->status;
    if ($_SESSION['api']->rep_hide === \TRUE) {
        $hide = \TRUE;
    } else {
        $hide = \FALSE;
    }
    $page = "Results";
    $meta = "Results Menu";
    
    $app->render('results.html.twig', [
        'page' => $page,
        'meta' => $meta,
        'info' => $_SESSION['info'],
        'error' => $_SESSION['error'],
        'loggedin' => $_SESSION['loggedin'],
        'records' => $records,
        'answer' => $answer,
        'status' => $status,
        'hide' => $hide
    ]);
    
    cheat\Session::clear();    
    
})->name('results');