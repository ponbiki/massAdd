<?php

use ns1\apiCheat as cheat;

$app->get('/logout', function() use ($app) {

    cheat\Session::logout();

    $app->redirect('/');

});