<?php

$app->post('/rdnsSearch', function () use ($app) {
    
    if ((!array_key_exists('loggedin', $_SESSION)) || ($_SESSION['loggedin'] !== \TRUE)) {
        $app->redirect('/');
    }
       
    $_SESSION['api']->findOrphans();
    
    if ($_SESSION['api']->orphans === \False) {
        $app->redirect('/menu');
    } else {
        $app->redirect('/orphans');
    }
    
});