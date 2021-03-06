<?php
/*
Plugin Name: Flying Twitter 
Plugin URI: http://www.xyz.com
Description: This plugin is based on javascript code for adds an animated flying twitter bird in your random HTML elements.if you set  cursor over the bird, a "tweet-this" - and "follow-me" link appears.you can also choose a color of bird according to your site 
Author: Aman
Version: 1.0.1
Author URI: http://www.xyz.com/
*/
?>
<style>
  .blue {
    background-image: url("../wp-content/plugins/flyingtwitter/images/blue.png");
    background-position: -3px -64px;
    background-repeat: no-repeat;
    
    height: 65px;
    width: 60px;
   }
   .orange {
    background-image: url("../wp-content/plugins/flyingtwitter/images/orange.png");
    background-position: -3px -64px;
    background-repeat: no-repeat;
    
    height: 65px;
    width: 60px;
   }
  .red {
    background-image: url("../wp-content/plugins/flyingtwitter/images/red.png");
    background-position: -3px -64px;
    background-repeat: no-repeat;
    
    height: 65px;
    width: 60px;
   }
   .gray {
    background-image: url("../wp-content/plugins/flyingtwitter/images/gray.png");
    background-position: -3px -64px;
    background-repeat: no-repeat;
    
    height: 65px;
    width: 60px;
   }
.purpal {
    background-image: url("../wp-content/plugins/flyingtwitter/images/purpal.png");
    background-position: -3px -64px;
    background-repeat: no-repeat;
    
    height: 65px;
    width: 60px;
   }
.green {
    background-image: url("../wp-content/plugins/flyingtwitter/images/green.png");
    background-position: -3px -64px;
    background-repeat: no-repeat;
    
    height: 65px;
    width: 60px;
   }


</style>
<?php
function addBadgeScript() {
	if ( !is_admin() ) { 
		 
		$imaget =  get_option("color_tweet");
		if($imaget == "" || $imaget ==null)
        $imaget = 'blue';
			
	echo("
	<!-- twitter follow badge by techlineinfo.com -->
		<script src=' ".get_bloginfo('wpurl').'/wp-content/plugins/flyingtwitter/js/tripleflap.js'."'; ' type='text/javascript'></script><script type='text/javascript' charset='utf-8'><!--
			var twitterAccount = '" . get_option("animated_account") . "';
			var showTweet = " . get_option("animated_tweet") . ";
			var birdSprite=' ".get_bloginfo('wpurl').'/wp-content/plugins/flyingtwitter/images/'.$imaget.'.png'."';
			var twitterfeedreader=' ".get_bloginfo('wpurl').'/wp-content/plugins/flyingtwitter/twitterfeedreader.php'."';
			tripleflapInit();
		--></script>
	<!-- end of twitter follow badge -->");
}
}
function twitter_admin_menu() { 
	add_menu_page(
		"fly setting",
		"Flying Twitter",
		8,
		__FILE__,
		"twitter_options_page",
		get_bloginfo('wpurl').'/wp-content/plugins/flyingtwitter/images/twitterbird.png'
	); 
	//add_submenu_page(__FILE__,'olypics','Site list','8','list-site','oly_admin_list_site');
           
}

function twitter_options_page() {
	echo '<div class="wrap">';
	echo '<h2>Flying Twitter Bird </h2>';
	echo '<form method="post" action="options.php">';
  
	wp_nonce_field('update-options');
  
	echo '<table class="form-table" style="width:100%;">';
	echo '<tr valign="top">';
	echo '<td scope="row" style="width: 200px;">' . __('Twitter account', 'animated') . '</td>';
	echo '<td style="width: 430px;"><input type="text" name="animated_account" value="' . get_option('animated_account') . '" /></td>';
	echo '</tr>';
	echo '<tr><td scope="row">' . __('Display your Latest Tweet?', 'ATB') . '</td>';
	echo '<td><input type="radio" name="animated_tweet" value="true"'; 
	if(get_option('animated_tweet') == "true")
		echo ' checked';
	echo '/> ' . __('Yes', 'animated') ;
	
	echo '&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="animated_tweet" value="false"'; 
	if(get_option('animated_tweet') == "false")
		echo ' checked';
	echo '/> ' . __('No', 'animated') . '</td>';
	echo '</tr>';
        /////////////////////////////////////
        
        echo '<tr><td scope="row">' . __('Change birdcolor?', 'ATB') . '</td>';
    
	echo '<td><table><tr><td><input type="radio" name="color_tweet" value="blue"'; 
	if(get_option('color_tweet') == "blue" || get_option('color_tweet') == "" || get_option('color_tweet') == null)
		echo ' checked';
      $blue = '<div class="blue"></div><div>Nela<div>';
	echo '/> ' . __($blue,'animated') . '</td>';
	
	echo '<td><input type="radio" name="color_tweet" value="orange"'; 
	if(get_option('color_tweet') == "orange")
		echo ' checked';
         $orange = '<div class="orange"></div><div>Santri<div>';
	echo '/> ' . __($orange, 'animated') . '</td>';

        echo '<td><input type="radio" name="color_tweet" value="red"'; 
	if(get_option('color_tweet') == "red")
		echo ' checked';
          $red = '<div class="red"></div><div>Lal<div>';
	echo '/> ' . __($red, 'animated') . '</td>';
        
       echo '<td><input type="radio" name="color_tweet" value="green"'; 
	if(get_option('color_tweet') == "green")
		echo ' checked';
         $green = '<div class="green"></div><div>Harah<div>';
	echo '/> ' . __($green, 'animated') . '</td>';

        echo '<td><input type="radio" name="color_tweet" value="gray"'; 
	if(get_option('color_tweet') == "gray")
		echo ' checked';
          $gray = '<div class="gray"></div><div>Asmani<div>';
	echo '/> ' . __($gray, 'animated') . '</td>';

        echo '<td><input type="radio" name="color_tweet" value="purpal"'; 
	if(get_option('color_tweet') == "purpal")
		echo ' checked';
          $purpal = '<div class="purpal"></div><div>Bangani<div>';
	echo '/> ' . __($purpal, 'animated') . '</td>';
        
	echo '</tr></table>';
       echo '</td></tr>';


       ////////////////////////////////////////////
	echo '</table>';
	echo '<p class="submit">';	
	echo '<input type="submit" class="button-primary" value="' . __('Save Changes') . '" />';
	echo '</p>';
  
	settings_fields('AnimatedTwitterBird');
  
	echo '</form>';
	echo '</div>';
	
	addBadgeScript();
}

function twitter_register_settings() {
	register_setting('AnimatedTwitterBird', 'animated_account');
	register_setting('AnimatedTwitterBird', 'animated_tweet');
        register_setting('AnimatedTwitterBird', 'color_tweet');
      
	}

$plugin_dir = basename(dirname(__FILE__));

add_option("animated_account");
add_option("animated_tweet", "false");
add_action('wp_footer', 'addBadgeScript');

if(is_admin()){
	add_action('admin_menu', 'twitter_admin_menu');
	add_action('admin_init', 'twitter_register_settings');
}
?>
