<?php

// CONTENT PROFESSOR

function allrewriters_cprof_rewrite($article) {

	$options = get_option("allrewriters_settings");
	$protected_words = $options["general"]["options"]["protected_words"]["value"];
	$options = $options["contentprofessor"]["options"];

	$login = $options["cprof_rewrite_email"]["value"];
	$pw = $options["cprof_rewrite_pw"]["value"];
	$quality = $options["cprof_quality"]["value"];		
	$cprof_language = $options["cprof_language"]["value"];		
	$cprof_syn_limit = $options["cprof_syn_limit"]["value"];		
	$cprof_acc_type = $options["cprof_acc_type"]["value"];		

	if(isset($article) && isset($quality) && isset($login) && isset($pw)) {

		$article = urlencode($article);

		if($cprof_acc_type == "paid") {
			$cpurl = 'http://www.contentprofessor.com/member_pro/api/get_session?format=xml&login='.$login.'&password='.$pw.'';
		} else {
			$cpurl = 'http://www.contentprofessor.com/member_free/api/get_session?format=xml&login='.$login.'&password='.$pw.'';
		}

		$req = curl_init();
		curl_setopt($req, CURLOPT_URL, $cpurl);
		curl_setopt($req,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($req, CURLOPT_POST, true);
		curl_setopt($req, CURLOPT_POSTFIELDS, $text);
		$result = trim(curl_exec($req));
		curl_close($req);
		
		$pxml = simplexml_load_string($result);
		$session = $pxml->data->session;

		if(!empty($pxml->error->description)) {
			$return["error"] = "Contenprofessor Error: ". $pxml->error->description;	
			return $return;				
		} elseif(!empty($session)) {
		
			if($cprof_acc_type == "paid") {
				$cpurl = 'http://www.contentprofessor.com/member_pro/api/include_synonyms?format=xml&session='.$session.'&removal_on=1&quality='.$quality.'&limit='.$cprof_syn_limit.'&language='.$cprof_language.'&text='.$article.'';
			} else {
				$cpurl = 'http://www.contentprofessor.com/member_free/api/include_synonyms?format=xml&session='.$session.'&removal_on=1&quality='.$quality.'&limit='.$cprof_syn_limit.'&language='.$cprof_language.'&text='.$article.'';
			}				
			
			$req = curl_init();
			curl_setopt($req, CURLOPT_URL, $cpurl);
			curl_setopt($req,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($req, CURLOPT_POST, true);
			curl_setopt($req, CURLOPT_POSTFIELDS, $text);
			$result = trim(curl_exec($req));
			curl_close($req);
			
			$pxml = simplexml_load_string($result);		

			if(!empty($pxml->error->description)) {
				$return["error"] = "Contenprofessor Error: ". $pxml->error->description;	
				return $return;				
			} elseif(!empty($pxml->data->text)) {
				return (string) strip_tags($pxml->data->text);		
			} else {
				$return["error"] = "Contenprofessor Error: Empty response.";	
				return $return;				
			}
		} else {
			$return["error"] = "Contenprofessor Session could not be loaded.";	
			return $return;			
		}
	} else {
		$return["error"] = "API Information missing.";	
		return $return;	
	}
}

// WORDAI

function allrewriters_wai_rewrite($article) {

	$options = get_option("allrewriters_settings");
	$protected_words = $options["general"]["options"]["protected_words"]["value"];
	$options = $options["wordai"]["options"];

	$user = $options["wai_rewrite_email"]["value"];
	$pw = $options["wai_rewrite_pw"]["value"];
	$quality = $options["wai_quality"]["value"];		
	$sentence = $options["wai_sentence"]["value"];		
	$paragraph = $options["wai_paragraph"]["value"];		
	$nooriginal = $options["wai_nooriginal"]["value"];		
	
	if($sentence == 1) {$sentence = "on";}
	if($paragraph == 1) {$paragraph = "on";}
	if($nooriginal == 1) {$nooriginal = "on";}
	$api_ver = $options["wai_api_ver"]["value"];
	
   if(isset($article) && isset($quality) && isset($user) && isset($pw)) {

      $article = urlencode($article);

	  if($api_ver == "regular") {
		$ch = curl_init('http://wordai.com/users/regular-api.php');	
		if($quality == "Regular") {
			$quality = 0;
		} elseif($quality == "Readable") {
			$quality = 50;
		} else {
			$quality = 100;
		}		
	  } else {
		$ch = curl_init('http://wordai.com/users/turing-api.php');	  
	  }

      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt ($ch, CURLOPT_POST, 1);
      curl_setopt ($ch, CURLOPT_POSTFIELDS, "s=$article&quality=$quality&email=$user&pass=$pw&returnspin=true&sentence=$sentence&paragraph=$paragraph&nooriginal=$nooriginal&protected=$keyword,".urlencode($protected)."");
      $result = curl_exec($ch);
      curl_close ($ch);

		if (strpos($result, "Error ") !== false) {
			$return["error"] = "Rewrite Error: ".strip_tags($result);	
			return $return;	
		}	  
	  
      return $result;

   } else {
		$return["error"] = "API Information missing.";	
		return $return;	
   }

}

// SPINCHIMP

function allrewriters_spinchimp_GlobalSpin($email,$apiKey, $text, $quality, $protectedTerms, $posmatch, $rewrite) {

	//Check Inputs
	if (!isset($email) || trim($email)=== '') return 'Failed: No email specified';
	if (!isset($apiKey) || trim($apiKey)=== '') return 'Failed: No APIKey specified';
	if (!isset($text) || trim($text)=== '') return "Failed: Empty text.";

	//Add paramaters
	$paramaters = array();
	$paramaters['email'] = $email;
	$paramaters['apiKey'] = $apiKey;
	$paramaters['aid'] = "WPRobot"; 
	$paramaters['rewrite'] = 1; 
	$paramaters['spinwithinhtml'] = 1; 
	if (isset($quality) && trim($quality)=== '') 
		$paramaters['quality'] = $quality;
	if (isset($posmatch)) 
		$paramaters['posmatch'] = $posmatch;
	if (isset($protectedTerms) && trim($protectedTerms)=== '') 
		$paramaters['protectedterms'] = $protectedTerms;		

	$qs = allrewriters_spinchimp_buildQueryString($paramaters);
	$result = allrewriters_spinchimp_makeApiRequest('http://api.spinchimp.com/','GlobalSpin',$qs,$text);
	return $result;
}

function allrewriters_spinchimp_buildQueryString($paramaters) {
	$data = '';
	$firstparam = true;
	foreach ($paramaters as $key => $value) {
		if ($firstparam) $firstparam = false;
		else $data .= '&';
		$data .= $key . '=' . urlencode($value);
	}
	return $data;
}

function allrewriters_spinchimp_makeApiRequest($url, $command, $querystring, $text) {
	$req = curl_init();
	curl_setopt($req, CURLOPT_URL, 'http://api.spinchimp.com/' . $command . '?' . $querystring);
	curl_setopt($req,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($req, CURLOPT_POST, true);
	curl_setopt($req, CURLOPT_POSTFIELDS, $text);
	$result = trim(curl_exec($req));
	curl_close($req);
	return $result;
}

function allrewriters_spinchimp_rewrite($text) {

	$options = get_option("allrewriters_settings");
	$protected_words = $options["general"]["options"]["protected_words"]["value"];
	$options = $options["spinchimp"]["options"];

	$result = allrewriters_spinchimp_GlobalSpin($options["schimp_email"]["value"],$options["appid"]["value"], $text, $options["schimp_quality"]["value"], $protected_words, $options["schimp_posmatch"]["value"], $rewrite);

	if (strpos($result, "Failed:") !== false) {
		$return["error"] = "Rewrite Error: ".strip_tags($result);	
		return $return;		
	}
	
	return $result;
}

// SPIN REWRITER

function allrewriters_sr_rewrite($article) {

	$options = get_option("allrewriters_settings");
	$protected_words = $options["general"]["options"]["protected_words"]["value"];
	$options = $options["spinrewriter"]["options"];	

	$data = array();	
	$data['email_address'] = $options["sr_email"]["value"];			// your Spin Rewriter email address goes here
	$data['api_key'] = $options["appid"]["value"];	// your unique Spin Rewriter API key goes here
	$data['action'] = "unique_variation";		
	// possible values: 'api_quota', 'text_with_spintax', 'unique_variation', 'unique_variation_from_spintax'
	$data['text'] = $article;
	
	$protected = explode(",", $protected_words);
	$prot = "";
	
	foreach($protected as $pt) {$prot .= trim($pt)."\n";}
	$data['protected_terms'] = $prot;		// protected terms: John, Douglas Adams, then
	$data['confidence_level'] = $options["sr_quality"]["value"];							// possible values: 'low', 'medium' (default value), 'high'
	$data['nested_spintax'] = "true";							// possible values: 'false' (default value), 'true'	
	$data_raw = "";
	foreach ($data as $key => $value){
		$data_raw = $data_raw . $key . "=" . urlencode($value) . "&";
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://www.spinrewriter.com/action/api");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_raw);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$api_response = trim(curl_exec($ch));
	curl_close($ch);	
	$resp = json_decode($api_response, true);
	
	if($resp["status"] == "OK") {
		return $resp["response"];	
	} elseif($resp["status"] == "ERROR") {
		$return["error"] = $resp["response"];	
		return $return;			
	} else {
		$return["error"] = "No response received";	
		return $return;				
	}
}

// SpinnerChief

function allrewriters_sc_rewrite($article){

	$options = get_option("allrewriters_settings");
	
	$protected_words = $options["general"]["options"]["protected_words"]["value"];
	$options = $options["spinnerchief"]["options"];	
	$user = $options["sc_email"]["value"];
	$pw = $options["sc_pw"]["value"];
	$quality = $options["sc_quality"]["value"];
	$port = $options["sc_port"]["value"];
	$thesaurus = $options["sc_thesaurus"]["value"];
	
	if(empty($port)) {$port = 9001;}	
	if(empty($thesaurus)) {$thesaurus = "English";}	
	$url = "http://api.spinnerchief.com:$port/apikey=ca01285820b24905b&username=$user&password=$pw&spintype=1&protecthtml=0&spinhtml=0&original=0&spinfreq=1&wordquality=$quality&thesaurus=$thesaurus&tagprotect=[]&protectwords=".urlencode($protected_words)."";

	$article = base64_encode($article);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_PORT , $port);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $article);
	//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($ch, CURLOPT_REFERER, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 80);	
	$html = trim(curl_exec($ch));
	curl_close($ch);

	$html = base64_decode($html);	
	
	if (strpos($html, "error=") !== false) {
		$return["error"] = "Rewrite Error: ".strip_tags($html);	
		return $return;		
	}

	return $html;
	return urldecode($html);
}

// TheBestSpinner

function allrewriters_tbs_request($url, $data, &$info){

	$fdata = "";
	foreach($data as $key => $val){
		$fdata .= "$key=" . urlencode($val) . "&";
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fdata);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_REFERER, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 80);	
	$html = trim(curl_exec($ch));
	curl_close($ch);
	return $html;
}

function allrewriters_tbs_rewrite($text) {
	$options = get_option("allrewriters_settings");
	$protected_words = $options["general"]["options"]["protected_words"]["value"];
	$options = $options["thebestspinner"]["options"];	

	$data = array();
	$data['action'] = 'authenticate';
	$data['apikey'] = 'wprobot4b8ff4a5ef0d3';	
	$data['format'] = 'php';
	$data['username'] = $options["tbs_email"]["value"];
	$data['password'] = $options["tbs_pw"]["value"];
	
	$output = unserialize(allrewriters_tbs_request('http://thebestspinner.com/api.php', $data, $info));
	if($output['success']=='true'){

		$session = $output['session'];

		$data = array();
		$data['session'] = $session;
		$data['apikey'] = 'wprobot4b8ff4a5ef0d3';
		$data['format'] = 'php';
		$data['text'] = $text;
		$data['action'] = 'replaceEveryonesFavorites';
		$data['maxsyns'] = '3';
		$data['quality'] = $options["tbs_quality"]["value"];
		$data['protectedterms'] = urlencode($protected_words);
		
		$output = allrewriters_tbs_request('http://thebestspinner.com/api.php', $data, $info);
		$output = unserialize($output);

		if($output['success']=='true'){
			if($spinsave == "Yes") {		
				return stripslashes(str_replace("\r", "<br>", $output['output']));			
			} else {
				
				$newtext = stripslashes(str_replace("\r", "<br>", $output['output']));

				$data = array();
				$data['session'] = $session;
				$data['apikey'] = 'wprobot4b8ff4a5ef0d3';			
				$data['format'] = 'php';
				$data['text'] = $newtext;
				$data['action'] = 'randomSpin';
				
				$output = allrewriters_tbs_request('http://thebestspinner.com/api.php', $data, $info);
				$output = unserialize($output);		

				if($output['success']=='true'){	
					return stripslashes(str_replace("\r", "<br>", $output['output']));
				} else {
					if(empty($output["error"])) {$output["error"] = "TBS request has timed out, no response received.";}
					$return["error"] = __("Rewrite Error: ","allrewriters").$output["error"];	
					return $return;				
				}	
			}
		} else {
			if(empty($output["error"])) {$output["error"] = "TBS request has timed out, no response received.";}			
			$return["error"] = __("Rewrite Error: ","allrewriters").$output["error"];	
			return $return;				
		}
	} else {
		if(empty($output["error"])) {$output["error"] = "TBS request has timed out, no response received.";}
		$return["error"] = __("Rewrite Error: ","allrewriters").$output["error"];	
		return $return;			
	}
}

?>