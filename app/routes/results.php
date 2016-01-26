<?php

use ns1\apiCheat as cheat;

$app->get('/results', function() use ($app) {
    
    if ((!array_key_exists('loggedin', $_SESSION)) || ($_SESSION['loggedin'] !== \TRUE)) {
        $app->redirect('/');
    }
    
    $records = [];
    
    foreach ($_SESSION['api']->matches_array as $match) {
        $records[] = $match['domain'];
    }
    
    $answer = $_SESSION['api']->search_answer;
    $page = "Results";
    $meta = "Results Menu";
    
    $app->render('results.html.twig', [
        'page' => $page,
        'meta' => $meta,
        'info' => $_SESSION['info'],
        'error' => $_SESSION['error'],
        'loggedin' => $_SESSION['loggedin'],
        'records' => $records,
        'answer' => $answer
    ]);

    //echo "<pre style='color: white'>";print_r($_SESSION);echo "</pre>";
    //echo "<pre style='color: white'>";print_r($_SESSION['api']->matches_array[0]['answers'][1]['answer'][0]);echo "</pre>";    
    
    cheat\Session::clear();    
    
})->name('results');