<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$cas_host = getenv('CAS_HOST');
$cas_port = 443; 
$cas_context =getenv('CAS_CONTEXT');
$sco_user = getenv('SCODOC_USER');
$sco_pw = getenv('SCODOC_PASSWORD');
$sco_url = getenv('SCODOC_URL');
$http_options = array();

if(getenv("PROXY")){
    $http_options = array('proxy' => getenv('PROXY'), 'request_fulluri' => true,);
    stream_context_set_default(['http'=> $http_options]);

}

$user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; fr; rv:1.8.1) Gecko/20061010 Firefox/2.0';
