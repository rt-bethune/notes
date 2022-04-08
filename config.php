<?php

include_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$cas_host = $_ENV['CAS_HOST'];
$cas_port = 443; 
$cas_context =$_ENV['CAS_CONTEXT'];
$sco_user = $_ENV['SCODOC_USER'];
$sco_pw = $_ENV['SCODOC_PASSWORD'];
$sco_url = $_ENV['SCODOC_URL'];
$http_options = array();

if(isset($_ENV["PROXY"])){
    $http_options = array('proxy' => $_ENV['PROXY'], 'request_fulluri' => true,);
    stream_context_set_default(['http'=> $http_options]);

}

$user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; fr; rv:1.8.1) Gecko/20061010 Firefox/2.0';
