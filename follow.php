<?php
@session_start();
require_once 'src/Instagram.php';

// Instantiate the API handler object
$instagram = new Instagram($config);

if (!$instagram->isLogged())
    $instagram->redirect('/');

$type = (isset($_GET['type']))? $_GET['type'] : 'Follows';

// get data
if ($type == 'Follows') {
    $data = $instagram->getUserFollows('self');
} else if ($type == 'FollowedBy') {
    $data = $instagram->getUserFollowedBy('self');
}
$data = json_decode($data, true);

$count = 0;

//echo '<pre>';
//print_r($data);
//echo '</pre>';
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
           <h3>Instagram Follows</h3>
           
            <?php require_once '_menu.php'; ?>

        </div>
       
       
       <div class="row data_wrap">
             <h4>User <?php echo $type; ?> List</h4>
             
             <?php if (count($data['data'])): ?>
             <?php foreach($data['data'] as $post): ?>
             <?php $count++; ?>
             
                <div class="data_line">
                    
                    <div class="row data_photo">
                       <div class="span2">
                           <a target="_blank" href="http://instagram.com/<?php echo $post['username'] ?>">
                                <img src="<?php echo $post['profile_picture']; ?>" alt=""/>
                           </a>
                       </div>
                       <div class="span9">
                           <ul>
                               <li><strong>ID:</strong> <?php echo $post['id']; ?></li>
                               <li><strong>Username:</strong> <?php echo $post['username']; ?></li>
                               <li><strong>Full Name:</strong> <?php echo $post['full_name']; ?></li>
                               <li><strong>Bio:</strong> <?php echo $post['bio']; ?></li>
                           </ul>
                       </div>
                    </div>

                    <div class="row data_action">
                        <div class="pull-right">
                            <a target="_blank" href="http://instagram.com/<?php echo $post['username'] ?>" class="btn btn-mini">View Page</a>
                        </div> 
                    </div>
                    
                    <div class="row data_raw">
                        <a class="pull-right" onclick="$('#post_<?php echo $post['id']; ?>').slideToggle()" href="javascript:void(0)">[ Raw data ]</a>
                        <div class="clearfix"></div>
                        <div id="post_<?php echo $post['id']; ?>" class="hide"><pre><?php print_r($post); ?></pre></div>
                        <div class="clearfix"></div>
                    </div>                    
                    
                </div>
             
             <?php endforeach ?>
             <?php endif; ?>
           
      </div>
       
       <div class="row data_wrap">
            <div style="margin-top: 5px;"><a class="pull-right" onclick="$('#feed_all').toggle()" href="javascript:void(0)">[ Raw data all ]</a></div>
            <div class="clearfix"></div>
            <div id="feed_all" class="hide"><pre><?php print_r($data); ?></pre></div>
            <div class="clearfix"></div>
       </div>
       
      
       
   </div>
</div>

    
</body>
</html>