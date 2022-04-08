
<?php
require_once "config.php";
require_once 'connexion.php';
//phpCAS::logout();
session_start();

// Détruit toutes les variables de session
$_SESSION = array();


if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_unset() ; 

session_destroy();
echo " <h1>Merci de votre visite. </h1><h3>Pensez à fermer votre navigateur en partant et ne jamais enregistrer votre mot de passe.</h3>"
?>
