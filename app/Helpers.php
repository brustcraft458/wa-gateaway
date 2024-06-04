<?php
// app/Helpers.php

if (!function_exists('urlMainServer')) {
    function urlMainServer()
    {
        return env('APP_URL') . ($_SERVER['SERVER_PORT'] ? ":" . $_SERVER['SERVER_PORT'] : "");
    }
}