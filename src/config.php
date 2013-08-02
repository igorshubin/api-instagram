<?php

/**
 * Configuration params, make sure to write exactly the ones
 * instagram provide you at http://instagr.am/developer/
 */
$config = array(
        'client_id'     => 'a637fedefa264722a2b336a34103b89d',
        'client_secret' => 'e47e554d8892453a8a0f01207a4c5dd5',
        'grant_type'    => 'authorization_code',
        'redirect_uri'  => 'http://instagram.test.com/login.php',
        'scope'         => array( 'basic','comments','relationships','likes' ),
     );



?>
