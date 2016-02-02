<?php

$app->post('/rdnsSearch', function () use ($app) {
    
    if ((!array_key_exists('loggedin', $_SESSION)) || ($_SESSION['loggedin'] !== \TRUE)) {
        $app->redirect('/');
    }
       
    $zones = $_SESSION['api']->findOrphans();
    
    echo"<pre style='color:white'>";\print_r($zones);echo"</pre>";
    
    /*if () {
       
    } else {
        $app->redirect('/orphans');
    }*/
    
});