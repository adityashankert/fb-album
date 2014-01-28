<?php
require 'facebook.php';
$facebook = new Facebook(array(
  'appId'  => '585206761550147',
  'secret' => '55ef91c421258bc0246dc00f14c61f9b',
));

$user = $facebook->getUser();

if ($user) {
  try {
    $user_profile = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    error_log($e);
    $user = null;
  }
}

if ($user) {
  $logoutUrl = $facebook->getLogoutUrl();
} else {
  $statusUrl = $facebook->getLoginStatusUrl();
  $loginUrl = $facebook->getLoginUrl(array('scope' => 'user_photos'));
}

?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>Facebook Photo Album</title>
    <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/south-street/jquery-ui.css" id="theme">
	<link rel="stylesheet" href="http://blueimp.github.io/Gallery/css/blueimp-gallery.min.css">
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
	<script src="http://blueimp.github.io/Gallery/js/jquery.blueimp-gallery.min.js"></script>
	<script src="jquery.image-gallery.js"></script>
	<style>
	th, td
	{
	border: 1px solid black;
	padding:20px;
	}
	</style>
  </head>
  <body>
	 
    <?php if ($user): ?>
    <div style="float:right;">
      <a href="<?php echo $logoutUrl; ?>">Logout</a>
    </div> 
    <?php else: ?>
	<div style="padding-top: 15%;padding-left: 30%;">
        <a href="<?php echo $loginUrl; ?>"><img src="fb_login_button.png"></a>
    </div>
    <?php endif ?>

    <?php if ($user):?>
    
    <div>Welcome, <?php echo $user_profile['name'];?></div>
    <div style="height:20px;"></div>
    <table id="album_covers" style="padding-left: 20%;">
		<tr>
      <?php $albums_list = $facebook->api("/me/albums");
      $count = 0;
		foreach($albums_list['data'] as $single_album) {
			if(isset($single_album['cover_photo'])) {
				$photo = $facebook->api("/".$single_album['cover_photo']);
				echo "<td><a href='javascript:initGallery(".$single_album['id'].")'>";
				echo "<img src=".$photo['picture']."></img></a><br>".$single_album['name']."</td>";
				if(($count+1)%4==0){
					echo '</tr><tr>';
				}
				$count++;
			}
		}?> 
		</tr>
	</table>
    <?php endif ?>
	<div id="albums" style="display:none;">
		<?php
			foreach($albums_list['data'] as $single_album) {
				$photos_list = $facebook->api("/".$single_album['id']."/photos");
				echo '<div  id="'.$single_album['id'].'">';
				foreach($photos_list['data'] as $single_photo) {
					
					echo "<a href=".$single_photo['images'][0]['source']." >";
					echo "<img src=".$single_photo['images'][0]['source']."></img></a>";
				}
				echo '</div>';
			}
		
		?>
	</div>
<div id="blueimp-gallery" class="blueimp-gallery">
    <div class="slides"></div>
    <h3 class="title"></h3>
    <a class="prev">‹</a>
    <a class="next">›</a>
    <a class="close">×</a>
    <a class="play-pause"></a>
    <ol class="indicator"></ol>
</div>
<script>
	function initGallery(id) {
		$('#'+id).prop('data-dialog', true);
		document.getElementById(id).onclick = function (event) {
			event = event || window.event;
			var target = event.target || event.srcElement,
			link = target.src ? target.parentNode : target,
			options = {index: link, event: event},
			links = this.getElementsByTagName('a');
			blueimp.Gallery(links, options);			
		};
		$("#"+id).click();
	}
</script>
  </body>
</html>
