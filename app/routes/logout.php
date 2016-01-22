<?php

use ns1\apiCheat as api;

$app->get('/logout', function() use ($app) {
    
    api\Session::logout();
    
    $app->redirect('/');
    
});