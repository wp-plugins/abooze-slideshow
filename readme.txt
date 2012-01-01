=== Plugin Name ===
Contributors: Aboobacker Omar
Tags: slideshow, images, jquery cycle, abooze slideshow
Requires at least: 3.0
Tested up to: 3.3
Stable tag: 1.0

This plugin creates an image slideshow in your theme, using the jQuery Cycle plugin. You can upload/delete images via the administration panel, and display the images in your theme by using the 
<code>
<div id="slideShow">
    <?php if (function_exists('wp_cycle')){ wp_cycle(); }?>
</div>  
</code> template tag, which will generate all the necessary HTML for outputting the rotating images.

== Description ==

Easily upload images with links to display a nice slideshow on your website. To manage, Go to <strong>Media-> Aboozé Slideshow</strong>. To display the slideshow, add the shortcode: <code> <div id="slideShow">
    <?php if (function_exists('wp_cycle')){ wp_cycle(); }?>
</div> </code> in your template.

Each image can also be given a URL which, when the image is active in the slideshow, will be used as an anchor wrapper around the image, turning the image into a link to the URL you specified.  The slideshow is set to pause when the user hovers over the slideshow images, giving them ample time to click the link.

Images can also be deleted via the plugins Administration page.

== Installation ==

1. Upload the entire `abooze-home-slider` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure the plugin, and upload/edit/delete images via the "Aboozé Slideshow" menu within the "Media" tab
1. Place `<div id="slideShow">
    <?php if (function_exists('wp_cycle')){ wp_cycle(); }?>
</div>` in your theme where you want the slideshow to appear
1. Alternatively, you can use the shortcode [wp_cycle] in a post or page to display the slideshow.

== Frequently Asked Questions ==

= My images won't upload. What should I do? =

The plugin uses built in WordPress functions to handle image uploading. Therefore, you need to have [correct permissions](http://codex.wordpress.org/Changing_File_Permissions "Changing File Permissions") set for your uploads directory.

Also please note that the images should be having the dimensions: <b>1000x528 pixels</b> to upload. I'm working to allow users for choosing different dimensions.

Also, a file that is not an image, or an image that does not meet the minimum height/width requirements, will not upload. Images larger than the dimensions set in the Settings of this plugin will be scaled down to fit, but images smaller than the dimensions set in the Settings will NOT be scaled up. The upload will fail and you will be asked to try again with another image.

Finally, you need to verify that your upload directory is properly set. Some hosts screw this up, so you'll need to check. Go to "Settings" -> "Miscellaneous" and find the input box labeled "Store uploads in this folder". Unless you are absolutely sure this needs to be something else, this value should be exactly this (without the quotes) "wp-content/uploads". If it says "/wp-content/uploads" then the plugin will not function correctly. No matter what, the value of this field should never start with a slash "/". It expects a path relative to the root of the WordPress installation.

= In what order are the images shown during the slideshow? =

Chronologically, from the time of upload. For instance, the first image you upload will be the first image in the slideshow. The last image will be the last, etc.
== Changelog ==

= 1.0 =
* Initial Release