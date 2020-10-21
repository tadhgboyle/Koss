<?php

/**
 * 
 * Demo page for my own testing as well as documentation examples  
 */ 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'Koss.php';
require 'config.php'; /* Using config.php so I don't leak my password */

$koss = new Koss('localhost', 3306, 'koss', 'tadhg', $password);

$results = $koss
            ->getAll('users')
            ->orderBy('first_name', 'ASC')
            ->when(5 < 10, 
                function() use ($koss) {
                    return $koss->limit(5);
                })
            ->execute();
foreach ($results as $result) {
    echo print_r($result) . '<br>';
}

$results = $koss
               ->getSome('users', 'username')
               ->when(
                    function() {
                        return true;
                    },
                    function() use ($koss) {
                        return $koss->orderBy('id');
                    }
               )->execute();
foreach ($results as $result) {
    echo print_r($result) . '<br>';
}

$results = $koss->execute("SELECT * FROM users");
foreach ($results as $result) {
        echo print_r($result) . '<br>';
}