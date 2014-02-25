<?php
$ar_optionsarray = array(
	"spinnerchief" => array(
		"enabled" => 0,
		"link" => "http://wprobot.net/go/spinnerchief",
		"name" => "SpinnerChief",
		"function" => "allrewriters_sc_rewrite",
		"options" => array(
			"sc_email" => array("value" => "", "name" => "Username", "type" => "text"),
			"sc_pw" => array("value" => "", "name" => "Password", "type" => "text"),
			"sc_quality" => array("value" => 1, "name" => "Quality", "type" => "select", "values" => array(0 => "use Best Thesaurus", 1 => "use Better Thesaurus", 2 => "use Good Thesaurus", 3 => "use All Thesaurus", 9 => "use Everyones favorites", )),
			"sc_port" => array("value" => 1, "name" => "Port", "type" => "select", "values" => array(9001 => 9001, 8000 => 8000, 8080 => 8080, 443 => 443)),
			"sc_thesaurus" => array("value" => "English", "name" => "Thesaurus", "type" => "select", "values" => array("English" => "English", "Arabic" => "Arabic", "Belarusian" => "Belarusian", "Bulgarian" => "Bulgarian", "Croatian" => "Croatian", "Danish" => "Danish", "Dutch" => "Dutch", "Filipino" => "Filipino", "Finnish" => "Finnish", "French" => "French", "German" => "German", "Greek" => "Greek", "Indonesian" => "Indonesian", "Italian" => "Italian", "Lithuanian" => "Lithuanian", "Norwegian" => "Norwegian", "Polish" => "Polish", "Portuguese" => "Portuguese", "Romanian" => "Romanian", "Slovak" => "Slovak", "Slovenian" => "Slovenian", "Spanish" => "Spanish", "Swedish" => "Swedish", "Turkish" => "Turkish", "Vietnamese" => "Vietnamese", )),
		)
	),	
	"thebestspinner" => array(
		"enabled" => 0,
		"link" => "http://wprobot.net/go/thebestspinner",
		"name" => "TheBestSpinner",
		"function" => "allrewriters_tbs_rewrite",
		"options" => array(
			"tbs_email" => array("value" => "", "name" => "Username", "type" => "text"),
			"tbs_pw" => array("value" => "", "name" => "Password", "type" => "text"),
			"tbs_quality" => array("value" => 1, "name" => "Quality", "type" => "select", "values" => array(1 => "Good", 2 => "Better", 3 => "Best")),
		)
	),
	"spinchimp" => array(
		"enabled" => 0,
		"link" => "http://wprobot.net/go/chimprewriter",
		"name" => "ChimpRewriter",
		"function" => "allrewriters_spinchimp_rewrite",
		"options" => array(
			"schimp_email" => array("value" => "", "name" => "Email", "type" => "text"),
			"appid" => array("value" => "", "name" => "API Key", "type" => "text"),
			"schimp_quality" => array("value" => 4, "name" => "Quality", "type" => "select", "values" => array(1 => "All", 2 => "Average", 3 => "Good", 4 => "Better", 5 => "Best")),
			"schimp_posmatch" => array("value" => 3, "name" => "Part of Speech (POS) match", "type" => "select", "values" => array(1 => "Very Loose", 2 => "Loose", 3 => "Full", 4 => "FullSpin", 0 => "None")),
		)
	),
	"spinrewriter" => array(
		"enabled" => 0,
		"link" => "http://wprobot.net/go/spinrewriter",
		"name" => "SpinRewriter",
		"function" => "allrewriters_sr_rewrite",
		"options" => array(
			"sr_email" => array("value" => "", "name" => "Email", "type" => "text"),
			"appid" => array("value" => "", "name" => "API Key", "type" => "text"),
			"sr_quality" => array("value" => "medium", "name" => "Quality", "type" => "select", "values" => array("low" => "Low", "medium" => "Medium", "high" => "High")),
		)
	),	
	"wordai" => array(
		"enabled" => 0,
		"link" => "http://wprobot.net/go/wordai",
		"name" => "WordAI",
		"function" => "allrewriters_wai_rewrite",
		"options" => array(
			"wai_rewrite_email" => array("value" => "", "name" => "Email", "type" => "text"),
			"wai_rewrite_pw" => array("value" => "", "name" => "Password", "type" => "text"),
			"wai_quality" => array("value" => "Regular", "name" => "Quality", "type" => "select", "values" => array("Regular" => "Regular", "Readable" => "Readable", "Very Readable" => "Very Readable")),
			"wai_sentence" => array("value" => 1, "name" => "Automatically Rewrite Sentences", "type" => "checkbox"),
			"wai_paragraph" => array("value" => 0, "name" => "Automatically Rewrite Paragraphs", "type" => "checkbox"),
			"wai_nooriginal" => array("value" => 0, "name" => "Don't Include Original Words", "type" => "checkbox"),
			"wai_api_ver" => array("value" => "turing", "name" => "API Version", "type" => "select", "values" => array("regular" => "Regular", "turing" => "Turing")),
		)
	),	
	"contentprofessor" => array(
		"enabled" => 0,
		"link" => "http://wprobot.net/go/contentprofessor",
		"name" => "ContentProfessor",
		"function" => "allrewriters_cprof_rewrite",
		"options" => array(
			"cprof_rewrite_email" => array("value" => "", "name" => "Login", "type" => "text"),
			"cprof_rewrite_pw" => array("value" => "", "name" => "Password", "type" => "text"),
			"cprof_acc_type" => array("value" => "paid", "name" => "Accout Type", "type" => "select", "values" => array("free" => "Free", "paid" => "Paid")),
			"cprof_quality" => array("value" => "ok", "name" => "Quality", "type" => "select", "values" => array("ok" => "ok", "better" => "better", "ideal" => "ideal")),
			"cprof_language" => array("value" => "en", "name" => "Language", "type" => "select", "values" => array("en" => "English", "es" => "Spanish", "fr" => "French", "de" => "German", "it" => "Italian")),
			"cprof_syn_limit" => array("value" => "5", "name" => "Max Synonyms", "type" => "select", "values" => array("1" => "1", "2" => "2", "3" => "3", "4" => "4", "5" => "5", "7" => "7", "10" => "10")),
		)
	),		
	"general" => array(
		"enabled" => 2,
		"name" => "General",
		"options" => array(
			"protected_words" => array("value" => "", "name" => "Protected Words", "type" => "textarea"),		
		)
	),		
);

?>