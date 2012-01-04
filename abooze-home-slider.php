<?php
/*
Plugin Name: Aboozé Slideshow
Plugin URI: http://wordpress.org/extend/plugins/abooze-slideshow/
Description: Easily upload images with links to display a nice slideshow on your website. To manage, Go to <strong>Media-> Aboozé Slideshow</strong>. To display the slideshow, add the shortcode: <code><?php if (function_exists('ab_show')){ ab_show(); }?> </code> in your template.
Version: 2.0
Author: Aboobacker Omar
Author URI: http://www.aboobacker.com/

This plugin inherits the GPL license from it's parent system, WordPress., customized from WP-Cycle. Thanks Nathan Rice.
*/

$wp_cycle_defaults = apply_filters('wp_cycle_defaults', array(
	'rotate' => 1,
	'effect' => 'fade', // fade, wipe, scrollUp, scrollDown, scrollLeft, scrollRight, cover, shuffle
	'delay' => 3,
	'duration' => 1,
	'img_width' => 900,
	'img_height' => 500,
	'div' => 'slideShowItems'
));

//	pull the settings from the db
$wp_cycle_settings = get_option('wp_cycle_settings');
$wp_cycle_images = get_option('wp_cycle_images');

//	fallback
$wp_cycle_settings = wp_parse_args($wp_cycle_settings, $wp_cycle_defaults);


//	this function registers our settings in the db
add_action('admin_init', 'wp_cycle_register_settings');
function wp_cycle_register_settings() {
	register_setting('wp_cycle_images', 'wp_cycle_images', 'wp_cycle_images_validate');
	register_setting('wp_cycle_settings', 'wp_cycle_settings', 'wp_cycle_settings_validate');
}


//	this function adds the settings page to the Appearance tab
add_action('admin_menu', 'add_wp_cycle_menu');
function add_wp_cycle_menu() {
	add_submenu_page('upload.php', 'Aboozé Slideshow Settings', 'Aboozé Slideshow', 'upload_files', 'abooze-slideshow', 'wp_cycle_admin_page');
}

//	add "Settings" link to plugin page
add_filter('plugin_action_links_' . plugin_basename(__FILE__) , 'wp_cycle_plugin_action_links');
function wp_cycle_plugin_action_links($links) {
	$wp_cycle_settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'upload.php?page=abooze-slideshow' ), __('Settings') );
	array_unshift($links, $wp_cycle_settings_link);
	return $links;
}

function wp_cycle_admin_page() {
	echo '<div class="wrap">';
	
		//	handle image upload, if necessary
		if($_REQUEST['action'] == 'wp_handle_upload')
			wp_cycle_handle_upload();
		
		//	delete an image, if necessary
		if(isset($_REQUEST['delete']))
			wp_cycle_delete_upload($_REQUEST['delete']);
		
		//	the image management form
		wp_cycle_images_admin();
		
		//	the settings management form
		//wp_cycle_settings_admin();

	echo '</div>';
}

function wp_cycle_handle_upload() {
	global $wp_cycle_settings, $wp_cycle_images;
	
	//	upload the image
	$upload = wp_handle_upload($_FILES['wp_cycle'], 0);
	
	//	extract the $upload array
	extract($upload);
	
	//	the URL of the directory the file was loaded in
	$upload_dir_url = str_replace(basename($file), '', $url);
	
	//	get the image dimensions
	list($width, $height) = getimagesize($file);
	
	//	if the uploaded file is NOT an image
	if(strpos($type, 'image') === FALSE) {
		unlink($file); // delete the file
		echo '<div class="error" id="message"><p>Sorry, but the file you uploaded does not seem to be a valid image. Please try again.</p></div>';
		return;
	}
	
	//	if the image doesn't meet the minimum width/height requirements ...
	if($width < $wp_cycle_settings['img_width'] || $height < $wp_cycle_settings['img_height']) {
		unlink($file); // delete the image
		echo '<div class="error" id="message"><p>Sorry, but this image does not meet the minimum height/width requirements. Please upload another image</p></div>';
		return;
	}
	
	//	if the image is larger than the width/height requirements, then scale it down.
	if($width > $wp_cycle_settings['img_width'] || $height > $wp_cycle_settings['img_height']) {
		//	resize the image
		$resized = image_resize($file, $wp_cycle_settings['img_width'], $wp_cycle_settings['img_height'], true, 'resized');
		$resized_url = $upload_dir_url . basename($resized);
		//	delete the original
		unlink($file);
		$file = $resized;
		$url = $resized_url;
	}
	
	//	make the thumbnail
	$thumb_height = round((100 * $wp_cycle_settings['img_height']) / $wp_cycle_settings['img_width']);
	if(isset($upload['file'])) {
		$thumbnail = image_resize($file, 100, $thumb_height, true, 'thumb');
		$thumbnail_url = $upload_dir_url . basename($thumbnail);
	}
	
	//	use the timestamp as the array key and id
	$time = date('YmdHis');
	
	//	add the image data to the array
	$wp_cycle_images[$time] = array(
		'id' => $time,
		'file' => $file,
		'file_url' => $url,
		'thumbnail' => $thumbnail,
		'thumbnail_url' => $thumbnail_url,
		'image_links_to' => ''
	);
	
	//	add the image information to the database
	$wp_cycle_images['update'] = 'Added';
	update_option('wp_cycle_images', $wp_cycle_images);
}

//	this function deletes the image,
//	and removes the image data from the db
function wp_cycle_delete_upload($id) {
	global $wp_cycle_images;
	
	//	if the ID passed to this function is invalid,
	//	halt the process, and don't try to delete.
	if(!isset($wp_cycle_images[$id])) return;
	
	//	delete the image and thumbnail
	unlink($wp_cycle_images[$id]['file']);
	unlink($wp_cycle_images[$id]['thumbnail']);
	
	//	indicate that the image was deleted
	$wp_cycle_images['update'] = 'Deleted';
	
	//	remove the image data from the db
	unset($wp_cycle_images[$id]);
	update_option('wp_cycle_images', $wp_cycle_images);
}

function wp_cycle_settings_update_check() {
	global $wp_cycle_settings;
	if(isset($wp_cycle_settings['update'])) {
		echo '<div class="updated fade" id="message"><p>Aboozé Slideshow Settings <strong>'.$wp_cycle_settings['update'].'</strong></p></div>';
		unset($wp_cycle_settings['update']);
		update_option('wp_cycle_settings', $wp_cycle_settings);
	}
}
//	this function checks to see if we just added a new image
//	if so, it displays the "updated" message.
function wp_cycle_images_update_check() {
	global $wp_cycle_images;
	if($wp_cycle_images['update'] == 'Added' || $wp_cycle_images['update'] == 'Deleted' || $wp_cycle_images['update'] == 'Updated') {
		echo '<div class="updated fade" id="message"><p>Image(s) '.$wp_cycle_images['update'].' Successfully</p></div>';
		unset($wp_cycle_images['update']);
		update_option('wp_cycle_images', $wp_cycle_images);
	}
}

function wp_cycle_images_admin() { ?>
	<?php global $wp_cycle_images; ?>
	<?php wp_cycle_images_update_check(); ?>
	<h2><?php _e('Home Slideshow Images', 'wp_cycle'); ?></h2>
	
	<table class="form-table">
		<tr valign="top"><th scope="row">Upload New Image</th>
			<td>
			<form enctype="multipart/form-data" method="post" action="?page=abooze-slideshow">
				<input type="hidden" name="post_id" id="post_id" value="0" />
				<input type="hidden" name="action" id="action" value="wp_handle_upload" />
				
				<label for="wp_cycle">Select a File: </label>
				<input type="file" name="wp_cycle" id="wp_cycle" />
				<input type="submit" class="button-primary" name="html-upload" value="Upload" />
			</form>
			</td>
		</tr>
	</table><br />
	
	<?php if(!empty($wp_cycle_images)) : ?>
	<table class="widefat fixed" cellspacing="0">
		<thead>
			<tr>
				<th scope="col" class="column-slug">Image</th>
				<th scope="col">Image Links To</th>
				<th scope="col" class="column-slug">Actions</th>
			</tr>
		</thead>
		
		<tfoot>
			<tr>
				<th scope="col" class="column-slug">Image</th>
				<th scope="col">Image Links To</th>
				<th scope="col" class="column-slug">Actions</th>
			</tr>
		</tfoot>
		
		<tbody>
		
		<form method="post" action="options.php">
		<?php settings_fields('wp_cycle_images'); ?>
		<?php foreach((array)$wp_cycle_images as $image => $data) : ?>
			<tr>
				<input type="hidden" name="wp_cycle_images[<?php echo $image; ?>][id]" value="<?php echo $data['id']; ?>" />
				<input type="hidden" name="wp_cycle_images[<?php echo $image; ?>][file]" value="<?php echo $data['file']; ?>" />
				<input type="hidden" name="wp_cycle_images[<?php echo $image; ?>][file_url]" value="<?php echo $data['file_url']; ?>" />
				<input type="hidden" name="wp_cycle_images[<?php echo $image; ?>][thumbnail]" value="<?php echo $data['thumbnail']; ?>" />
				<input type="hidden" name="wp_cycle_images[<?php echo $image; ?>][thumbnail_url]" value="<?php echo $data['thumbnail_url']; ?>" />
				<th scope="row" class="column-slug"><img src="<?php echo $data['thumbnail_url']; ?>" /></th>
				<td><input type="text" name="wp_cycle_images[<?php echo $image; ?>][image_links_to]" value="<?php echo $data['image_links_to']; ?>" size="35" /></td>
				<td class="column-slug"><input type="submit" class="button-primary" value="Update" /> <a href="?page=abooze-slideshow&amp;delete=<?php echo $image; ?>" class="button">Delete</a></td>
			</tr>
		<?php endforeach; ?>
		<input type="hidden" name="wp_cycle_images[update]" value="Updated" />
		</form>
		
		</tbody>
	</table>
	<?php endif; ?>

<?php
}

function wp_cycle_settings_validate($input) {
	$input['rotate'] = ($input['rotate'] == 1 ? 1 : 0);
	$input['effect'] = wp_filter_nohtml_kses($input['effect']);
	$input['img_width'] = intval($input['img_width']);
	$input['img_height'] = intval($input['img_height']);
	$input['div'] = wp_filter_nohtml_kses($input['div']);
	
	return $input;
}
//	this function sanitizes our image data for storage
function wp_cycle_images_validate($input) {
	foreach((array)$input as $key => $value) {
		if($key != 'update') {
			$input[$key]['file_url'] = clean_url($value['file_url']);
			$input[$key]['thumbnail_url'] = clean_url($value['thumbnail_url']);
			
			if($value['image_links_to'])
			$input[$key]['image_links_to'] = clean_url($value['image_links_to']);
		}
	}
	return $input;
}

function ab_show($args = array(), $content = null) {
	global $wp_cycle_settings, $wp_cycle_images;
	
	// possible future use
	$args = wp_parse_args($args, $wp_cycle_settings);
	
	$newline = "\n"; // line break
	
	echo '<div id="slideShow"><div id="'.$wp_cycle_settings['div'].'">'.$newline;
	
	foreach((array)$wp_cycle_images as $image => $data) {
		if($data['image_links_to'])
		echo '<a href="'.$data['image_links_to'].'">';
		
		echo '<div><img src="'.$data['file_url'].'" width="'.$wp_cycle_settings['img_width'].'" height="'.$wp_cycle_settings['img_height'].'" class="'.$data['id'].'" alt="" /></div>';
		
		if($data['image_links_to'])
		echo '</a>';
		
		echo $newline;
	}
	
	echo '</div></div>'.$newline;
}

//	create the shortcode [wp_cycle]
add_shortcode('ab_show', 'wp_cycle_shortcode');
function wp_cycle_shortcode($atts) {
	
	// Temp solution, output buffer the echo function.
	ob_start();
	ab_show();
	$output = ob_get_clean();
	
	return $output;
	
}

add_action( 'wp_head', 'wp_cycle_style' );
function wp_cycle_style() { 
	global $wp_cycle_settings;
?>
	
<style type="text/css" media="screen">
	#<?php echo $wp_cycle_settings['div']; ?> {
		position: relative;
		width: <?php echo $wp_cycle_settings['img_width']; ?>px;
		height: <?php echo $wp_cycle_settings['img_height']?>px;
		margin: 0; padding: 0;
		overflow: hidden;
	}
</style>
	
<?php } 
add_action('wp_head', 'abooze_slideshow_script');
function abooze_slideshow_script(){ 
 ?>
<script type="text/javascript">
$(document).ready(function() {
	$('#slideShowItems div').hide().css({position:'absolute',width:'900px'});

var currentSlide = -1;
var prevSlide = null;
var slides = $('#slideShowItems div');
var interval = null;
var FADE_SPEED = 500;
var DELAY_SPEED = 15000;

var html = '<ul id="slideShowCount">'

for (var i = slides.length - 1;i >= 0 ; i--){
	html += '<li id="slide'+ i+'" class="slide"><span>'+(i+1)+'</span></li>' ;
}

html += '</ul>';
$('#slideShow').after(html);

for (var i = slides.length - 1;i >= 0 ; i--){
	$('#slide'+i).bind("click",{index:i},function(event){
		currentSlide = event.data.index;
		gotoSlide(event.data.index);
	});
};

if (slides.length <= 1){
	$('.slide').hide();
}

nextSlide();

function nextSlide (){

	if (currentSlide >= slides.length -1){
		currentSlide = 0;
	}else{
		currentSlide++
	}

	gotoSlide(currentSlide);

}

function gotoSlide(slideNum){

	if (slideNum != prevSlide){

		if (prevSlide != null){
			$(slides[prevSlide]).stop().hide();
			$('#slide'+prevSlide).removeClass('selectedTab');
		}

		$('#slide'+currentSlide).addClass('selectedTab');


		$('#slide'+slideNum).addClass('selectedTab');
		$('#slide'+prevSlide).removeClass('selectedTab');

		$(slides[slideNum]).stop().fadeIn(FADE_SPEED,function(){
			$(this).css({opacity:1});
			if(jQuery.browser.msie){
				this.style.removeAttribute('filter');
			}
		});

		prevSlide = currentSlide;

		if (interval != null){
			clearInterval(interval);
		}
		interval = setInterval(nextSlide, DELAY_SPEED);
	}

}
$('ul#slideShowCount li.slide:first').addClass('fs_li');
$('ul#slideShowCount li.slide:last').addClass('ls_li');
});

</script>
<style type="text/css">
/* home slideshow css */
div#slideShowItems{
    height:500px;
    overflow:hidden;
    position:relative;
}

div#slideShowItems div{
    width:900px;
}


div#slideShowItems img {
    margin-right:13px;
    float:left;
}

ul#slideShowCount{
    margin:0px;
    padding:0px;
    width:900px;
    margin: 0 auto;
}
ul#slideShowCount li.slide{
    background:#69A8BB;
    bottom: 38px;
    right: 15px;
    cursor: pointer;
    display: block;
    float: right;
    height: 25px;
    line-height: 22px;
    position: relative;
    width: 26px;
 }
 .fs_li{
    -webkit-border-radius: 0 7px 7px 0;
    -moz-border-radius: 0 7px 7px 0;
    border-radius: 0 7px 7px 0;
 }
 .ls_li{
    -webkit-border-radius: 7px 0px 0px 7px;
    -moz-border-radius: 7px 0px 0px 7px;
    border-radius: 7px 0px 0px 7px;
 }
ul#slideShowCount li.slide span{
    padding-left:10px;
    color:white;
    font-size:12px;
}
ul#slideShowCount li.selectedTab span{
    color: #000;
}
ul#slideShowCount li.slide:hover{
    background-position:left -18px;
}

ul#slideShowCount li.slide.selectedTab{
    background-position:left -18px;
}
div#slideShow{
    background:#222;
    width:900px;
    margin: 0 auto;
	margin-bottom:15px;
    color:#fff;
}
/*home slideshow css end*/
</style>
<?php } ?>

