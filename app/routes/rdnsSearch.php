<?php

$app->post('/rdnsSearch', function () use ($app) {
    
    if ((!array_key_exists('loggedin', $_SESSION)) || ($_SESSION['loggedin'] !== \TRUE)) {
        $app->redirect('/');
    }
       
    $_SESSION['api']->findOrphans();
    
    $app->redirect('/menu');
    
});