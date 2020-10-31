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
            ->where('first_name', '!=', 'Tadhg')
            ->limit(5)
            ->execute();
foreach ($results as $result) {
    echo print_r($result) . '<br>';
}

echo '<br>';

$results = $koss
               ->getSome('users', 'username')
               ->when(
                    fn() => true,
                    fn() => $koss->orderBy('id', 'ASC')
               )
               ->limit(5)
               ->execute();
foreach ($results as $result) {
    echo print_r($result) . '<br>';
}

echo '<br>';

$results = $koss->execute("SELECT * FROM users WHERE users.id <= 4");
foreach ($results as $result) {
        echo print_r($result) . '<br>';
}