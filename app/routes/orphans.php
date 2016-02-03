<?php

use ns1\apiCheat as cheat;

$app->get('/orphans', function() use ($app) {
    
    if ((!array_key_exists('loggedin', $_SESSION)) || ($_SESSION['loggedin'] !== \TRUE)) {
        $app->redirect('/');
    }
    
    $answer = $_SESSION['api']->fieldset;
    $status = $_SESSION['api']->status;
    $page = "Orphaned PTR's";
    $meta = "Orphaned PTR Records";
    
    $app->render('orphans.html.twig', [
        'page' => $page,
        'meta' => $meta,
        'info' => $_SESSION['info'],
        'error' => $_SESSION['error'],
        'loggedin' => $_SESSION['loggedin'],
        'answer' => $answer,
        'status' => $status
    ]);

    cheat\Session::clear();    
    
})->name('orphans');