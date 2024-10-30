<?php
/*
Plugin Name: KB Survey
Plugin URI: http://adambrown.info/b/widgets/category/kb-survey/
Description: Administer multi-item surveys via Wordpress. Easily export results to a spreadsheet.
Author: Adam Brown
Version: 0.1
Author URI: http://adambrown.info/
*/


/********************


	*****************************
	INCREDIBLY IMPORTANT NOTES	-	READ THIS (yes, all of it) BEFORE CONTACTING ME ABOUT THIS PLUGIN
	*****************************
	This is a very raw piece of code. It does not have a pretty admin interface. You have to know PHP to use this. I repeat. Knowing PHP is not optional.
	To create a survey, you have to create a PHP file containing the survey's questions, options, and so forth. If you do not know PHP, I'm sorry, but tough luck.
	Maybe if I get motivated I'll put the time in and give this a pretty interface so you don't need to fiddle with the code. But I don't plan to soon.
	Maybe you'd like to. Or maybe you'd like to send a few hundred dollars my way to pay me to do it. :) 
	
	If you do have feature requests...well, I've got a survey up (appropriately enough) at the plugin's homepage that you can take to tell me all about it. Although
	I don't look through those survey results all that often. I mean, it's a demo, not a serious survey. So I guess you could contact me using the contact form at
	my site. But make sure your feature request isn't already listed in the "to do" list below.

	And seriously, please read all of the following documentation before contacting me for support. I'm happy to help if you need it, but I don't have much
	patience for folks who don't try to figure things out on their own first.


	*****************************
	LICENSE, WARRANTY, ETC
	*****************************
	THIS IS BETA SOFTWARE. NO WARRANTY OR GUARANTEES OF ANY KIND ACCOMPANY THIS SOFTWARE, NOT EVEN IMPLIED WARRANTIES OF 
	FUNCTIONALITY OR SUITABILITY FOR A PARTICULAR PURPOSE. YOU USE THIS AT YOUR OWN FREAKING RISK.

	The biggest security threat for software like this is that a single user may try to answer the survey several times in a row to throw off the results. That's not
	really a security threat, though, more like a threat to the validity of your results. But do note that I employ only basic safeguards against that. Maybe someday
	I'll do something fancy like log IP addresses.(As for more serious threats like XSS and whatnot, they're well taken care of by KSES.)


	*****************************
	INSTRUCTIONS
	*****************************
	(1) 	Create a PAGE (not a post) and put [kbsurvey] on a SEPARATE line in the page--I strongly recommend leaving the rest blank. All your surveys will be 
		administered from this one page, so give it a generic title like "Surveys". (If you want to have a separate page for each survey, rather than administering
		all your surveys from a single page, keep reading for instructions.)
	(2)	Optional: Also put [kbsurvey] at the end of the page's title. This will tell the plugin to replace the page's title with the name of the survey currently
		being viewed/taken.
	(3)	Open up /kbSurvey/surveys/demo.php and look at it. To create a survey, copy demo.php (in the same directory), rename it, and modify the contents as desired.
		You'll find lots of commenting in demo.php explaining how to set up your survey. Please read the commenting carefully.
	(4)	Save your new survey in the same directory as demo.php. If the survey is public, people will be able to take the survey by selecting it from the drop-down 
		"public surveys" box. If it is private, they will type the survey's filename (minus the '.php' suffix) into the "private surveys" box to take the survey.
	(5)	Optional, but recommended: The plugin puts some CSS into your template's <head>. These styles were chosen to make the surveys work in my custom 
		theme. They will look awful in most other themes. To change the styles, look below (in this file) for a function called kbSurveyHead() and change the
		styles in there.

	You shouldn't need to edit any of the files that came with this plugin unless you want to modify the functionality. All you need to edit are the survey files, as
	described above.
	
	TO LIMIT WHICH SURVEYS RESPONDENTS CAN CHOOSE
	Each survey can have a begin and end time, which is the easiet way to control availability. But suppose you want to have more than one PAGE that uses this 
	plugin, and you want to exclude some surveys from one page and exclude other surveys from the other page. Or maybe you want a separate PAGE for each
	survey you write. Here's how to do that:
		If you have created several surveys, but you want only one or a certain set to be available, add a meta tag (custom field) to your page. Key must be 'kbsurvey', value
	is a comma-separated list of survey filenames to allow. E.g. if you have demo.php, demo2.php, and demo3.php in the /kbSurvey/surveys/ directory, but you want 
	to show only the first and third, then your custom field with key "kbsurvey" would have value "demo.php,demo3.php" (without the quotes). To EXCLUDE specific 
	surveys rather than specify particular ones for inclusion, name your key 'kbsurvey_not' instead. In this example, 'kbsurvey_not' would have value 'demo2.php'. 
	(If you use both custom fields, only 'kbsurvey' will be used--'kbsurvey_not' will be ignored entirely.)


	*****************************
	TROUBLESHOOTING - READ BEFORE CONTACTING ME FOR SUPPORT
	*****************************
	Supporting this plugin is a very, very, very, very, very LOW priority for me. Do not contact me for support unless you have tried your best to fix the problem
	yourself. And if you do fix it, send me a note so I can update the code. Here's a couple pointers:
	* 	Make sure that /responses/ is writable by your server. (CHMOD)
	* 	Developed on PHP 5, WPMU 1.2.5, so might be a problem if you're on another version of PHP or WP.
	* 	Though I developed this on WPMU, it is NOT safe for WPMU (yet) and I do not recommend it for that purpose. Use only on WP.
	* 	You must be logged in as an administrator to do certain actions. This should be obvious, though, since you'll get a message about not being logged in,
		or something like "Repent, hacker" will show up on your screen.
	*	Most of the links that this code produces assume that you have permalinks on. If there's already a "?" in your URL other than this plugin's parameters,
		then some of the links this plugin generates won't work. Sorry.


	*****************************
	TO DO LIST FOR DEVELOPERS - Read this if you'd like to improve this plugin. And if you've got a feature request, don't send it to me without checking the list below.
	*****************************
	I wrote this to administer small scale surveys to a specific group of people for a specific purpose. This plugin worked well for me, so I thought I'd
	make the code available to others. But it is NOT a polished, completed program. If you choose to develop it further, by all means, do so. Send me your enhancements.
	Please bugcheck them first so I don't have to. If you make a significant contribution (i.e. knocking a couple items off the list below), also send me your wordpress.org
	username so I can credit you on the plugin's download page over at wp.org.

	These are the things that most need to be done. You can see from the size of this list why I'm not likely to do it myself any time soon. Maybe if there is LOTS of demand
	(which I don't expect) I'll do some of this myself.

	(1)	Use the mysql database instead of flat files to hold responses. Requires creating a new table to hold surveys and responses in--don't store using add_option()
	(2)	Needs an admin interface for creating surveys and setting survey options. Like responses, surveys should be stored in a newly created table. Note that this interface
		should prevent users from violating the warning described in the demo survey included with this plugin. (the warning about not editing the survey after people have
		started taking it)
	(3)	The CSS in this file (further down) is designed only to go with my custom theme. Might be nice to include an option via the WP interface to pick one
		of three or four styles to use with the survey to make it more flexible from theme to theme. So maybe you'd like to figure out three or four variants on the
		styles I provide, taking care to select styles that are generic enough too work on a variety of themes.
	(4)	It would be nice to replace the "viewSpreadsheet" functionality with an option to download a csv file with all the data.
	(5)	There should be a way to make (private) surveys answerable by invitation only--e.g. you give a list of email addresses, and each one gets sent a unique key allowing the
		recipient to take the survey. And for public surveys, IP logging to prevent double-answering might be nice, though that's not always the best approach.
	(6)	Ajaxifying this thing would be awesome. But that's a big job and has lower priority than the other items I've listed.
	(7)	Needs internationalization.
	(8)	Need more task types. E.g.
		- multiple choice, but allow multiple answers to be selected
		- instruction blocks that allow you to insert an image or other attachment, not just text
		- validation options for the TI (TextInput) fields--e.g. require users to format their answer as MM-DD-YYYY, or (xxx) xxx-xxxx, etc.
		- i'm sure there will be other things that people request.
	(9)	Logic would be cool. That is, an option to adjust which question comes next based on the answer to a previous question. This would also require either
		ajaxification or pagination (preferably ajax).
	(10)	Ability to make a task required (so respondents cannot leave it blank).
	(11)	Should record a timestamp of when the survey was submitted. The closes we get now is in the respondent's unique ID.
	(12)	Multiple choice options should have a "display order" option, so that if you want to reorder their display after people have already started taking the survey, you can (without
		screwing up earlier responses).

	Note that none of these require fiddling with the code I've already written much (though fiddling would probably improve it). They require new files and
	new functionalities. To my knowledge, the code I've written works fine, at least on my setup. (But remember, no warranties or guarantees.)

	*****************************
	FAQ - If the material above didn't answer your question
	*****************************
	Q: What's the difference between "View Spreadsheet" and "View Spreadsheet (long)"?
		A: They're the same except for one thing. In the long form, responses to multiple choice questions are displayed as text. In the short form, they're displayed
		using integers corresponding to which option was selected. Integers are easier to download and analyze statistically, but use the long form if you find yourself
		forgetting which integers correspond with which answers.
	Q: The colors look horrendous on my site!
		A: See above, 'Instructions'
	Q: How do I get rid of that "powered by kbsurvey" link?
		A: That wouldn't be very nice, would it? I mean, I gave this to you for free; the least you can do is give me some google juice. If you seriously want to be an 
		ingrate, search through functions.inc.php and you'll figure out what to delete easily enough.
	Q: Help! I'm not very good with PHP and don't know how to create my survey!!
		A: Not to be rude, but... you're on your own with questions like this. See the materials above for info and tips.





	END OF DOCUMENTATION
	






********************/




// we need this for filters to work right
if (''==$_GET['kbsN'] && ''!=$_GET['kbsNlist'])
	$_GET['kbsN'] = $_GET['kbsNlist'];



// lest we gum up wordpress, we keep this file really lightweight and don't include the rest of the plugin unless the filter finds a need to

//////////////////////////////////////////////////
////////////////// FILTERING FUNCTIONS
//////////////////////////////////////////////////

// filters the page's title
function kbSurveyTitleFilter($t){ // note that this will run on any page if the ?kbsN and ?kbsA vars are set.
	if (false===stripos( $t, '[kbsurvey]' )) // case insensitive
		return $t;
	$t = str_ireplace( '[kbsurvey]', '', $t ); // get rid of that...
	// we did the previous steps before checking whether we're on a page b/c we don't want page widgets to display the '[kbsurvey]' part of the title.
	if (!is_page())
		return $t;
	if (''==$_GET['kbsA'] || ''==$_GET['kbsN'])
		return $t;	// don't need to put a survey's name in place of the page's name if there's not a survey selected...
	return kbSurveyFilter($t, true);
}
// filters the page's content
function kbSurveyContentFilter($c){ // looks for "[kbsurvey]" in page
	if (!is_page())
		return $c;
	if (false===stripos( $c, '[kbsurvey]' )) // case insensitive
		return $c;
	return kbSurveyFilter( $c );
}
// filters wp_title, which shows up in the browser at the very top
function kbSurveyWPTitleFilter($t){
	return trim( str_ireplace( '[kbsurvey]', '', $t ) ); // get rid of that...
}
add_filter( 'the_content', 'kbSurveyContentFilter' );
add_filter( 'the_title', 'kbSurveyTitleFilter' );
add_filter( 'wp_title', 'kbSurveyWPTitleFilter' );

// general filtering function called by the previous three filters if necessary
function kbSurveyFilter($c,$title=false){
	if (!defined('KBSURVEY')){
		define('KBSURVEY', dirname(__FILE__) ) ; // abs path to the kbSurvey fold
		define('KBSURVEY_DATA', KBSURVEY.'/surveys' ); // abs path to folder where we store survey questions
		define('KBSURVEY_RESPONSES', KBSURVEY.'/responses' ); // abs path to folder where we store survey responses
	}
	require_once( KBSURVEY.'/inc/functions.inc.php' );	// include the rest of the plugin's functions
	
	if ($title){
		$t = kbSurvey_display(true);
		if (''!=$t)
			return $t;
		return $c; // don't change the_title if the requested survey has no title defined
	} // ELSE:
	
	$surveyC = kbSurvey_display();

	$newC = str_ireplace( '<p>[kbsurvey]</p>', $surveyC, $c ); // case insensitive
	if ($c==$newC)
		$newC = str_ireplace( '[kbsurvey]', $surveyC, $c ); // just in case we didn't get it last time. Expect validation errors if it comes to this.
	return $newC;
}







//////////////////////////////////////////////////
////////////////// ACTION HOOK FUNCTIONS
//////////////////////////////////////////////////

// anything you want inserted into the blog header, put it here:
function kbSurveyHead(){
	// try to avoid putting stuff into the <head> if it's not necessary:
	if (!is_page())
		return;
	if (''==$_GET['kbsA'] || ''==$_GET['kbsN'])
		return;		// don't need survey styles if there's not a survey selected...

	echo '
	<style type="text/css"><!--
	/*general*/
	.alternate td,.alternate th{background:#ddb;}
	th.kbSurveyNum,td.kbSurveyTask{vertical-align:top;padding:1em 0.5em;}
	.kbSurvey_textarea{width:75%;}
	.kbSNote{font-size:80%;color:#775;}
	/*bargraph*/
	.kbSurveyBargraph{margin-top:1em;}
	.kbSurveyBargraph th,.kbSurveyBargraph td{padding:0 0.5em 0.5em;}
	.kbSurveyBargraph th{font-weight:normal;text-align:right;width:50%;}
	.kbSurveyBargraph th ul{text-align:left;}
	.kbSurveyBargraph td{}
	.kbSBargraphBG{margin:0;width:300px;padding:0;border:solid 1px #886;}
	.kbSBargraph{background:#005;height:2em;margin:0;padding:0;}
	/*viewing responses*/
	.kbSurvey_RandSub{margin:1.5em 0 0 0.5em;}
	ul.kbSResponses{margin:1em 0 0;padding:0;}
	ul.kbSResponses li{margin:0.2em 0;padding:0.1em 0.2em;background:#bcb;list-style-type:none;}
	/*spreadsheet*/
	.kbS_spreadsheetTop{background:#aaa;font-size:80%;}
	.kbSurvey_spreadsheet td{font-size:60%;}
	// -->
	</style>
	';
}
add_action( 'wp_head', 'kbSurveyHead' );








//////////////////////////////////////////////////
//////////// GLOBAL VARS
//////////////////////////////////////////////////

// a couple vars need global scope, so let's put them here for lack of a better place
$kbSurvey_num = 1;

// note that every task type has a name (the array key, here) and a 'prompt'
// 'numbered' indicates whether the task solicits a response (hence should have a task number) or merely contains information (e.g. instructions).
// if a task allows double responses--e.g. MC allows you to select the "Other" box, then fill out a text box to specify--give it 'allowOther'=true so the spreadsheet knows to make two columns.
// note that all possible options are listed here. They can be overriden in your survey file. See kbSurvey/surveys/demo.php for an example.
$kbSurvey_defaults = array(
	'Ins' => array(			// Instructions--accepts no response
		'prompt' => '',
		'numbered' => false,	// not typically something you would bother to override... Indicates that this task does NOT require user response, hence it's not numbered
	),
	'MC' => array(			// Multiple choice--accepts only one answer
		'prompt' => '',
		'options' => false, // should be an array (values only) of possible answers to the prompt. If you want people to be able to write in their "other", use the options below instead of providing an "other" option.
		'randomizeOptions' => false, // randomize options or give in order listed?
		'allowOther' => false, // you can always specify "Other" as an option and set this false. Setting this true allows you to check "other" and then write something in.
		'otherPrompt' => 'Other (please specify)', // if allowOther is true, this is the prompt we give
		'otherDefault' => 'Other', // if allowOther is true, this is what we write in the bargraph if they didn't type something into the text field
		'numbered' => true,	 // not typically something you would bother to override...
	),
	'TI' => array(			// Text Input--for one- or two-word responses
		'prompt' => '',
		'numbered' => true,	 // not typically something you would bother to override...
	),
	'TA' => array(			// Text Area--for open-ended responses
		'prompt' => '',
		'numbered' => true,	 // not typically something you would bother to override...
	),
);
$kbSurvey_taskTypes = array_keys( $kbSurvey_defaults );
$kbSurvey_alt = 'alternate';



?>