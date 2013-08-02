<?php
@session_start();
require_once 'src/Instagram.php';

// Instantiate the API handler object
$instagram = new Instagram($config);

if (!$instagram->isLogged())
    $instagram->redirect('/');


// get params
$action  = $_POST['action'];
$id      = $_POST['id'];
$comment = $_POST['comment'];

list($id,$user_id)=  explode('_', $id);

// post data
if ($action == 'like') {
    $data = $instagram->postLike( $id );    
} else {
    $data = $instagram->postMediaComment( $id, $comment );
}
$data = json_decode($data, true);

$error = (isset($data['meta']['error_type']))? $data['meta']['error_message'] : false;

if ($error) {
    echo 'API Error: '.$error;
    exit;
}

header('Location: '.$_SERVER['HTTP_REFERER']);
exit;

    


