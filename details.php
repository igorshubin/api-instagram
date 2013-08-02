<?php
@session_start();
require_once 'src/Instagram.php';

// Instantiate the API handler object
$instagram = new Instagram($config);

if (!$instagram->isLogged())
    $instagram->redirect('/');

$id = (isset($_GET['id']))? $_GET['id'] : false;

// get all user media
$data = $instagram->getMedia($id);
$data = json_decode($data, true);

//echo '<pre>';
//print_r($data);
//echo '</pre>';
//exit;


/*
require_once './GoogleMap/Api.php';
$GoogleMapApi = new GoogleMapApi();
$strLocation = 'World Trade Center (WTC), Boston, MA 02210, USA';
$GoogleMapApi->getLocations($strLocation);
exit;
*/

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
           <h3>Instagram Media Details</h3>
           
            <?php require_once '_menu.php'; ?>

        </div>
       
       
       <div class="row data_wrap">
           
           <div id="info">
               <h3>Meta</h3>  
               
                <table width="100%" border="0">
                  <tr>
                    <td style="width: 100px">
                        <strong>Date:</strong>
                    </td>
                    <td>
                        <?php echo date('d M Y h:i:s', $data['data']['created_time']); ?>
                    </td>
                  </tr>
                  <tr>
                    <td>
                        <strong>Creator:</strong>
                    </td>
                    <td>
                        <?php echo $data['data']['user']['username']; ?>
                        (<?php echo!empty($data['data']['user']['full_name']) ? $data['data']['user']['full_name'] : 'Not specified';   ?>)
                    </td>
                  </tr>
                  <tr>
                    <td>
                        <strong>Location:</strong>
                    </td>
                    <td>
                        <?php echo!is_null($data['data']['location']) ? $data['data']['location']['latitude'] . ',' . $data['data']['location']['longitude'] : 'Not specified'; ?>
                    </td>
                  </tr>
                  <tr>
                    <td>
                        <strong>Filter:</strong>
                    </td>
                    <td>
                        <?php echo $data['data']['filter']; ?>
                    </td>
                  </tr>
                  <tr>
                    <td>
                        <strong>Comments:</strong>
                    </td>
                    <td>
                        <?php echo $data['data']['comments']['count']; ?>
                    </td>
                  </tr>
                  <tr>
                    <td>
                        <strong>Likes:</strong>
                    </td>
                    <td>
                        <?php echo $data['data']['likes']['count']; ?>
                    </td>
                  </tr>
                  <tr>
                    <td>
                        <strong>Resolution:</strong>
                    </td>
                    <td>
                        <a href="<?php echo $data['data']['images']['standard_resolution']['url'];
                           ?>">Standard</a> | 
                        <a href="<?php echo $data['data']['images']['thumbnail']['url'];
                           ?>">Thumbnail</a>

                    </td>
                  </tr>
                  <tr>
                    <td>
                        <strong>Tags:</strong>
                    </td>
                    <td>
                        <?php echo implode(', ', $data['data']['tags']); ?>
                    </td>
                  </tr>
                </table>

           </div>
           
            <div id="image">
              <h3>Image</h3>  
              <a target="_blank" href="<?php echo $data['data']['link']; ?>">
               <img src="<?php echo $data['data']['images']['low_resolution']['url']; ?>" />
              </a>
            </div>

            <div id="comments">
              <?php if ($data['data']['comments']['count']): ?>
              <h3>Comments</h3>
                <ul>
                    <?php foreach ($data['data']['comments']['data'] as $c): ?>
                        <div class="item"><img src="<?php echo $c['from']['profile_picture']; ?>" class="profile" />
                            <?php echo $c['text']; ?> <br/>
                            By <em> <?php echo $c['from']['username']; ?></em> 
                            on <?php echo date('d M Y h:i:s', $c['created_time']); ?>
                        </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>      

           
      </div>
       
       
       <div class="row data_wrap">
            <div style="margin-top: 5px;"><a class="pull-right" onclick="$('#feed_all').toggle()" href="javascript:void(0)">[ Raw data ]</a></div>
            <div class="clearfix"></div>
            <div id="feed_all" style="display: none">
                <pre><?php print_r($data); ?></pre>
           </div>
           <div class="clearfix"></div>                                
       </div>
       
      
       
   </div>
</div>

    
</body>
</html>