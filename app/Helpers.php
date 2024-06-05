<?php
// app/Helpers.php

if (!function_exists('urlMainServer')) {
    function urlMainServer()
    {
        $url = env('APP_URL');
        $port = (($_SERVER['SERVER_PORT'] != '80' && $_SERVER['SERVER_PORT'] != '443') ? $_SERVER['SERVER_PORT'] : '');
        
        return  $url . ($port != '' ? ':' : '' ) . $port;
    }
}