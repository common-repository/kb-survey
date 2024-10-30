<?php

if (!defined('KBSURVEY'))
	die( 'Please do not call this file directly, hacker.' );

	
	
	
	
	
//////////////////////////////////////////////////////////////////////
// our main function.
//////////////////////////////////////////////////////////////////////
function kbSurvey_display($title=false){


	$survey = kbSurvey_validID(); // Checks ?kbsN for validity; returns either FALSE or filename of survey data
	if (!$survey && 'badID'!=$_GET['kbsA'])
		unset( $_GET['kbsA' ] ); // don't try executing one of the actions below (other than badID) if there's not a valid survey ID being passed
	if($survey)
		require( KBSURVEY_DATA . '/' . $survey );

	// are we just getting the title, or displaying the_content?
	if ($title)
		return $kbSurvey_meta['title'];
	// ELSE we're filtering the_content, not the title
	
	// if some dork writes [kbsurvey] twice in the same page, let's execute only once.
	if (defined('KBSURVEY_DONE'))
		return '[kbsurvey]';
	define('KBSURVEY_DONE',true);

	// is this a normal survey, or do we need to randomly select a sub-survey?
	if ($kbSurvey_meta['randomDir']){
		$kbSurvey_meta['randomSelection'] = kbSurvey_randomSubsurvey($kbSurvey_meta['randomDir']);
		require( KBSURVEY_DATA . '/' . $kbSurvey_meta['randomDir'] . '/' . $kbSurvey_meta['randomSelection'] );
	}

	if (1==$_POST['tookSurvey'])
		$_GET['kbsA'] = 'submit';

	$out = '<div class="kbSurvey">';

	switch ($_GET['kbsA']){ // kbsA means "kbSurvey action"
		case 'takeSurvey':
			$out .= kbSurvey_takeSurvey($kbSurvey_data,$survey,$kbSurvey_meta);
			break;
		case 'submit':
			$out .= kbSurvey_submitResponses($kbSurvey_data,$survey,$kbSurvey_meta);
			break;
		case 'viewResults':
			$out .= kbSurvey_viewResponses($kbSurvey_data,$survey,$kbSurvey_meta);
			break;
		case 'viewSpreadsheet':
		case 'viewSpreadsheetLong': // displays text, not numbers, for MC responses
			if ($kbSurvey_meta['randomDir']){
				$files = kbSurvey_availableSubsurveys($kbSurvey_meta['randomDir']);
				$j = 1;
				foreach($files as $f){
					$out .= '<h3>Questionnaire #'.$j++.'</h3>';
					require(KBSURVEY_DATA . '/' . $kbSurvey_meta['randomDir'] . '/' . $f);
					$out .= kbSurvey_viewSpreadsheet($kbSurvey_data, substr($survey,0,-4).'_'.$f);
				}
			}else{
				$out .= kbSurvey_viewSpreadsheet($kbSurvey_data,$survey);
			}
			break;
		case 'badID':
			$out .= kbSurvey_selectSurvey(true);
			break;
		default:
			$out .= kbSurvey_selectSurvey();
			break;
	}

	$out .= '</div>';
	return $out;
}








//////////////////////////////////////////////////////////////////////
// output functions
//////////////////////////////////////////////////////////////////////

function kbSurvey_takeSurvey($kbSurvey_data,$survey,$kbSurvey_meta){
	// use a time marker in the URL to prevent folks from coming directly to the survey via url--make them go through the selection form, which produces ?n=time()/972
	// this is an easy mechanism to fool--it's mainly to prevent naive web visitors from linking to the wrong part of the survey.
	$t = 972 * $_GET['n'];
	$t = time() - $t;
	if ($t<-1500 || $t>86400) // 24 hours in the past. To compensate for rounding on $_GET[n], we also allow a time stamp up to 1500 seconds in the future.
		return '<p>Sorry, but your session has expired.</p>';

	// valid survey?
	if (!is_array($kbSurvey_data))
		return '<p>Sorry, but there seems to be a problem with the survey you have requested.</p>';

	// survey currently open?
	if ($kbSurvey_meta['begin'] && (time()<$kbSurvey_meta['begin']))
		return '<p>Sorry, but this survey is not available yet. Please come back later.</p>';
	if ($kbSurvey_meta['end'] && (time()>$kbSurvey_meta['end']))
		return '<p>Sorry, but this survey is no longer available.</p>';

	// limit number of responses:
	$file = KBSURVEY_RESPONSES . '/' . $survey; // file where responses to this survey get saved
	if ($kbSurvey_meta['maxResponses'] && file_exists($file)){
		require_once( $file ); // gives us $responses
		$responses = unserialize( $responses );
		if ($kbSurvey_meta['maxResponses'] <= count($responses))
			return '<p>Sorry, but this survey has already had the maximum number of responses and is not accepting further respondents.</p>';
	}

	// proceed:
	$out = '
		<form method="post" action="'.get_permalink().'?kbsN='.$_GET['kbsN'].'&amp;kbsA=submit" onsubmit=\'return confirm("Are you sure you want to submit your responses? Once you submit, you cannot change your responses later.");\'>
			<table class="kbSurveyT">
	';

	$i = 0;
	foreach( $kbSurvey_data as $task )
		$out .= kbSurvey_showTask( $task['type'], $task, $i++ ) . "\n";

	// we don't want to save a student's responses twice just because the dork hit "reload" after saving. A way to avoid that:
	$nonce = date('siHymd') . str_replace('.php', '', $survey) . '_id' . uniqid();
	// note that this is not a typical WP nonce, nor is it used for the same purpose. I made this so I could give students a confirmation number allowing me to give them
	// extra credit for doing surveys online. That's really the only purpose this number served when it was coded in...

	// if we're using a survey that randomly selects a sub-survey, we'll need to know which sub-survey is in use:
	if ($kbSurvey_meta['randomDir'] && $kbSurvey_meta['randomSelection'])
		$subS = '<input type="hidden" id="subSurvey" name="subSurvey" value="'.$kbSurvey_meta['randomSelection'].'" />';

	$out .= '
				<tr><th></th><td><input type="submit" value="Done, submit my responses &raquo;" name="submitted" id="submitted" /><input type="hidden" name="tookSurvey" id="tookSurvey" value="1" /><input type="hidden" name="nonce" id="nonce" value="'.$nonce.'" />'.$subS.'</td></tr>
			</table>
		</form>
	';
	return $out;
}

function kbSurvey_submitResponses($kbSurvey_data,$survey,$kbSurvey_meta){
	if (!is_array($kbSurvey_data))
		return '<p>Sorry, but there seems to be a problem with the survey you have requested.</p>';
	if (!preg_match('~^[\w]{12}'.str_replace('.php','',$survey).'_id[\w]{13}$~', $_POST['nonce']))
		return '<p>Sorry, but there was a problem with the information you submitted. Please send this error message to me so I can fix it: <br /><strong>Bad nonce '.$_POST['nonce'].'</strong></p>';

	$i = 0;
	foreach( $kbSurvey_data as $task )
		$newResponses[] = kbSurvey_saveTask( $task['type'], $task, $i++ );

	// what file will we save responses to?
	if ($kbSurvey_meta['randomDir'] && $kbSurvey_meta['randomSelection']){ // we're using a randomly selected sub-survey
		if ( in_array($_POST['subSurvey'], kbSurvey_availableSubsurveys($kbSurvey_meta['randomDir']) ) )
			$file = KBSURVEY_RESPONSES . '/' . substr($survey,0,-4) . '_' . $_POST['subSurvey'];
		else
			return '<p>Oops--there was an error. Your responses were not saved.</p>';		
	}else{ // a normal survey
		$file = KBSURVEY_RESPONSES . '/' . $survey;
	}

	if (file_exists( $file )){
		require_once( $file ); // gives us $responses
		$responses = unserialize( $responses );
	}else{
		$responses = array();
	}

	$responses[$_POST['nonce']] = $newResponses; // ensures we don't enter the same student's responses more than once if he e.g. reloads the page after saving
	$serialResponses = '<?php $responses = \''. serialize( $responses ) .'\'; ?>';
	
	$successMsg = '<p>Thank you for submitting your responses.';
	if ($kbSurvey_meta['giveReceipt'])
		$successMsg .= ' This is your unique confirmation number:</p><p>'.$_POST['nonce'];
	$successMsg .= '</p>';
	
	if ($kbSurvey_meta['inviteResults'] && ($kbSurvey_meta['inviteResults']<=$responses))
		$successMsg .= '<p><a href="?kbsN='.$_GET['kbsN'].'&amp;kbsA=viewResults">View survey results</a></p>';

	if ( file_put_contents( $file, $serialResponses ) )
		return $successMsg;
	else
		return '<p>Oops--there was an error. Your responses were not saved. Please reload to try again.</p>';
}

function kbSurvey_viewResponses($kbSurvey_data,$survey,$kbSurvey_meta){
	// control access:
	if (!current_user_can('administrator')){
		if ( !$kbSurvey_meta['publicResults'] )
			return '<p>Sorry, but these survey results are not available to the public.</p>';
		if ($kbSurvey_meta['publicResultsWhen'] && (time()<$kbSurvey_meta['publicResultsWhen']))
			return '<p>Sorry, but these survey results are not available yet.</p>';
		$limit = $kbSurvey_meta['publicResultsOpenLimit'];
	}else{
		$limit = $kbSurvey_meta['adminResultsOpenLimit'];
	}

	// is this a normal survey, or are we dealing with a randomizing survey?
	if ($kbSurvey_meta['randomDir'] && $kbSurvey_meta['randomSelection']){ // we're using a randomizing survey with sub-surveys
		// compile all the data for all the subsurveys
		$files = kbSurvey_availableSubsurveys($kbSurvey_meta['randomDir']);
		$count = 0;
		$taskCount = 0;
		foreach($files as $file){
			$f = KBSURVEY_RESPONSES . '/' . substr($survey,0,-4) . '_' . $file;
			$d = KBSURVEY_DATA . '/' . $kbSurvey_meta['randomDir'] . '/' . $file;
			if (!file_exists($d))
				continue;
			require($d);
			if (!is_array($kbSurvey_data))
				continue;
			$allSurveys[ $file ] = $kbSurvey_data;
			$lastCount = count($kbSurvey_data);
			$taskCount += $lastCount; // both are used shortly
			if (file_exists($f)){
				require($f);
				$allResponses[ $file ] = unserialize( $responses );
				if (is_array($allResponses[$file]))
					$count += count( $allResponses[$file] ); // used shortly
			}else{
				$allResponses[ $file ] = array();
			}
		}
		if (0==$count)
			return '<p>No responses yet.</p>';

		// display the data for all the subsurveys, taking account of the 'randomParallel' setting, but only if (1) there's only two surveys and (2) they have equal counts
		if ($kbSurvey_meta['randomParallel'] && 2==count($allSurveys) && ($taskCount/2==$lastCount)){ // display side-by-side
			$outR = '<table class="kbSurveyT">';
			// make headers
			$outR .= '<tr class="randomParallel">';
			$outR .= '<th scope="col"></th>'; // holds task numbers
			$i = 1;
			foreach($allSurveys as $file=>$data){
				$outR .= '<th scope="col">Questionnaire #'.$i++.' &ndash; '.count($allResponses[$file]).' Responses</th>';
			}
			$outR .= '</tr>';
			// display results
			global $kbSurvey_num, $kbSurvey_alt;
			$files = array_keys($allSurveys);
			for( $j=0; $j<count($allSurveys[$files[0]]); $j++ ){
				$args1 = $allSurveys[ $files[0] ][$j]; // task from survey 1
				$args2 = $allSurveys[ $files[1] ][$j]; // task from survey 2
				$args1 = kbSurvey_getTaskDefaults( $args1['type'], $args1 );
				$args2 = kbSurvey_getTaskDefaults( $args2['type'], $args2 );
				if (!$args1 || !$args2)
					continue;
				$t1 = kbSurvey_viewTaskResponses( $args1['type'], $args1, $j, $allResponses[$files[0]], $limit );
				$t2 = kbSurvey_viewTaskResponses( $args2['type'], $args2, $j, $allResponses[$files[1]], $limit );
				if (!$t1 || !$t2)
					continue;
				if ($args1['numbered'] || $args2['numbered'])
					$outR .= '<tr class="'.$kbSurvey_alt.'"><th class="kbSurveyNum">' . $kbSurvey_num++ . '</th>';
				else
					$outR .= '<tr class="'.$kbSurvey_alt.'"><th class="kbSurveyNum"></th>';
				$outR .= '<td class="kbSurveyTask">' . $t1 . '</td><td class="kbSurveyTask">' . $t2 . '</td></tr>';
				$kbSurvey_alt = ('alternate'==$kbSurvey_alt) ? '' : 'alternate';
				$i++;

			}
			$outR .= '</table>';
		}else{ // display in sequence
			global $kbSurvey_num;
			$i = 1;
			foreach($allSurveys as $file=>$data){
				$outR .= '<h3>Questionnaire #'.$i++.' &mdash; '.count($allResponses[$file]).' Responses</h3>';
				$outR .= kbSurvey_viewResponsesHelper($data,$allResponses[$file],$limit);
				$kbSurvey_num = 1; // restart the numbering
			}
		}

	}else{ // a normal survey, not a randomized one
		$file = KBSURVEY_RESPONSES . '/' . $survey;

		if (file_exists( $file )){
			require_once( $file ); // gives us $responses
			$responses = unserialize( $responses );
			$count = count($responses);
		}else{
			return '<p>No responses yet.</p>';
		}

		$outR = kbSurvey_viewResponsesHelper($kbSurvey_data,$responses,$limit);
	}

	//////////// prepare our final output
	if ($kbSurvey_meta['resultsMsg'])
		$out .= '<div class="kbSurvey_msg notice">'.$kbSurvey_meta['resultsMsg'].'</div>';
	
	$out .= '<p><strong>There have been '.$count.' responses so far</strong>.</p>';

	if (is_null($limit))
		$out .= '<p>All open-ended responses will be displayed. Keep in mind that this is intended for a small number of respondents; everybody\'s comments will be written below, so if there are many respondents, this may take a while to load.</p>';
	elseif (is_int($limit))
		$out .= '<p>Displaying only the most recent '.$limit.' responses to open-ended questions.</p>';
	
	$out .= $outR; // append responses

	return $out;
}
function kbSurvey_viewResponsesHelper($kbSurvey_data,$responses,$limit){
	$out .= '<table class="kbSurveyT">';
	global $kbSurvey_num, $kbSurvey_alt;
	$i = 0;
	foreach( $kbSurvey_data as $task ){
		$args = kbSurvey_getTaskDefaults( $task['type'], $task );
		if (!$args)
			continue;
		$t = kbSurvey_viewTaskResponses( $args['type'], $args, $i, $responses, $limit );
		if (!$t)
			continue;
		if ( $args['numbered'] )
			$out .= '<tr class="'.$kbSurvey_alt.'"><th class="kbSurveyNum">' . $kbSurvey_num++ . '</th>';
		else
			$out .= '<tr class="'.$kbSurvey_alt.'"><th class="kbSurveyNum"></th>';
		$out .= '<td class="kbSurveyTask">' . $t . '</td></tr>';
		$kbSurvey_alt = ('alternate'==$kbSurvey_alt) ? '' : 'alternate';
		$i++;
	}
	$out .= '</table>';
	return $out;
}

function kbSurvey_viewSpreadsheet($kbSurvey_data,$survey){
	if (!current_user_can('administrator'))
		return '<p>Repent, hacker.</p>';
	if (!is_array( $kbSurvey_data ))
		return '<p>This survey has no tasks in it.</p>';
	$file = KBSURVEY_RESPONSES . '/' . $survey; // file where responses to this survey get saved
	if (file_exists( $file )){
		require_once( $file ); // gives us $responses
		$responses = unserialize( $responses );
	}else{
		return '<p>No responses yet.</p>';
	}
	$out1 = '<p>'.count($responses).' responses so far. Displayed using very small font so that you can easily highlight the whole table and paste it into Excel or something. (Keep in mind that this is intended for a small number of respondents; everybody\'s comments will be written below, so if there are many respondents, this will take forever to load.)</p>';
	$out2 .= '<table class="kbSurvey_spreadsheet"><tr class="kbS_spreadsheetTop"><th scope="col">ID</th><th scope="col">Key</th>';

	// prepare the tasks
	global $kbSurvey_defaults;
	$i = -1;
	foreach( $kbSurvey_data as $task ){
		$i++;
		$args[$i] = array_merge( $kbSurvey_defaults[ $task['type'] ], $task );
		if (!$args[$i]['numbered'])
			continue;
		$out2 .= '<th scope="col">'.$args[$i]['type'].'_'.$i.'</th>';
		if ($args[$i]['allowOther'])
			$out2 .= '<th scope="col">'.$args[$i]['type'].'_'.$i.'B</th>';
	}
	$out2 .= '</tr>';
	$i = 1;
	$alt = "alternate";
	foreach( $responses as $k=>$R ){
		$out2 .= '<tr class="'.$alt.'"><th scope="row">'.$i++.'</th><td><span class="kbSNote">'.$k.'</span></td>';
		$c = 0;
		foreach( $R as $n=>$r ){
			if (!$args[$n]['numbered'])
				continue;
			$out2 .= kbSurvey_viewTaskSpreadsheet( $args[$n]['type'], $args[$n], $c++, $r );
		}
		$out2 .= '</tr>';
		$alt = ('alternate'==$alt) ? '' : 'alternate';
	}
	$out2 .= '</table>';
	// to paste into excel/stata/other spreadsheet, can't have any new lines in the spreadsheet (e.g. in student comments). Kill them:
	$out2 = str_replace( '<br />', ' ', $out2 );
	$out = $out1 . $out2;
	return $out;
}

function kbSurvey_selectSurvey($err=false){
	$surveys = kbSurvey_availableSurveys();
	if (!$surveys)
		return '<p>Sorry, but there are not any surveys available at this time.</p>';
	$private = 0;
	foreach( $surveys as $survey ){
		require( KBSURVEY_DATA . '/' . $survey );
		if ($kbSurvey_meta['public'])
			$public[] = array( substr($survey,0,strpos($survey,'.php')), $kbSurvey_meta['title'] );
		else
			$private++;
	}

	if (is_array($public) && $private>0)
		$welcMsg = 'If you have a code to take a private survey, please enter it into the form. Otherwise, select a public survey from the list below.';
	elseif( is_array($public) )
		$welcMsg = 'Please select a public survey from the list below.';
	else
		$welcMsg = 'If you have a code to take a private survey, please enter it into the form below.';

	$out = '<div style="text-align:center;">';
	if ($err)
		$out .= "<p>Sorry, I didn't recognize that survey code. Please check your spelling and capitalization, then try again. $welcMsg</p>";
	else
		$out .= '<p>Welcome. '.$welcMsg.'</p>';
	$out .= '<form method="get" action="'.get_permalink().'"><p>';
	if (is_array($public)){
		$out .= 'Public surveys: <select name="kbsNlist" id="kbsNlist" style="width:200px;"><option value=""> </option>';
		foreach( $public as $p )
			$out .= '<option value="'.$p[0].'">'.$p[1].'</option>';
		$out .= '</select><br />&nbsp;<br />';
	}
	if ($private>0)
		$out .= 'Private survey: <input type="text" name="kbsN" id="kbsN" style="width:200px;" /><br />&nbsp;<br />';
	$out .= '<input type="radio" name="kbsA" id="takeSurvey" value="takeSurvey" checked="checked" /><label for="takeSurvey"> Take survey</label><br /><input type="radio" name="kbsA" id="viewResults" value="viewResults" /><label for="viewResults"> View results</label><br />';
	if (current_user_can('administrator'))
		$out .= '<input type="radio" name="kbsA" id="viewSpreadsheet" value="viewSpreadsheet" /><label for="viewSpreadsheet"> View spreadsheet</label><br /><input type="radio" name="kbsA" id="viewSpreadsheetLong" value="viewSpreadsheetLong" /><label for="viewSpreadsheetLong"> View spreadsheet (long)</label><br />';
	$out .= '&nbsp;<br />';
	$out .= '<input type="submit" value="Submit &raquo;" /><input type="hidden" name="n" id="n" value="'.round( time() / 972 ).'" /></p></form>';

	// Obviously, you can delete the next line if you want. But that's unkind, seeing as I gave you this code without charge. Show a little gratitude, eh?
	$out .= '<p><small>Powered by <a href="http://adambrown.info/b/widgets/category/kb-survey/">KB Survey</a></small></p>';

	$out .= '</div>';
	return $out;
}







//////////////////////////////////////////////////////////////////////
// survey parsing functions (helpers for kbSurvey_takeSurvey() , kbSurvey_submitResponses() , and kbSurvey_viewResponses() )
//////////////////////////////////////////////////////////////////////

// we allow only four types of survey question: instruction, multiple choice, text input, and textarea.

// used by functions below. Takes a task and populates any null properties with default values
function kbSurvey_getTaskDefaults( $type, $args ){
	if (!is_array($args))
		return false;
	global $kbSurvey_defaults;
	$args = array_merge( $kbSurvey_defaults[ $type ], $args );
	if (!is_array($args)) // should never happen
		return false;
	return $args;
}

// general wrapper for showing a task function
function kbSurvey_showTask( $type, $args, $num ){
	global $kbSurvey_num, $kbSurvey_alt;
	$args = kbSurvey_getTaskDefaults( $type, $args );
	if (!$args)
		return;
	$task = call_user_func( 'kbSurvey_show'.$type, $args, $num );
	if (false===$task) // might be FALSE
		return;
	// alright, prepare HTML output:
	$out = '';
	if ( $args['numbered'] )
		$out .= '<tr class="'.$kbSurvey_alt.'"><th class="kbSurveyNum">' . $kbSurvey_num++ . '</th>';
	else
		$out .= '<tr class="'.$kbSurvey_alt.'"><th class="kbSurveyNum"></th>';
	$kbSurvey_alt = ('alternate'==$kbSurvey_alt) ? '' : 'alternate';
	$out .= '<td class="kbSurveyTask">' . $task . '</td></tr>';
	return $out;
}
// general wrapper for saving a task function. Note that we return NULL unless we actually have something to save.
function kbSurvey_saveTask( $type, $args, $num ){
	$args = kbSurvey_getTaskDefaults( $type, $args );
	if (!$args)
		return;
	$task = call_user_func( 'kbSurvey_save'.$type, $args, $num );
	if (false===$task) // might be FALSE. Let's store that as NULL.
		return;
	return $task;
}
// general wrapper for viewing all responses to a particular task.
function kbSurvey_viewTaskResponses( $type, $args, $num, $responses, $limit ){
	return call_user_func( 'kbSurvey_responses'.$type, $args, $num, $responses, $limit );
}
// general wrapper for putting a single response to a single task into the spreadsheet
function kbSurvey_viewTaskSpreadsheet( $type, $args, $num, $response ){
	// tasks are not required to have a spreadsheet callback, but if one exists, it will be used. E.g. the MC task uses a special callback.
	if (!function_exists( 'kbSurvey_spreadsheet'.$type ))
		return '<td>'.$response.'</td>';
	return call_user_func( 'kbSurvey_spreadsheet'.$type, $args, $num, $response );
}

// filter text output of task functions
function kbSurvey_showTaskFilter($c,$strict=false){
	$c = str_replace( "'", '&#8217;', $c );
	$c = str_replace( '"', '&#34;', $c );
	if ($strict)
		$c = wp_filter_kses( $c );
	else
		$c = wp_filter_post_kses( $c );
	$c = str_replace( "\r", '', $c );
	$c = str_replace( "\n", '<br />', $c ); // i want to delete the \n, so I don't use nl2br
	return $c;
}
add_filter( 'kbSurvey_displayTask', 'kbSurvey_showTaskFilter' );

// filter visitor responses before saving
function kbSurvey_saveResponseFilter($c){
	return kbSurvey_showTaskFilter( $c, true );
}
add_filter( 'kbSurvey_saveResponse', 'kbSurvey_saveResponseFilter' );

// type-specific functions

/////// 'Ins'
function kbSurvey_showIns($a,$n){ // instructions only
	return '<div class="kbSurveyPrompt kbSurveyIns">' . apply_filters( 'kbSurvey_displayTask', $a['prompt'] ) . '</div>';
}
function kbSurvey_saveIns($a,$n){
	return false; // nothing to save
}
function kbSurvey_responsesIns($a,$n,$r){
	$out = kbSurvey_showIns( $a, $n );
	return $out;
}

/////// 'MC'
function kbSurvey_showMC($a,$n){ // multiple choice
	if (!is_array( $a['options'] ))
		return false;
	// print prompt
	$out = '<div class="kbSurveyPrompt">' . apply_filters( 'kbSurvey_displayTask', $a['prompt'] ) . '</div><div class="kbSurveyMC">';
	// prepare options
	$i = 0;
	if ($a['allowOther'])
		$onblur = 'onblur="if (\'true\'!=getElementById(\'kbSurvey_MC_'.$n.'_Other\').checked){document.getElementById(\'kbSurvey_MC_'.$n.'_otherText\').value=\'\';}"';
	foreach( $a['options'] as $o ){
		$optionsOut[] = '<p><input type="radio" name="kbSurvey_MC_'.$n.'" id="kbSurvey_MC_'.$n.'_'.$i.'" value="'.$i.'" '.$onblur.' /><label for="kbSurvey_MC_'.$n.'_'.$i.'"> '.apply_filters( 'kbSurvey_displayTask', $o ).'</label></p>';
		$i++;
	}
	if ($a['randomizeOptions'])
		shuffle( $optionsOut );
	foreach( $optionsOut as $oO )
		$out .= $oO;
	// "other" option?
	if ($a['allowOther'])
		$out .= '<p><input type="radio" name="kbSurvey_MC_'.$n.'" id="kbSurvey_MC_'.$n.'_Other" value="other" /><label for="kbSurvey_MC_'.$n.'_Other"> '.apply_filters( 'kbSurvey_displayTask', $a['otherPrompt'] ).'</label> <input type="text" name="kbSurvey_MC_'.$n.'_otherText" id="kbSurvey_MC_'.$n.'_otherText" class="kbSurvey_input" onblur="if (\'\'!=this.value){ document.getElementById(\'kbSurvey_MC_'.$n.'_Other\').checked=true}" /></p>';
	// done
	$out .= '</div>';
	return $out;	
}
function kbSurvey_saveMC($a,$n){
	if (!is_array( $a['options'] ))
		return false;
	$r = $_POST['kbSurvey_MC_'.$n];
	if ($a['allowOther'] && ('other'==$r)){
		$text = apply_filters('kbSurvey_saveResponse', $_POST['kbSurvey_MC_'.$n.'_otherText']);
		if (''==$text)
			$text = $a['otherDefault'];
		return array( 'other', $text );
	}	// else:
	$allowed = array_keys( $a['options'] );
	if (!in_array( $r, $allowed ))
		return false;
	// now we need to distinguish between not selecting a response and selecting the first response (where value="0")
	if (!$r){ // either null or 0
		if (0!==$r && '0'!==$r)
			return false;
	}
	$r = (int) $r;
	return $r;
}
function kbSurvey_responsesMC($a,$n,$r,$lim){
	if (!is_array( $a['options'] ))
		return false;
	// print prompt
	$out = '<div class="kbSurveyPrompt">' . apply_filters( 'kbSurvey_displayTask', $a['prompt'] ) . '</div><div class="kbSurveyMC">';
	// aggregate responses
	$r = array_reverse( $r ); // most recent responses first so we can use $lim
	$l = 0;
	foreach( $r as $R ){
		if (is_null( $R[$n] ) || is_bool( $R[$n] )) // is_bool in case it's false
			continue;
		if (is_array( $R[$n] )){
			$opts[ 'other' ]++;
			if ($R[$n][1] != $a['otherDefault']){
				if ( is_null($lim) || ($lim > $l++) ) // impose our limit, if necessary
					$other .= '<li>'.$R[$n][1].'</li>';
			}
		}else{
			$opts[ $R[$n] ]++;
		}
		$total++;
	}
	$out .= '<table class="kbSurveyBargraph">';
	$i = 0;
	foreach( $a['options'] as $o ){
		if (!$opts[$i])
			$opts[$i] = 0;
		$pct = ($total>0) ? round( 100*$opts[$i]/$total ) : 0; // don't divide by 0
		$out .= '<tr><th>'.apply_filters( 'kbSurvey_displayTask', $o ).'<br /><span class="kbSNote">'.$opts[$i].' responses; '.$pct.'%</span></th><td><div class="kbSBargraphBG"><div class="kbSBargraph" style="width:'. (3*$pct) .'px;"></div></div></td></tr>';
		$i++;
	}
	// "other" option?
	if ($a['allowOther']){
		if (!$opts[ 'other' ])
			$opts['other'] = 0;
		$pct = ($total>0) ? round( 100*$opts['other']/$total ) : 0;
		$out .= '<tr><th>'.apply_filters( 'kbSurvey_displayTask', $a['otherPrompt'] ).'<br /><span class="kbSNote">'.$opts['other'].' responses; '.$pct.'%</span>';
		if ($lim>0)
			$out .= '<ul class="kbSResponses">'.$other.'</ul>';
		$out .= '</th><td><div class="kbSBargraphBG"><div class="kbSBargraph" style="width:'. (3*$pct) .'px;"></div></div></td></tr>';
	}
	// done
	$out .= '</table></div>';
	return $out;	
}
function kbSurvey_spreadsheetMC($args,$num,$response){
	if ($args['allowOther'] && is_array( $response )){
		$out1 = '<td>'.$response[0].'</td>';
		$out2 = '<td>'.$response[1].'</td>';
	}elseif($args['allowOther']){
		$out1 = '<td>'.$response.'</td>';
		$out2 = '<td></td>';
	}else{
		$out1 = '<td>'.$response.'</td>';
	}
	if ('viewSpreadsheetLong'!=$_GET['kbsA'])
		return $out1.$out2;
	if (is_array($response))	// leaving the response number in front of the option text ensures responses will sort properly in spreadsheet
		$out1 = str_replace( $response[0], $response[0].': '.$args['options'][$response[0]], $out1 );
	else
		$out1 = str_replace( $response, $response.': '.$args['options'][$response], $out1 );
	return $out1.$out2;
}


/////// 'TI'
function kbSurvey_showTI($a,$n){ // text <input>
	return '<div class="kbSurveyPrompt kbSurveyTI"><label for="kbSurvey_TI_'.$n.'">' . apply_filters( 'kbSurvey_displayTask', $a['prompt'] ) . '</label></div><p><input type="text" name="kbSurvey_TI_'.$n.'" id="kbSurvey_TI_'.$n.'" class="kbSurvey_input" /></p>';
}
function kbSurvey_saveTI($a,$n){
	return apply_filters( 'kbSurvey_saveResponse', $_POST['kbSurvey_TI_'.$n] );
}
function kbSurvey_responsesTI($a,$n,$r,$lim){
	$out = '<div class="kbSurveyPrompt kbSurveyTI">' . apply_filters( 'kbSurvey_displayTask', $a['prompt'] );
	$out .= '<ul class="kbSResponses">';
	$r = array_reverse( $r ); // most recent responses first
	$l = 0;
	foreach( $r as $R ){
		if (''!=$R[$n]){
			if ( is_null($lim) || ($lim > $l++) ) // impose our limit, if necessary
				$out .= '<li>'.$R[$n].'</li>';
		}
	}
	$out .= '</ul>';
	$out .= '</div>';
	return $out;
}

/////// 'TA'
function kbSurvey_showTA($a,$n){ // textarea
	return '<div class="kbSurveyPrompt kbSurveyTA"><label for="kbSurvey_TA_'.$n.'">' . apply_filters( 'kbSurvey_displayTask', $a['prompt'] ) . '</label></div><p><textarea rows="5" cols="30" name="kbSurvey_TA_'.$n.'" id="kbSurvey_TA_'.$n.'" class="kbSurvey_textarea"></textarea></p>';
}
function kbSurvey_saveTA($a,$n){
	return apply_filters( 'kbSurvey_saveResponse', $_POST['kbSurvey_TA_'.$n] );
}
function kbSurvey_responsesTA($a,$n,$r,$lim){
	$out = '<div class="kbSurveyPrompt kbSurveyTA">' . apply_filters( 'kbSurvey_displayTask', $a['prompt'] );
	$out .= '<ul class="kbSResponses">';
	if (1<count($r))
		$r = array_reverse( $r ); // most recent responses first
	$l = 0;
	foreach( $r as $R ){
		if (''!=$R[$n]){
			if ( is_null($lim) || ($lim > $l++) ) // impose our limit, if necessary
				$out .= '<li>'.$R[$n].'</li>';
		}
	}
	$out .= '</ul>';
	$out .= '</div>';
	return $out;
}






//////////////////////////////////////////////////////////////////////
// utility functions
//////////////////////////////////////////////////////////////////////

// checks whether user has passed a valid survey name
function kbSurvey_validID(){
	if (''==$_GET['kbsN'])	// kbsN means "kbSurvey survey ID Number"
		return false; // user hasn't selected a survey yet
	$requested = $_GET['kbsN'] . '.php';
	$surveys = kbSurvey_availableSurveys();
	if ( in_array( $requested, $surveys ) )
		return $requested;
	$_GET['kbsA'] = 'badID';
	return false;
}

// returns an array of available surveys, or FALSE
function kbSurvey_availableSurveys(){
	$files = scandir( KBSURVEY_DATA );
	if (!is_array( $files ))
		return false;
	foreach( $files as $f ){
		if ( false!==strpos( $f, '.php' ) )
			$out[] = $f;
	}
	if (!is_array( $out ))
		return false;
	// check for our meta tag:
	global $post;
	if ($subset = get_post_meta($post->ID, 'kbsurvey', true)){
		$subset = str_replace( ' ', '', $subset );
		$subset = explode(',', $subset);
		if (!is_array($subset))
			return $out;
		$out = array_intersect( $out, $subset );
		if (is_array($out) && (0<count($out)))
			return $out;
		else
			return false;
	}
	if ($exclude = get_post_meta($post->ID, 'kbsurvey_not', true)){
		$exclude = str_replace( ' ', '', $exclude );
		$exclude = explode(',', $exclude);
		if (!is_array($exclude))
			return $out;
		$out = array_diff( $out, $exclude );
		if (is_array($out) && (0<count($out)))
			return $out;
		else
			return false;
	}		
	return $out;
}

// two functions for randomly selecting a sub-survey from a subdir and returning its filename
function kbSurvey_availableSubsurveys($d){  // $d is subdir to scan
	if (!$d)
		return false;
	// get array of available sub-surveys
	$files = scandir( KBSURVEY_DATA . '/' . $d );
	if (!is_array( $files ))
		return false;
	foreach( $files as $f ){
		if ( false!==strpos( $f, '.php' ) )
			$sub[] = $f;
	}
	if (!is_array( $sub ))
		return false;
	return $sub;
}
function kbSurvey_randomSubsurvey($d){ // $d is subdir to scan
	$sub = kbSurvey_availableSubsurveys($d);
	if (!$sub)
		return false;
	$rand = rand( 0, count($sub)-1 ); // randomly select a key
	$rand = $sub[ $rand ]; // filename to grab
	return $rand;
}




?>