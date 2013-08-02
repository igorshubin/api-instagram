<?php
//@session_start();
require_once 'src/Instagram.php';

// Instantiate the API handler object
$instagram = new Instagram($config);
$instagram->logOut( '/' );



