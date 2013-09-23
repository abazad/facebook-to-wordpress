<?php

define('YOUR_APP_ID', 'enter_your_app_id_here');

//uses the PHP SDK.  Download from https://github.com/facebook/php-sdk
require 'facebook/facebook.php';

$facebook = new Facebook(array(
  'appId'  => YOUR_APP_ID,
  'secret' => 'enter_your_app_secret_here',
));

$userId = $facebook->getUser();
$pages = null;

if ($userId) {
	$pages = $facebook->api('/me/accounts');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<meta http-equiv="content-type" 
		content="text/html;charset=utf-8" />
		</meta>
	<head>
		<script src="jquery.js"></script>
		<script>
 			var fbPosts = [];
			var fbPostLimit = 1000;
			var postImportLimit = 0;
			var nbPostImported = 0;
		
 		<?php
			echo "var pagesTokens = {";
			if ($userId) {
				$first = true;
				foreach($pages['data'] as $key => $value) {
					if (!$first) {
						echo ", ";
					}
					echo "'" . $value['id'] . "':'" . $value['access_token'] . "'";
					$first = false;
				}
			}
			
			echo "};";
			?>
		</script>
		</head>
  <body>
    <div id="fb-root"></div>
    <?php if ($userId) { 
	
      $userInfo = $facebook->api('/' . $userId);?>

      Welcome <?= $userInfo['name'] ?><br/>
	Please select the page to import : <select id="page">
<?php
		
	if ($userId) {
	
		foreach($pages['data'] as $key => $value) {
			?>
			<option value='<?=$value['id'] ?>'><?= $value['name'] ?></option>
					<?php
		}
}
	?>
</select>
	  <br/>
		<a href="#" onclick="displayFacebookPosts()">Display Facebook posts</a>
		<a href="#" onclick="importFacebookPosts()">Import Facebook posts</a>
    <?php } else { ?>
    <fb:login-button scope="manage_pages"></fb:login-button>
    <?php } ?>


        <div id="fb-root"></div>

		<div id="importStatus"></div>


		<div id="content"></div>
		
        <script>
          window.fbAsyncInit = function() {
            FB.init({
              appId      : '134781613340865', // App ID
              channelUrl : '//www.lablaguedumatin.net/channel.html', // Channel File
              status     : true, // check login status
              cookie     : true, // enable cookies to allow the server to access the session
              xfbml      : true  // parse XFBML
            });
        FB.Event.subscribe('auth.login', function(response) {
          window.location.reload();
        });
          };
          // Load the SDK Asynchronously
          (function(d){
             var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
             if (d.getElementById(id)) {return;}
             js = d.createElement('script'); js.id = id; js.async = true;
             js.src = "//connect.facebook.net/en_US/all.js";
             ref.parentNode.insertBefore(js, ref);
           }(document));

		function displayFacebookPosts() {
			var pageId = document.getElementById('page').value;
			loadPosts(pageId);
		}
		
		function loadPosts(pageId) {
			var graphUrl = "https://graph.facebook.com/" + pageId + "/posts";
		    var script = document.createElement("script");
		    script.src = graphUrl + "?since=start_date&limit=" + fbPostLimit + "&callback=processResult&access_token=" + pagesTokens[pageId];
			document.body.appendChild(script);
		
		}
		
		 function processResult(posts) {      
			fbPosts.length = 0;
		    for (var post in posts.data) {
				var postData = posts.data[post]; 
				fbPosts.push(postData);
				printMessage(postData.type + " - " + postData.name + " - " + postData.message, "content");
		   	}
		}
		
		function importFacebookPosts() {
		
			//get nonce
			$.getJSON('your_wordpress_blog/api/get_nonce/?controller=posts&method=create_post', function(response) {
				if (response.status == 'ok') {
					var nonce = response.nonce;
					for(var postId in fbPosts) {
						
						if (postImportLimit == 0 || nbPostImported < postImportLimit) {
							
							var post = fbPosts[postId];
							var content = post.message;
							var postType = post.type;
							
							var canImport = (postType != 'link' && postType != 'question' && content != undefined);
							
							if (canImport) {
								var isVideo = (post.type == 'video');
								var isPhoto = (post.type == 'photo');
							
								//escape quotes
								content = replaceAll(content, "'", "&apos;");
						
								if (isPhoto) {
									content = content + '\n' + "<img src=\"" + post.picture.replace('_s.jpg', '_n.jpg') + "\" class=\"alignnone\" />";
								}
								
								if (isVideo) {
									content = content + '\n' + "<embed src=\"" + post.source + "\" />";
								}
						
								content = replaceAll(content, "\n", "<br/>");
						
								$.getJSON('../bdm/api/posts/create_post', {
									'nonce' : nonce,
									'status' : 'publish',
									'title' : getTitle(post),
									'content' : content,
									//you can decide to classify your post in one or several categories
									//'categories' : 'cat1,cat2,...,catn',
									//metadata : facebook post id
			 						'meta' : {'fb_fan_page_post_id' : post.id},
									//publication date
									'date' : post.created_time
								}, function(response) {
									printMessage("Post - " + response.status, "importStatus");
								});
							}
						}
					}
				}	
			});
			
			
		}
		
 		function printMessage(msg, parentDivId) {
	        var message = document.createElement("div");
	        message.innerHTML = msg;
	       document.getElementById(parentDivId).appendChild(message);	
       }	

		function replaceAll(s, a, b) {
  			return s.replace(new RegExp(a, 'g'), b);
		}
		
		function getTitle(post) {
			var d = new Date(post.created_time);
			var day = d.getDate();
			var month = (d.getMonth()+1);
			var res = "Post - " + post.name + " - " + formatInt(day) + '/' + formatInt(month) + '/' + d.getFullYear();
	
			return res;
		}
		
		function formatInt(i) {
			return (i < 10 ? '0' : '') + i;
		}
        </script>

  </body>
</html>