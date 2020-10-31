<?php

/**
 * 
 * Demo page for my own testing as well as documentation examples  
 */ 
$start = time() * 1000;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'Koss.php';
require 'config.php'; /* Using config.php so I don't leak my password */

$koss = new Koss('localhost', 3306, 'koss', 'tadhg', $password);

$results = $koss->getAll('users')->execute();
echo print_r($results);

$results = $koss->getAll('users')->when(isset($_COOKIE['limit']), fn() => $koss->limit(5))->execute();
echo print_r($results);

echo '<br>';
die('page load: ' . ((time() * 1000) - $start));