<?php

use ns1\apiCheat as cheat;

$app->get('/menu', function() use ($app) {
    
    echo "<pre style='color: white'>";print_r($_SESSION['api']->matches_array);echo "</pre>";
    
    if ((!array_key_exists('loggedin', $_SESSION)) || ($_SESSION['loggedin'] !== \TRUE)) {
        $app->redirect('/');
    }
    
    $page = "Menu";
    $meta = "API Cheat Menu";
    
    $app->render('menu.html.twig', [
        'page' => $page,
        'meta' => $meta,
        'info' => $_SESSION['info'],
        'error' => $_SESSION['error'],
        'loggedin' => $_SESSION['loggedin']
    ]);
    
    cheat\Session::clear();    
    
})->name('menu');