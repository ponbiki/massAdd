<?php

namespace ns1\apiCheat;

class Session implements  iSession
{
    public function __construct()
    {
        ini_set('session.use_only_cookies', true);
        session_start();
        if (!isset($_SESSION['generated']) || $_SESSION['generated'] < (time() - 30)) {
            session_regenerate_id();
            $_SESSION['generated'] = time();
        }
        if (!array_key_exists('info', $_SESSION)) {
            $_SESSION['info'] = [];
        }
        if (!array_key_exists('error', $_SESSION)) {
            $_SESSION['error'] = [];
        }
    }
    
    public static function logout()
    {
        if (isset($_SESSION['api_key'])) {
            $_SESSION['loggedin'] = \FALSE;
            unset($_SESSION['api']);
            if (session_id() != "" || isset ($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 2592000, '/');
            }
            session_destroy();
        }
    }
    
    public static function clear()
    {
        unset($_SESSION['error']);
        unset($_SESSION['info']);
    }
}
