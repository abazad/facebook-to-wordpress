facebook-to-wordpress
=====================

This is a simple PHP script to import your Facebook page status updates to your blog.
One WP post will be created for each FB status update.

Please note that you have to be the owner of the page to import it.

Pre-requisites
==============

1. Install the Wordpress JSON API plugin :
http://wordpress.org/plugins/json-api/

2. (optional) Update the plugin with the files from my fork of the API, which permits to set the date and metadata of the posts : 
https://github.com/fabienric/wp-json-api 

3. Create a developer account on Facebook 

4. Create a Facebook application with the "manage_pages" extended permission
see https://developers.facebook.com/docs/php/gettingstarted/

5. Replace the following informations in the source code :
	- enter_your_app_id_here : the app ID
	- enter_your_app_secret_here : the app secret
	- your_wordpress_blog : your blog URL
	- start_date (YYYY-MM-DD) : only the posts published at or after this date will be imported.

Installation
============

Copy the files to a folder on your web server.

Usage
=====

1. Navigate to index.php.
2. Login to your Facebook account.
3. Select your page in the list.
4. Click on "Display Facebook posts" : posts are loaded and displayed on the page.
5. Click on "Import Facebook posts" : posts are imported as entries into your blog.
