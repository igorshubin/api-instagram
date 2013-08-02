<?php
@session_start();
require_once 'src/Instagram.php';

// Instantiate the API handler object
$instagram = new Instagram($config);

if ($instagram->isLogged())
    $instagram->redirect('/profile.php');

?>

<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Instagram API</title>

<link rel="stylesheet" href="/css/reset.css" type="text/css" />
<link rel="stylesheet" href="/css/bootstrap.css" type="text/css" />
<link rel="stylesheet" href="/css/style.css" type="text/css" />

<script src="/js/jquery.js"></script>
<script src="/js/bootstrap.js"></script>

</head>


<body>

<div id="main" class="container">
   <div class="content">    
    
        <div class="page-header">
            <h3>Instagram API</h3>
            <a href="/login.php" class="btn primary large">Login To Instagram</a>
        </div>
       
       <div class="row span3 well" style="margin-left: 0">
          
           Please accept all permission requests.
          
      </div>       
       
            
   </div>       
</div>
       
</body>
</html>