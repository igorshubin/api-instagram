<?php
@session_start();
require_once 'src/Instagram.php';

// Instantiate the API handler object
$instagram = new Instagram($config);

if (!$instagram->isLogged())
    $instagram->redirect('/');


$sort = (isset($_GET['sort']))? $_GET['sort'] : 'date';

// get all user media
$data = $instagram->getUserFeeds('',$sort);
$count = 0;

//echo '<pre>';
//print_r($data);
//echo '</pre>';
//exit;

$nav = false;

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
           <h3>Instagram User Feeds</h3>
           
            <?php require_once '_menu.php'; ?>

        </div>
       
       
       
       
       <div class="row data_wrap">
             <h4>Feeds List</h4>
             
             <?php if (count($data)): ?>
             <?php foreach($data as $post): ?>
             <?php $count++; ?>
             
                <div class="data_line">
                    
                    <div class="row data_photo">
                       <div class="span2">
                            <a href="<?php echo $post['images']['standard_resolution']['url']; ?>">
                             <img src="<?php echo $post['images']['thumbnail']['url']; ?>" alt=""/>
                            </a>
                       </div>
                       <div class="span9">
                           <ul>
                               <li><strong>ID:</strong> <?php echo $post['id']; ?></li>
                               <li><strong>User:</strong> <?php echo $post['user']['full_name']; ?> (<?php echo $post['user']['username']; ?>)</li>
                               <li><strong>Likes:</strong> <?php echo $post['likes']['count']; ?></li>
                               <li><strong>Comments:</strong> <?php echo $post['comments']['count']; ?></li>
                           </ul>
                       </div>
                    </div>

                    <div class="row data_action">
                        <div class="pull-right">
                            <form action="/feed_post.php" method="post" id="post_form_<?php echo $post['id'] ?>">
                                <input type="hidden" name="id" value="<?php echo $post['id'] ?>">
                                <input type="hidden" name="action" id="action_<?php echo $post['id'] ?>" value="">

                                <input type="text" name="comment" id="comment_<?php echo $post['id'] ?>" value="" style="margin: 0;">
                                <a onclick="post_comment('<?php echo $post['id'] ?>');" href="javascript:void(0)" class="btn btn-mini btn-warning">Comment</a>
                                ::
                                <a onclick="post_like('<?php echo $post['id'] ?>');" href="javascript:void(0)" class="btn btn-mini btn-info">Post Like</a>
                                ::
                                <a target="_blank" href="<?php echo $post['link'] ?>" class="btn btn-mini">View Page</a>
                            </form>
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
       
       
       <?php if ($nav):  ?>
       <div class="row">
           
           <!--
           <div class="pagination pagination-centered">
           <ul>
            <?php foreach ($pages as $page):  ?>
               <li><a href="?page=<?php echo $page; ?>"><?php echo $page; ?></a></li>
            <?php endforeach  ?>
           </ul>
           </div>
           -->
           
        <ul class="pager">
            <?php if ($prev_exist): ?>
                <li><a href="?prev=<?php echo $min_id; ?>">Назад</a></li>
            <?php else : ?>
                <li class="disabled"><a href="javascript:void(0)">Назад</a></li>
            <?php endif; ?>
            
                
            <?php if ($next_exist): ?>    
                <li class=""><a href="?next=<?php echo $max_id; ?>">More feeds</a></li>
            <?php else : ?>
                <li class="disabled"><a href="javascript:void(0)">More feeds</a></li>
            <?php endif; ?>            
            
        </ul>
           
           <div class="clearfix"></div>
           
       </div>
       <?php endif ?>
       
       
       <div class="row data_wrap">
           <div style="margin-top: 5px;"><a class="pull-right" onclick="$('#feed_all').toggle()" href="javascript:void(0)">[ Raw data all ]</a></div>
           <div class="clearfix"></div>
           <div id="feed_all" class="hide"><pre><?php print_r($data); ?></pre></div>
           <div class="clearfix"></div>
       </div>
       
      
       
   </div>
</div>

    
<script>

function post_like ( id ) {
    
    if (confirm('Are you sure to like this media?')) {
        $('#action_'+id).val('like');
        $('#post_form_'+id).submit();
    }
    
    return false;
}

function post_comment ( id ) {
    
    if (!$('#comment_'+id).val()) {
        alert('Please write a comment');
        $('#comment_'+id).focus();
        return false;
    }
    
    $('#action_'+id).val('comment');
    $('#post_form_'+id).submit();
    
    return false;
}

</script>
    
</body>
</html>