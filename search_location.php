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
$error = false;
$result = false;
$json = array();

if (isset($_POST['keyword']) || isset($_GET['keyword'])) {

    $search = true;
    $keyword = (isset($_POST['keyword']))? $_POST['keyword'] : $_GET['keyword'];
    $keyword = trim(strip_tags($keyword));
    $distance = (isset($_POST['distance']))? $_POST['distance'] : $_GET['distance'];
    if (!$distance) $distance = 1000;
    //$next    = (isset($_GET['next']))? $_GET['next'] : '';

    if ($keyword) {
    
        // 1. get coords by address
        require_once 'src/GoogleMap.php';        
        $googleMapApi = new GoogleMapApi();
        $coords = $googleMapApi->getCoords( $keyword );

        if (is_array($coords)) {
            
            // 2. search media by lat and lng
            $data = $instagram->mediaSearch($coords['lat'], $coords['lng'], '', '', $distance);
            $data = json_decode($data, true);
            
            $error = (isset($data['meta']['error_type']))? $data['meta']['error_message'] : false;
            
            if (!$error && count($data['data'])) {
                $result = true;
                $json = array(
                    'lat' => $coords['lat'],
                    'lng' => $coords['lng'],
                    'title' => $keyword,
                    'distance' => intval($distance),
                );
            }
                
            
        } else {
            $error = 'No coords fround for location: ['.$keyword.']';
        }

    }
    
}

if ($search) {
//    echo '<pre>';
//    print_r($data);
//    exit;
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

<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>

</head>

   
<body>
    
<div id="main" class="container">
   <div class="content">
       
        <div class="page-header">
           <h3>Instagram Search</h3>
           
            <?php require_once '_menu.php'; ?>

        </div>
       
       
       <div class="row data_wrap">
           
           <div class="span4">
                <form method="post" action="" onsubmit="return tag_search()">
                 <h4>Search media by location</h4>
                 <div>
                     <input placeholder="Enter location" type="text" name="keyword" id="keyword" value=""> 
                 </div>

                 <div>
                     With distance
                     <select name="distance" style="width: 90px;">
                         <option value="10">10</option>
                         <option value="100">100</option>
                         <option value="500">500</option>
                         <option value="1000" selected="selected">1000</option>
                         <option value="2000">2000</option>
                         <option value="3000">3000</option>
                         <option value="4000">4000</option>
                         <option value="5000">5000</option>
                     </select>
                     meters
                 </div>

                 <div style="margin-top: 20px;">
                     <input type="submit" class="btn" value="Search"> &nbsp; 
                     <input type="button" onclick="location.href='<?php echo $_SERVER['SCRIPT_NAME']; ?>'" class="btn" value="Clear Results">
                 </div>
                </form>
           </div>
           
           <?php if ($result): ?>
            <div class="span3 offset2">
                <div id="google_map"></div>
            </div>
           <?php endif; ?>
           
           
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
                                   <a target="_blank" href="<?php echo $post['link'] ?>">
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
                                  <a target="_blank" href="<?php echo $post['link'] ?>" class="btn btn-mini">Page Link</a>
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
    
function tag_search () {

    if (!$('#keyword').val()) {
        alert('Please type location');
        $('#keyword').focus();
        return false;
    }
    return true;
}
    

// init google map
<?php if ($result): ?>

<?php echo 'map_init('.json_encode($json).')'."\n"; ?>

function map_init( objParam ) {
    
    // create map
    var myCoords = new google.maps.LatLng(objParam.lat, objParam.lng);
    
    // get zoom depending on distance
    var myZoom = (objParam.distance <= 500)? 14 : 11;
    
    var myOptions = {
        zoom: myZoom,
        center: myCoords,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    var map = new google.maps.Map(document.getElementById("google_map"), myOptions); 
    
    // show marker
    var marker = new google.maps.Marker({
        position: myCoords,
        map: map,
        title: objParam.title
    });
    
    // show circle
    var circleOptions = {
        strokeColor: "#fef645",
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: "#fef645",
        fillOpacity: 0.20,
        map: map,
        center: myCoords,
        radius: objParam.distance
    };

    var circle = new google.maps.Circle(circleOptions);
    
}
    
<?php endif; ?>    
    
</script>
    
    
</body>
</html>