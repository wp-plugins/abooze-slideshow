=== Plugin Name ===
Contributors: Aboobacker P Omar
Donate link: http://aboobacker.com/
Tags: slideshow, images, jquery cycle, abooze slideshow
Requires at least: 3.0
Tested up to: 3.4.1
Stable tag: 3.1

This plugin creates an image slideshow in your theme. You can upload/delete images via the admin panel, and display the images in your theme.

== Description ==

Easily upload images with links to display a nice slideshow on your website. To manage, Go to <strong>Media-> Aboozé Slideshow</strong>. To display the slideshow, add the shortcode in your template: 
<code> <?php if (function_exists('ab_show')){ ab_show(); }?></code> 
or simply adding the shortcode 
<code>[ab_show]</code> 
in the page from the admin panel.

Each image can also be given a URL which, when the image is active in the slideshow, will be used as an anchor wrapper around the image, turning the image into a link to the URL you specified.  The slideshow is set to pause when the user hovers over the slideshow images, giving them ample time to click the link.

Images can also be deleted via the plugins Administration page.

== Installation ==

1. Upload the entire `abooze-home-slider` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure the plugin, and upload/edit/delete images via the "Aboozé Slideshow" menu within the "Media" tab
1. Upload images with dimension 900x450 pixels
1. Place `<?php if (function_exists('ab_show')){ ab_show(); }?>` in your theme where you want the slideshow to appear
1. Alternatively, you can use the shortcode [ab_show] in a post or page to display the slideshow.
1. Set custom width and height if you want.

== Screenshots ==

1. Screenshot of admin page. 
2. Front end view of the slideshow

== Frequently Asked Questions ==

= My images won't upload. What should I do? =

The plugin uses built in WordPress functions to handle image uploading. Therefore, you need to have [correct permissions](http://codex.wordpress.org/Changing_File_Permissions "Changing File Permissions") set for your uploads directory.

Also, a file that is not an image, or an image that does not meet the minimum height/width requirements, will not upload. Images larger than the dimensions set in the Settings of this plugin will be scaled down to fit, but images smaller than the dimensions set in the Settings will NOT be scaled up. The upload will fail and you will be asked to try again with another image.

Finally, you need to verify that your upload directory is properly set. Some hosts screw this up, so you'll need to check. Go to "Settings" -> "Miscellaneous" and find the input box labeled "Store uploads in this folder". Unless you are absolutely sure this needs to be something else, this value should be exactly this (without the quotes) "wp-content/uploads". If it says "/wp-content/uploads" then the plugin will not function correctly. No matter what, the value of this field should never start with a slash "/". It expects a path relative to the root of the WordPress installation.

= Images are uploaded, but the slideshow is not working on the site? =

There might be some jQuery conflict in your site. Please try moving the <code><?php wp_head(); ?></code> to the right before of the <code></head> </code> tag in your header.php of your current theme folder.

= In what order are the images shown during the slideshow? =

Chronologically, from the time of upload. For instance, the first image you upload will be the first image in the slideshow. The last image will be the last, etc.
== Changelog ==

= 1.0 =
* Initial Release

= 2.0 =
* Improved version

= 2.1 =
* Fixed minor bugs

= 3.0 =
* Added options to set custom width and height for the show.