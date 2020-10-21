<?php

// Demo page simply for my own testing. You can ignore this

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'Koss.php';
require 'config.php';

$koss = new Koss('localhost', 3306, 'koss', 'tadhg', $password);

$results = $koss
        ->getAll('users')
        ->orderBy('first_name', 'ASC')
        ->when(5 < 10, function() use ($koss) {
                return $koss->limit(5);
            })
        ->execute();

foreach ($results as $result) {
        echo print_r($result) . '<br>';
}


// Output: SELECT `*` FROM `users` WHERE `username` <> 'Dreta' AND `username` LIKE '%Aberdeener%' ORDER BY `first_name` ASC LIMIT 5