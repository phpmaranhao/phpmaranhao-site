<?PHP

/////////////////////////////////////////////////////////
// AutoBlogged Defaults File
//
// To create your own defaults, make a copy of this file
// and name it mydefaults.php. AutoBlogged will always
// load load a mydefaults.php file before this one if 
// it exists.
//
/////////////////////////////////////////////////////////
// Default Feed Settings

// General Options
$enabled = true;
$feed_type = 1;
//	 1 = RSS Feed,
//	 2 = Google Blog Search
//	 3 = Technorati Search
//	 4 = BlogDigger Search
//	 5 = Blogpulse Search
//	 6 = MSN Spaces Search
//	 7 = Yahoo! News Search
//	 8 = Flickr Tag Search
//	 9 = YouTube Tag Search
//	 10 = Yahoo! Video Search

$post_type = "Post";
$keywords_or_feed_url = '';
$default_status = 'publish'; // publish, pending, draft, private
$use_date_from_feed = 1;

// Feed Processing
$feed_processing_schedule = 0;
// 0 = With every scheduled update
// 1 = after every x updates
// 2 = Manually or when notified via XML-RPC ping

$feed_processing_every_x_updates = 0;
$post_processing = 1;
// 0 = Include all posts
// 1 = Include first x posts
// 2 = Randomly include x% of all posts
$max_posts_per_update = 10;
$randomly_include_x_percent_of_posts = 100;

// Tags
$additional_tags = '';

// Categories
$assign_posts_to_this_category = '';
$add_additional_categories = true;
$add_categories_as_tags = true;
$randomly_add_selected_categories = true;
$use_categories_from_original = true;
$add_categories_from_original = false;

// Authors
$author = RANDOM_AUTHOR;
//	RANDOM_AUTHOR
//	AUTHOR_FROM_FEED
//  or specific author name

$alternate_author_if_doesnt_exist = ADD_AUTHOR;
//	ADD_AUTHOR
//	SKIP_POST
//	RANDOM_AUTHOR
//  or specific author name

// Images
$save_full_images = false;
$create_thumbnails = true;

// Embedded Video Player
$video_width = 250;
$video_height = 206;
$custom_player_url = '';

// Include Posts that Contain
$all_these_words = '';
$any_of_these_words = '';
$the_exact_phrase = '';
$none_of_these_words = '';

// Custom Fields
//$custom_fields = ab_serialize(ab_arrayEncode(Array('author', 'copyright')));  // These are the fields
//$custom_values = ab_serialize(ab_arrayEncode(Array('YouTube', "Copyright (c) YouTube, LLC")));  // These are the values for the fields


// Post Templates
$feed_post_template = '
<p>{%video%|%thumbnail%}</p>

<p>%excerpt%</p>

[Continue at source|Continue reading|Continue reading|Continue reading here|Continued|Credit|Continued here|Excerpt from|Excerpt from|Follow this link|From|Jump to original|Link|Link|Link|Link|Link|Link to article|Link to original|More|More|More here|Original article|Original article|Original link|Original post|Original source|Originally from|Originally posted here|Originally posted here|Read article here|Read More|Read more|Read original article|Read the article|Read this article|Read this article|See more here|See original|See original article|See the article here|See the original article here|See the original post|See original article here|Source|Source|Source|Source article|Taken from|Taken from|This article|This article is from|View article|View original|View original article|View original post here|View post|View the original here|View this article|Visit link|Visit site|Visit site|Visit source][:<br />|:&nbsp;|:&nbsp;|&nbsp;-&nbsp;|:&nbsp;&nbsp;]

<a target="_blank" href="%link%" title="%title%">%title%</a>
';

/////////////////////////////////////////////////////////
// Default Tag Options

// General Options
$minimum_tag_length = 3;
$maximum_tag_length = 25;
$maximum_tags_per_post = 15;

// Tag Sources
$use_original_tags_from_feed = true;
$use_internal_tagging_engine = true;
$visit_source_url = true;
$get_yahoo_tags = false;
$yahoo_app_id = '';

// Additional Tags
$randomly_add_these_tags = '';

// Tag Filtering
$do_not_use_these_as_tags = '';


/////////////////////////////////////////////////////////
// Default Filtering Options

// Duplicate Posts
$match_title = true;
$match_link = false;

// Title filtering
$maximum_title_length = 150;
$long_title_handling = 0;
// 0 = Truncate to the nearest word
// 1 = Skip the post
$skip_titles_in_all_caps = true;
$skip_titles_with_multiple_punctuation_marks = true;

// Blacklists
$url_blacklist = 'digg.com';
$keywords_blacklist =
   'pharmacy
prescriptions
"comment on"
"buy now"
newsgroup
help!
vbulletin
splogger
guaranteed
"money back"
"link roundup"';


/////////////////////////////////////////////////////////
// Default Settings

// Registration
$serial_number = '';
$check_for_updates = true;

// General Options
$autoblogged_enabled = true;
$minimum_time_between_updates = 60; // minutes
$maximum_time_between_updates = 180;

// Excerpts
$minimum_excerpt_length = 3;
$maximum_excerpt_length = 5;


/* Excerpt Unit
   0 - Words
   1 - Sentences
   2 - Paragraphs
*/
$excerpt_unit = 1;



/* Excerpt Source:
 0   Feed <content> field
 1   Feed <description> field
 2   Original article (experimental)
*/
$excerpt_source = 0;


/* Excerpt Type
 0 - Create a short excerpt
 1 - Use all content
*/
$excerpt_type = 0;


/* HTML Filtering
  0 - Plain text only
  1 - Remove all but basic formatting
  2 - Remove only unsafe tags
  3 - Leave HTML intact (not recommended)
*/
$excerpthtml_filter = 1;



// WordPress Options
$use_link_info = true;
$use_author_info = false;

// HTTP Options
$http_referrer = home_url();
$http_user_agent = 'Mozilla/4.8 [en] (Windows NT 6.0; U)';

// RSS Options
$rss_cache_timeout = 600;

// Debug
$logging = false;
$show_debug = false;

// Preprocessing
$cleanfeed = false;
$securitycheck = false;


// Trackbacks and comments
$allowpings = true;
$allowcomments = true;