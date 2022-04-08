<?php
require_once "config.php";
$debug = $_ENV['APP_DEBUG'];
if ($_ENV['APP_FAKE']) {
    $nip = "22005039"; //22003409; 22101695
} else {
    //For development. Prints out additional warnings.
    if ($debug) {
        phpCAS::setDebug();
        phpCAS::setVerbose(true);
    }

    phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context, true);
    if ($http_options["proxy"])
        phpCAS::setExtraCurlOption(CURLOPT_PROXY, str_replace('tcp://', '', $http_options["proxy"]));

    //no SSL validation for the CAS server
    phpCAS::setNoCasServerValidation();
    phpCAS::forceAuthentication();

    $uid = phpCAS::getUser();
    $ds = ldap_connect(getenv('LDAP_HOST'));  // doit être un serveur LDAP valide !
    $r = ldap_bind($ds, getenv('LDAP_USER'), getenv('LDAP_PASSWORD'));
    $sr = ldap_search($ds, getenv('LDAP_SEARCH'), "(uid=$uid)");

    $info = ldap_get_entries($ds, $sr);
    $nip = $info[0]["uidnumber"][0];
}