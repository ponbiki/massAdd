<?php

use ns1\apiCheat as cheat;

$app->get('/results', function() use ($app) {
    
    if ((!array_key_exists('loggedin', $_SESSION)) || ($_SESSION['loggedin'] !== \TRUE)) {
        $app->redirect('/');
    }
    
    $records = [];
    
    unset($_SESSION['weeny']);
    foreach ($_SESSION['api']->matches_array as $match) {
        $records[] = $match->domain;
        foreach ($match->answers as $answ) {
            if (preg_match("/{$_SESSION['api']->search_answer}/i", $answ->answer[0])) {
                $_SESSION['weeny'][] = $answ->answer[0];
            }
        }
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
    
    echo"<pre style='color:white'>";print_r($_SESSION/*['api']->matches_array*/);echo"</pre>";
    
    cheat\Session::clear();    
    
})->name('results');