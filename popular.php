<?php
@session_start();
require_once 'src/Instagram.php';

// Instantiate the API handler object
$instagram = new Instagram($config);

if (!$instagram->isLogged())
    $instagram->redirect('/');


// auto refresh params
$auto = (isset($_GET['auto']))? $_GET['auto'] : false;
if ($auto == 'start') {
    $_SESSION['auto_refresh'] = true;
} else if ($auto == 'stop') {
    $_SESSION['auto_refresh'] = false;
}

$auto_refresh = (isset($_SESSION['auto_refresh']) && $_SESSION['auto_refresh'])? true : false;

if ($auto) {
    header('Location: /popular.php');
    exit;
}


// get data
$popular = $instagram->getPopularMedia();
$response = json_decode($popular, true);

//echo '<pre>';
//print_r($response);
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
           <h3>Instagram popular Media</h3>
           
            <?php require_once '_menu.php'; ?>

        </div>
       
       
       <div class="row" style="text-align: center">
           
           <?php if ($auto_refresh): ?>
                <a href="?auto=stop">[ Stop auto refresh ]</a> :: <a onclick="auto_refresh('stop')" href="javascript:void(0)">[ Stop this page ]</a>
                <div id="auto_refresh_info"></div>
           <?php else: ?>
                <a href="?auto=start">[ Start auto refresh ]</a>
           <?php endif; ?>
           
       </div>
       <div class="clearfix"></div>
       
       <div class="row">

            <?
                foreach ($response['data'] as $data) {
                    $link = $data['link'];
                    $id = $data['id'];
                    $caption = $data['caption']['text'];
                    $author = $data['caption']['from']['username'];
                    $thumbnail = $data['images']['thumbnail']['url'];
                ?>
           
                <div class="photo">
                    <a href="<?= $link ?>">
                        <img src="<?= $thumbnail ?>" alt="<?= $caption ?>" />
                    </a>
                    
                    <div class="caption">
                        <a rel="tooltip" style="cursor: pointer;display: block; padding: 0 2px;width: auto; float: left" data-original-title="<?= $caption ?>">Caption Text</a>
                        ::
                        <a target="_blank" href="/details.php?id=<?= $id ?>">Media details</a>                    
                    </div>                    
                    
                </div>

           
            <?
               }
            ?>           
           
      </div>
       <div class="clearfix"></div>
       
       <div class="row profile_wrap" style="margin-top: 30px;">
            <div style="margin-top: 5px;"><a class="pull-right" onclick="$('#feed_all').toggle()" href="javascript:void(0)">[ Raw data all ]</a></div>
            <div class="clearfix"></div>
            <div id="feed_all" style="display: none">
                <pre><?php print_r($response); ?></pre>
                
                <div style="margin-top: 5px;"><a class="pull-right" onclick="$('#feed_all').toggle()" href="javascript:void(0)">[ Raw data all ]</a></div>
           </div>
           <div class="clearfix"></div>                                
           
       </div>
       
       
      
       
   </div>
</div>

<script>

<?php if ($auto_refresh): ?>
    // auto refresh page
    var timer = 10;

    function auto_refresh( action ) {
        
        if (action == 'stop') {
            
            clearInterval( objTimer );
            $('#auto_refresh_info').html('');
            return false;
            
        } else {

            timer--;
            if (timer) {
                $('#auto_refresh_info').html('Refresh page after: '+ timer + ' seconds.');
            } else {
                clearInterval( objTimer );
                $('#auto_refresh_info').html('Refreshing ...');
                window.location.reload( true );
            }
            
        }
        
    }

    var objTimer = setInterval(auto_refresh, 1000);
    //setTimeout("auto_refresh()", 5000);
    
<?php endif ?>    


$(function(){
    $('body').tooltip({
        selector: "[rel=tooltip]",
        placement: "bottom" 
    });
});        


/*
 * infinite scroll - more results on scroll down
$(window).scroll(function() {
    if ( $(window).scrollTop() == $(document).height() - $(window).height() ) {
           // ajax call here
    }
});
*/

</script>
    
    
</body>
</html>




