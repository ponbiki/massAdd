<?php

$app->post('/rdnsSearch', function () use ($app) {
    
    if ((!array_key_exists('loggedin', $_SESSION)) || ($_SESSION['loggedin'] !== \TRUE)) {
        $app->redirect('/');
    }
        
   
    $_SESSION['api']->getMatches($clean_answer);
    
    if (isset($_SESSION['api']->matches_array)) {
        $_SESSION['info'][] = "Found ". \count($_SESSION['api']->matches_array) . " records containing {$_SESSION['api']->search_answer}";
        $app->redirect('/results');
    } else {
        $app->redirect('/menu');
    }
    
});