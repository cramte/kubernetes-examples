<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'Predis/Autoloader.php';

Predis\Autoloader::register();

if (isset($_GET['cmd']) === true) {
  $host = 'redis-master';
  if (getenv('GET_HOSTS_FROM') == 'env') {
    $host = getenv('REDIS_MASTER_SERVICE_HOST');
  }
  header('Content-Type: application/json');
  if ($_GET['cmd'] == 'set') {
    $client = new Predis\Client([
      'scheme' => 'tcp',
      'host'   => $host,
      'port'   => 6379,
    ]);

    $client->set($_GET['key'], $_GET['value']);
    print('{"message": "Updated"}');
  } elseif ($_GET['cmd'] == 'stress') {
    set_time_limit(300);
    $runtime_limit = 1000 * 60;
    if (isset($_GET['len']) === true) {
        $runtime_limit = 1000 * ((int) $_GET['len']);
    }
    $limit = 10;
    if (isset($_GET['cpu']) === true) {
        $limit = ((int) $_GET['cpu']);
    }
    $begin = microtime(true)*1000;
    $current_time = $begin;
    $sleep =  1000 * (100 - $limit);
    while ($current_time - $begin < $runtime_limit) {
      usleep($sleep);
      $loop_start = microtime(true)*1000;
      while ($current_time - $loop_start < $limit) {
        $current_time = microtime(true) * 1000;
      }
    }
    print('{}');
  } else {
    $host = 'redis-slave';
    if (getenv('GET_HOSTS_FROM') == 'env') {
      $host = getenv('REDIS_SLAVE_SERVICE_HOST');
    }
    $client = new Predis\Client([
      'scheme' => 'tcp',
      'host'   => $host,
      'port'   => 6379,
    ]);

    $value = $client->get($_GET['key']);
    print('{"data": "' . $value . '"}');
  }
} else {
  phpinfo();
} ?>
