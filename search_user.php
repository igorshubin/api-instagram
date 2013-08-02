<?php
@session_start();
require_once 'src/Instagram.php';

// Instantiate the API handler object
$instagram = new Instagram($config);

if (!$instagram->isLogged())
    $instagram->redirect('/');

// get current user info
$user = $instagram->getCurrentUser();
$user = get_object_vars($user);
extract($user);

// get data
$data = array();
$search = false;

if (isset($_POST['tag']) || isset($_GET['tag'])) {
    
    $search = true;
    $keyword = (isset($_POST['tag']))? $_POST['tag'] : $_GET['tag'];
    $keyword = trim(strip_tags($keyword));
    //$next    = (isset($_GET['next']))? $_GET['next'] : '';
    
    if ($keyword) {
        $data = $instagram->searchUser($keyword);
        $data = json_decode($data, true);        
    }
    
}

$error = (isset($data['meta']['error_type']))? $data['meta']['error_message'] : false;

//$user = get_object_vars($user);
//extract($user);

if ($search) {
//echo '<pre>';
//print_r($data);
//exit;
}


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
           <h3>Instagram Search</h3>
           
            <?php require_once '_menu.php'; ?>

        </div>
       
       
       <div class="row data_wrap">
           
            <form method="post" action="" onsubmit="return user_search()">
             <h4>Search by username</h4>
             <div>
                 <input type="text" name="tag" id="tag" value="">
             </div>
             <div>
                 <input type="submit" class="btn" value="Search"> &nbsp; 
                 <input type="button" onclick="location.href='<?php echo $_SERVER['SCRIPT_NAME']; ?>'" class="btn" value="Clear Results">
             </div>
            </form>
           
        </div>
           
           
           <?php if ($search && $error): ?>
            <div class="row data_wrap data_error">
                <h4>API Error</h4>
                <?php echo $error; ?>
           </div>
           <?php endif;  ?>
           
           
        <?php if ($search && !$error && isset($data['data'])): ?>
           
            <div class="row data_wrap">
                  <h4>Search results</h4>

                  <?php if (count($data['data'])): ?>
                    <?php foreach($data['data'] as $post): ?>

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
                  <?php else: ?>
                  
                    <strong>No results found.</strong>
                  
                  <?php endif; ?>

             </div>
           
           <?php endif; ?>
           
           
       
       
       <?php if (isset($data['pagination']['next_max_tag_id'])):  ?>
        <div class="row data_pager">
             <ul class="pager">
                 <li class=""><a href="?tag=<?php echo $keyword; ?>&next=<?php echo $data['pagination']['next_max_tag_id']; ?>">More results</a></li>
             </ul>
            <div class="clearfix"></div>
        </div>
       <?php endif ?>       
       
       
       <div class="row data_wrap">
            <div style="margin-top: 5px;"><a class="pull-right" onclick="$('#feed_all').toggle()" href="javascript:void(0)">[ Raw data all ]</a></div>
            <div class="clearfix"></div>
            <div id="feed_all" style="display: none"><pre><?php print_r($data); ?></pre></div>
           <div class="clearfix"></div>                                
       </div>       
      
       
   </div>
</div>
    
<script> 
    
    function user_search () {
        
        if (!$('#tag').val()) {
            alert('Please type username');
            $('#tag').focus();
            return false;
        }
        return true;
    }
    
</script>
    
    
</body>
</html>