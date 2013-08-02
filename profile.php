<?php
@session_start();
require_once 'src/Instagram.php';

// Instantiate the API handler object
$instagram = new Instagram($config);

if (!$instagram->isLogged())
    $instagram->redirect('/');

// get data - from session
$user = $instagram->getCurrentUser();

// get data - from api
$user = $instagram->getUser( $user->id );
$user = get_object_vars( json_decode($user)->data );
extract($user);


//var_dump($user);
//exit;

//echo '<pre>';
//print_r($user);
//exit;

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
           <h3>Instagram User Profile</h3>
           
            <?php require_once '_menu.php'; ?>
           
        </div>
       
       
       
       <div class="row data_wrap">
             <h4><?php echo $full_name; ?>'s Profile</h4>
             
                <div class="data_line">
                    
                    <div class="row data_photo">
                       <div class="span2">
                            <img src="<?php echo $profile_picture; ?>" alt="<?php echo $id; ?>"/>
                       </div>
                       <div class="span9">
                           <ul>
                               <li><strong>ID:</strong> <?php echo $id; ?></li>
                               <li><strong>Username:</strong> <?php echo $username; ?></li>
                               <li><strong>Full Name:</strong> <?php echo $full_name; ?></li>
                               <li><strong>Bio:</strong> <?php echo ($bio)? $bio : '&nbsp;'; ?></li>
                               <li><strong>Website:</strong> <?php echo ($website)? $website : '&nbsp'; ?></li>
                               <li><strong>Media count:</strong> <?php echo $counts->media; ?></li>
                           </ul>
                       </div>
                    </div>

                    <div class="row data_action">
                        <div class="pull-right">
                            <a target="_blank" href="https://instagram.com/<?php echo $username; ?>/" class="btn btn-mini">View Page</a>
                        </div> 
                    </div>
                    
                    <div class="row data_raw">
                        <a class="pull-right" onclick="$('#post_<?php echo $id; ?>').slideToggle()" href="javascript:void(0)">[ Raw data ]</a>
                        <div class="clearfix"></div>
                        <div id="post_<?php echo $id; ?>" class="hide"><pre><?php print_r($user); ?></pre></div>
                        <div class="clearfix"></div>
                    </div>                    
                    
                </div>
             
      </div>       
       
       
       
   </div>
</div>
    
    
</body>
</html>