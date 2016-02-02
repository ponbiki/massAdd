<?php

use ns1\apiCheat as cheat;

$app->get('/menu', function() use ($app) {
    
    if ((!array_key_exists('loggedin', $_SESSION)) || ($_SESSION['loggedin'] !== \TRUE)) {
        $app->redirect('/');
    }
    
    $page = "Search Menu";
    $meta = "Search Menu";
    
    $app->render('menu.html.twig', [
        'page' => $page,
        'meta' => $meta,
        'info' => $_SESSION['info'],
        'error' => $_SESSION['error'],
        'loggedin' => $_SESSION['loggedin']
    ]);
    
    echo"<pre style='color:white'>";\print_r($_SESSION);echo"</pre>";
    
    cheat\Session::clear();    
    
})->name('menu');