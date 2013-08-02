<?php
@session_start();
require_once 'src/Instagram.php';

// Instantiate the API handler object
$instagram = new Instagram($config);
$instagram->logIn();

if (!$instagram->isLogged()) {

    // process login actions
    $instagram->openAuthorizationUrl();
    
} else {

    // user logged on
    $instagram->redirect('/profile.php');
    
}

?>


