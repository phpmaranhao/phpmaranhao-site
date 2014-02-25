=== AutoBlogged ===
Contributors: AutoBlogged
Tags: Autoblog, rss, feeds, atom, automate, content
Requires at least: 2.9
Tested up to: 3.3.1

AutoBlogged automatically creates posts in your WordPress blog from any RSS or Atom feed.


== Description ==

AutoBlogged automatically creates posts in your WordPress blog from any RSS or Atom feed. Use AutoBlogged to build an automated blog network, as a self-building video blog, to create topic portals, or aggregate RSS feeds. WP Autoblogs are a great way to quickly build keyword-dense content, earn money with your blog, and fully automate your blog posting.


== Installation ==

You can install AutoBlogged using the Add New Plugin page in the WordPress admin panel.

Alternatively you can upload it manually:

1. Extract the zip file and upload the contents to the wp-content/plugins/autoblogged directory of your WordPress installation. The path to autoblogged.php should be:
/wp-content/plugins/autoblogged/autoblogged.php

2. Activate AutoBlogged from the Plugins page.

For more information on installing plugins in WordPress, see http://codex.wordpress.org/Managing_Plugins#Installing_Plugins

== Upgrading ==
If you are upgrading AutoBlogged from a previous version, you simply upload the new files into the existing AutoBlogged directory. Upgrading will have no affect on your settings or feed configuration. If you have problems with the upgrade process, try removing any existing files before uploading.


== Changelog ==
= Version 2.9.1 - Jun 15, 2012 =
 - New options to clean and security check feed before processing
 - New option to set time zone offset
 - Improved handling for feedburner feeds
 - Improved date handling
 - Better handling of content retrieval
 - Create users with nicename rather than display name
 - New API filter for added video attachments
 - Fixed excerpt formatting issues
 - Minor user interface cleanup and bugfixes
 - Fixed issues with Regex parsing
 - Fixed handling of multiple custom namespace elements
 - Database schema update
 - Improvements to media player embedding
 - Fixes to address issues caused by wp-ecommerce
 - Other minor bug fixes


== Changelog ==
= Version 2.9 - Jan 27, 2012 =
 - Added full text content feature
 - Added excerpt type feature
 - Added content source feature
 - Added HTML filtering feature
 - Added per-feed allow comments and trackbacks feature
 - Added ability to create excerpts on More Tag
 - User interface overhaul
 - Improved automatic tag keyword detection 
 - Updated tags.txt file
 - General user interface improvements
 - Added time setting diagnostics on Support page
 - Fixed problems with affiliate links in footer
 - Integrated htmLawed library for improved security
 - Fixed SQL errors with history journal
 - Fixed encoding issues throughout the script
 - Fixed problems with word filtering options
 - Improved unwanted image filtering
 - Improved theme handling
 - Changed default settings
 - Comprehensive code refactoring
 - Minor bug fixes 
 - WordPress 3.3.1 compatibility
 - Fixed issue with custom post types without a name label
 - Fixed error with empty custom field and taxonomy values
 - Added ability to remove custom fields in wp by leaving values empty
 - Made undocumented timestamp post template function official
 - Fixed errors with regexpost template function
 - Fixed errors with SSL feeds with invalid cert
 


= Version 2.8.6 RC2 - Nov 22, 2011 =
- Fixed issues with using author from feed
- Improved handling of Yahoo News URLs
- Fixed issue with %date% when not using feed date
- Fixed issue with empty lines on tags list
- Minor error fixes and cleanup
- Better scheduling management
- Misc improvements to image handling
- Fixed issues with custom taxonomies
- Added link on support page for PHP error log
- General user interface cleanup
- Updated SimplePie library to v1.2.1
- Added iconv as an alternative when mb_convert_encoding not installed
- Fixed group by t_stamp SQL error
- Fixed array_rand error
- Added permissions check for cache directory
- Fixed bug with HTTPS urls
- Updated deprecated WP functions to newer functions
- Minor user interface fixes
- Added support for user mydefaults.php file
- Added new default feed settings feature
- Improved database error handling
- Improved time zone diagnostics


= Version 2.8.5 - Jul 7, 2011 =
- Fixed minor WordPress 3.2 compatibility issues
- Regex searches now allow for modifiers
- Added feed titles to feed processing preview page
- Fixed Google blog search problems
- Fixed issue adding authors from feed
- Fixed image URL recognition
- Fixed issue with extra image fields


= Version 2.8.4 - May 17, 2011 =
- More dupechecking improvements
- Improved handling of encoded links
- Improved handling of UTF-8 character sets
- Fixed problem of grabbing too many images from original site
- Fixed problem of grabbing first image when the first image is too small
- Tag handling improvements and bug fixes
- Fixed bug with custom taxonomies
- Fixed compatibility issue with versions of WP before 2.9
- Fixed issue where admin stylesheet was loaded on non-admin pages
- Fixed scheduling issues
- Fixed SSL link issues
- Other minor bug fixes


= Version 2.8.3 - Apr 4, 2011 =
- Fixed database update bug
- Fixed scheduling problems
- Improvements to image handling


= Version 2.8.2 - Mar 28, 2011 =
- Added new API hooks
- Added new dupecheck method later in the posting process
- Fixed minimum time checks
- Allowing for braces in post template by using double braces
- Added new settings to add original-source header and respect robots directives
- Fixed numerous character set issues
- Fixed numerous image attachment issues
- Added option to only include first image
- Improvements to category handling
- Improvements to Regular Expression post template function


= Version 2.8.1 - Mar 7, 2011 =
- Regular Expression search fixes
- Fixed journal history
- Added new API actions
- Updates to adding authors
- Better image filename handling
- Minor changes to some default settings
- Other minor bug fixes


= Version 2.8.0 - Feb 15, 2011 =
- Major overhaul with hundreds of fixes, improvements, and new features


= Version 2.7.7 beta 4 - Dec 24, 2010 =
- New AutoBlogged journal tracks scheduled runs, errors, and stats
- Numerous improvements to prevent concurrent or closely timed job processing
- Many user interface improvements
- New built-in external cron scheduling
- New Pre-loading of RSS feed cache to reduce job processing overhead with a large number of feeds
- New tracking of job completion status to pick up where failed jobs left off
- Reduced memory overhead
- New support for custom taxonomies
- New user interface allows for pasting multiple URLs or keywords into feed settings
- Now shows feed processing stats after a manual feed run
- Improved URL validity checking
- Improved HTTP error handling
- Search and Replace now searches custom fields
- Search and Replace bug fixes
- New regex feature in post templates to extract data from content
- Schedule checking now only triggered by home page or RSS feed views
- New link to AutoBlogged.com in site footer, can include your affiliate ID or be turned off
- Numerous security improvements
- New button to validate a feed using W3C feed validator
- Clarifications to setting descriptions
- New link to view _debug.log
- New tags list that includes new trends, politics, sports, top-paying keywords, and better conceptual terms
- Fixed mutlisite navigation
- Improvements to dupe checking
- New file-based dupe checking
- Fixes to first available lists, custom fields
- Improved handling of non-breaking spaces
- Fixed "Unable to write to upload directory: Invalid file type" errors
- Allow for accessing individual videos in post template
- Improvements in image URL identification
- Better handling of images without filename extension
- Improved unwanted image filtering
- Fixed problems adding new feeds in multisite
- Fixed encoded image filename handling
- Do not include trashed posts when performing dupe checks
- Added option to not skip posts from own domain
- Added option to skip posts with empty links
- Improved image retrieval
- Fixed handling of videos on Gabfire themes.
- Hundreds of other minor bug fixes and improvements
- New minimum requirements: PHP5 and WP 2.7


= Version 2.7.5 - Oct 8, 2010 =
- Added new dupechecking code to improve duplicate detection
- Improvements to prevent custom fields carrying over to other feeds
- Improved error checking to prevent various error messages
- Look for media:description if description is empty
- Better handling of YouTube image thumbnails
- Fixed thumbnails cropping bug
- Improved support for Gabfire and Press75 themes
- Better handling of custom fields if field data is missing
- Make better use of WordPress thumbnail and featured image features
- Fixed Yahoo tags API support for encoded content
- Only show debug messages if admin is logged in
- Removed dependencies on filter_var function
- Other minor compatibility fixes


= Version 2.7.3 - Jul 19, 2010 =
- Fixed zip file for uploading via WordPress
- Removed purge cache feature due to some incompatibilities
- Fixed First Available Tags feature in post templates
- Misc custom fields bug fixes


= Version 2.7.2 - Jun 28, 2010 =
* Fixed bug with custom fields
* Fixed YouTube URL
* Other misc/compatibility fixes


= Version 2.7.1 - Jun 18, 2010 =
* WordPress v3 Compatibility
* Improved duplicate checking code
* Improved diagnostics popup to display character set info and other info
* Fixed YouTube search URL
* Fixed WP Multisite activation bugs
* Fixed broken/obsolete searches
* Fixed delete feed button on edit feeds page
* Added feature to allow plain text search/replace
* Improved image and video  handling on incorrectly formatted feeds
* Misc post template bug fixes

= Version 2.6.2 - May 3, 2009 =
* Fixed several bugs in handling custom fields in conditional post templates
* Added ALLOW_BLANK_LINKS option in autoblogged.php to allow posts with empty links
* Added ALLOW_OWN_DOMAIN option in autoblogged.php to allow posts from your own domain
* Improved GabFire themes compatibility
* Misc bug fixes with tagging features
* Better handling of empty variables in post templates
* Misc fixes with scheduling code
* Improvements to support e-mail form on support page
* Improved WP Super Cache compatibility
* Fixed handling of double brackets in post templates
* Improved Thesis theme compatibility
* Improved handling of non-breaking spaces and post truncating
* Improved naming of cached images
* image_1, image_2, etc tags now work in post templates when EXTRA_IMAGE_FIELDS enabled
* Added TAGS_DELIMITER option in autoblogged.php to change multi-word tag delimiter
* Added support for Google XML Sitemaps plugin

= Version 2.5.74 - Aug 12, 2009 =
* Added more debugging code for loading extra files
* Added additional WordPress hooks to fix cron issue
* Added code to handle mysql_real_escape_string failures
* Fixed additional problems with tag and category UI elements
* No longer silently drops mismatched post template variables

= Version 2.5.73 - July 30, 2009 =
* Fixed bug that caused empty URL on RSS feeds

= Version 2.5.72 - July 16, 2009 =
* Fixed Yahoo! API tagging feature
* Fixed ab_logMsg error, improved error handling for logging code
* Changed format of zip file to allow uploading and installing directly from WP
* Updated SQL statement for creating database which caused problems in some environments
* Fixed tags box for WP 2.8 admin panel
* Fixed categories box for WP 2.8 admin panel
* Fixed htmlspecialchars_decode backwards compatibility issue


= Version 2.5.61 - June 1, 2009 =
* Fixed WP 2.8 compatibility
* Fixed implode() error on line 1428


= Version 2.5.01 - May 27, 2009 =
* Added detection of html-encoded image references in feed content.
* Fixed handling of custom namespace elements.
* Misc error handling and debugging fixes.


= Version 2.4.25 - April 1, 2009 =
* Fixed several tagging bugs
* Improved exception handling and debugging
* Fixed double brackets bug when using search/replace
* Fixed long title truncation bug
* Fixed filtering bug with empty filters
* Fixed image and thumbnail custom field bugs with various themes
* Added support for Press75 On Demand theme

= Version 2.4.23 - March 4, 2009 =
* Fixed URL parsing error with PHP4
* Renamed simplepie.inc to simplepie.php
* Changed ALLOW_ALL_TAGS behavior to include all html attributes
* Added additional dupe check based on post name
* Added check for wp_insert_user function and error message for old versions of WP
* Import tags from any media thumbnails
* Fixed PHP4 error: parse_url() expects exactly 1 parameter, 2 given...
* Fixed array_rand error on line 1447.
* Fixed search/replace to also seach video and image fields
* Removed extra custom fields added to each post
* Added support for WPThemesMarket Magazine News theme
* Added support for Press75 themes
* Improved custom RSS module handling to allow heirarchal namespaces
* Fixed last processed and next scheduled times in sidebar in WP 2.7
* Fixed link to modify thumbnail settings in WP 2.7
* Added additional entries to tags.txt and notags.txt files
* Made adjustments to default post template
* Improved WooThemes support
* Custom fields can now override any other field in the post such as Title or Date
* Fixed "Call to a member function get_attribution() on a non-object" error on line 1002
* Fixed bug where category named "Object" appears
* Now assigns only the default category when no category selected for the feed

= Version 2.4.22 =
* Upgraded SimplePie to version 1.1.3
* Fixed fsockopen "Using $this when not in object context" bug
* Added undocumented settings ALLOW\_OBJECT\_AND_EMBED\_TAGS, ALLOW\_FORM\_TAGS,
   ALLOW\_FRAME\_TAGS, ALLOW\_SCRIPT\_TAGS, ALLOW\_ALL\_TAGS, ENCODE\_INSTEAD\_OF\_STRIP,
   FORCE\_FEED
* Added code to automatically handle encoded html.
* Remove unmatched variables from post template.
* Fixed broken process and preview links in sidebar in WP 2.7 UI.
* Improved code to identify images in any feed field.
* Added code to block certain images such as stars in youtube feeds.
* Fixed bug with saving images locally when filename is invalid.
* Custom fields now override any value set during post processing.
* Added support for WooThemes.

= Version 2.4.19 release candidate =
* Fixed version not displaying bug in WP pre-2.7 UI.
* Fixed Missing argument error in WP 2.7.

= Version 2.3.418 beta =
* Added support for WorPress 2.7 user interface
* Added debug.png icon
* Minor change in handling of debug messages
* Added additional error handling for image_resize

= Version 2.3.381 beta =
* Added new debugging error messages
* Fixed bug parsing multiple templates
* Fixed bug where %author% variables not populated
* Changed type for search/replace database fields to prevent truncation
* Added additional dupe checking code
* Improved image type verification
* Fixed support request form
* Improved author matching procedure
* Fixed bug parsing two variables with a colon between them in feed template
* Improved video handling, disabled WordPress post filtering
* Improved image handling
* Added image path variable to support Arthemia theme
* Removed page_content from post custom fields
* Fixed Yahoo Tags code
* Post templates now allow literal brackets by using double brackets: [[ and ]]
* Image urls in %content% variable now point to locally cached images
* Made fsockopen errors silent
* Added check for debug.log permissions
* Changed check for updates to occur no more than once every 12 hours


For additional help, see our Quick Start Guide here:
http://autoblogged.com/online-help/getting-started/

You can get support by sending an e-mail to support@autoblogged or visiting http://community.autoblogged.com. Also visit our
customer forums at http://community.autoblogged.com/forums.