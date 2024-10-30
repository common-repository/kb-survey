<?php

/*
	HOW TO CREATE A SURVEY
	1.	Copy this file. Rename it. The name should be something brief and descriptive that visitors to your site could type into a form without difficulty.
	2.	This file MUST contain two arrays: $kbSurvey_meta and $kbSurvey_data. They must have the same structure and names as the arrays below.
		Look through this file's comments for further instructions.
	3.	Upload to the same folder as this file and you're done--people can start taking your survey.
*/



// FIRST we'll set our survey's options:


// because this is here is a sample survey, it lists all possible options in the meta. Only "title" is required. The rest can be omitted (or set false).
// any option not specifically set otherwise will be presumed FALSE unless specified otherwise below.
$kbSurvey_meta = array(

	// will be used to replace the page's title in your theme. Required.
	'title' => 'Demo Survey',
	
	// display this survey as part of a list of available surveys (TRUE), or keep it private unless visitors can type the survey's slug into the form (FALSE)? Defaults to FALSE.
	'public' => true,

	// FALSE to make the survey available immediately; a Unix timestamp to make it available later. (This will be in the server's time zone.) Defaults to FALSE.
	'begin' => mktime( 0, 0, 0, 10, 25, 2007 ), // hour, minute, second, month, date, year

	// FALSE if no end time, unix timestamp otherwise. (i.e. same input type as 'begin'). Defaults to FALSE.
	'end' => false, 

	// if you would like to display a message above the survey results, put it in here. Defaults to NULL.
	'resultsMsg' => null,

	// can public see survey results, or only administrators? (Note that the public never has access to the spreadsheet view of the results, only to the basic HTML view.)
	// TRUE to allow the public to see results. Defaults to FALSE.
	'publicResults' => true,
	
	// if 'publicResults'==TRUE:	would you like to limit how many open-ended responses get shown public results? (doesn't apply if you're logged on as admin)
	// set to INTEGER to show X most recent comments. Set to 0 or FALSE to show no responses to open-ended questions (but you'll still display the Multiple Choice 
	// bargraphs). Set to NULL to show all responses, no matter how many there are (default).
	'publicResultsOpenLimit' => 2,
	
	// same idea as preceding setting, but this one affects only the administrator's view. Defaults to NULL.
	'adminResultsOpenLimit' => 100,

	// if 'publicResults'==TRUE:	When do the results become available to the public? FALSE if they are available starting now, or a unix timestamp to make them not available
	// until later. Defaults to FALSE. (Does not apply to administrators, only to the public. Administrators can view results at any time.)
	'publicResultsWhen' => false,

	// Maximum number of times your survey can be submitted. Provide an integer, or set to FALSE for unlimited responses. Defaults to FALSE.
	'maxResponses' => 5000,

	// on completion, would you like to give a unique confirmation code to each respondent so they can prove they took the survey?
	// (useful if you're a teacher and you give students extra credit for taking surveys, otherwise, probably want to leave on false.)
	// you can get a list of all valid confirmation codes via the spreadsheet view. Defaults to FALSE.
	'giveReceipt' => false,

	// on completion, should we give the respondent a link to view results? Either FALSE or an integer; integer is number of submissions that must occur before 
	// displaying invitation to see survey results. Defaults to FALSE.
	'inviteResults' => 1,
);







// SECOND we'll define the survey's contents:




/*
	WARNING
	WARNING
	WARNING
	
	DO NOT change $kbSurvey_data AFTER people have started submitting your survey. Things will really get screwed up. Only the following changes are safe:
	*	Correcting a spelling error
	*	Adding new tasks to the END of the survey
	*	Adding additional options to a multiple choice question, but at the END of the list of existing options
	*	Setting a MC task's 'allowOther' parameter from FALSE to TRUE, but not the other way around.
	*	Changing a MC tasks's 'otherPrompt', 'otherDefault', and 'randomizeOptions' settings.
	*	Switching a task between TA and TI type (TA stands for <TextArea>, TI for Text <Input>)
	
	Why is this the case? Respondents' answers are stored in an array very similar to the one you define below, using the same (implicit) array keys as $kbSurvey_data.
	If you add tasks anywhere but at the end of your survey, then the responses array will no longer have the same keys as $kbSurvey_data, and your responses will
	no longer make any sense whatsoever.
	
	WARNING
	WARNING
	WARNING
*/
// here come the survey's contents. Take a look at $kbSurvey_defaults in kbSurvey.php to see what the default options are for each task type, and to see which task types
// are available. You MUST specify 'type' and 'prompt' for each task. Specific task types may have additional required properties. (e.g. multiple choice requires 'options').
// Note the structure of the survey data: It is a multidimensional array with unnamed keys. Each array item is a survey task, which in turn is an array with a minimum of 'type' and 'prompt'. 
// DID YOU READ THE WARNING?
$kbSurvey_data = array(
	array(
		'type' => 'Ins',
		'prompt' => '<strong>Instructions: </strong> You can answer as many or as few questions as you like. Afterwards, you will be able to view aggregate responses to multiple choice questions, but only the most recent responses to open-ended questions.',
	),
	array(
		'type' => 'TA',
		'prompt' => 'What do you look for in a polling plugin?',
	),
	array(
		'type' => 'MC',
		'prompt' => "What blogging software do you most prefer? (FYI, the order of the options on this question is randomized when taking the survey, but not when viewing the results.)",
		'options' => array(
			"Blogger",
			"Moveable Type",
			"Wordpress",
		),
		'allowOther' => 1,
		'otherPrompt' => 'Other (please specify)',
		'otherDefault' => 'Other',
		'randomizeOptions' => 1,
	),
	array(
		'type' => 'TA',
		'prompt' => 'Why?',
	),
	array(
		'type' => 'TI',
		'prompt' => "What's the first word that comes to mind when you think of <em>blogging</em>?",
	),
	array(
		'type' => 'MC',
		'prompt' => "How good are you with PHP? (The order isn't randomized this time.)",
		'options' => array(
			"Master among Masters",
			"Not so bad",
			"Noob, but I know a little",
			"pee eh-ch what?",
		),
	),
);

?>