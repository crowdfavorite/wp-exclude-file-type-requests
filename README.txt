=== Exclude File Type Requests ===
Contributors: alexkingorg, crowdfavorite
Donate link: http://alexking.org/donate
Tags: 404, performance, permalinks
Requires at least: 2.9
Tested up to: 3.0
Stable tag: 1.0

Don't pass requests for certain file types to WordPress for 404 handling.

== Description ==

If you use the pretty permalinks feature of WordPress, any request that doesn't match to a file on the server will be passed to WordPress for handling. This results in 404 hits having more load on your server than a traditional 404 request. In particular it can be a problem if a directory of images is moved or missing, as each page load request might spawn dozens of 404-ing image requests - effectively increasing your server load by a multiplier.

This plugin allows you to set a list of file extensions that you do *not* want WordPress to handle 404s for. By default, it includes a list of media types, but you may want to adjust this to suit your needs.


== Installation ==

1. Download the plugin archive and expand it (you've likely already done this).
2. Put the 'exclude-file-type-requests' directory into your wp-content/plugins/ directory.
3. Go to the Plugins page in your WordPress Administration area and click 'Activate' for Exclude File Type Requests.
4. (Optional) Go to the Exclude File Type Requests Settings page (Settings > Exclude File Types) to adjust the file extensions to be excluded.


== Frequently Asked Questions ==

= Could this have any bad side effects? =

Nothing we've seen yet, but it could collide with other plugins that alter the .htaccess file. If something bad does happen, you can delete this plugin and re-save your permalinks.

= Does this work if you don't have pretty permalinks enabled? =

No, there is no need for this plugin if you do not have pretty permalinks enabled.


== Changelog ==

= 1.0 =
* First public release.
