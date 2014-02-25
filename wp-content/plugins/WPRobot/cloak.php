<?php
	require_once("../../../wp-config.php");	
	$url = get_option('home');	
	$pattern='/(<a[\s]+[^>]*href\s*=\s*)([\"\'])([^>]+?)\2([^<>]*>)((?sU).*)(<\/a>)/i';	
	$post_id = intval($_GET['post_id']);
	$link_num = intval($_GET['link_num']);		
	$thepost = get_post($post_id, OBJECT);		
	if ( is_object($thepost) ) {
		$post_content = $thepost->post_content;
		$content = do_shortcode($post_content);	
		$link_count = preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);			
		if($link_count >= $link_num) {
			$url = $matches[$link_num-1][3];
			$url = str_replace('&amp;','&',$url);
		}			
	}	
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: $url");
	header("X-Redirect-Src: $wpr_linkcloaker->redirector", TRUE);
	exit();
?>