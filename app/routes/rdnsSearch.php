<?php

$app->post('/rdnsSearch', function () use ($app) {
    
    if ((!array_key_exists('loggedin', $_SESSION)) || ($_SESSION['loggedin'] !== \TRUE)) {
        $app->redirect('/');
    }
       
    $_SESSION['api']->findOrphans();
    
    if ($this->orphans === \False) {
        $app->redirect('/menu');
    } else {
        $app->redirect('/orphans');
    }
    
});