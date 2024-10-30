<?php

/*
	HOW TO CREATE A SURVEY
	
	Take a look at demo.php (in this directory) for thorough instructions about how to make a survey. 
	
	This demo demonstrates an advanced use of this plugin, but it assumes that you already are familiar with the instructions in demo.php.
	
	RANDOMLY SELECTING A SURVEY
	Often, survey researchers find it useful to randomly give subgroups of respondents a slightly different survey. For example, survey respondents might be divided into
	two groups. Both groups might answer almost the exact same survey, but with a difference in the instructions, or with a few questions worded slightly differently. Usually,
	you do this with one of two goals:
		* To keep the survey length reasonable. If you have 20 necessary questions and 20 interesting (but less necessary) questions, maybe you'll give the
			first 20 questions to all respondents, but only give 10 of the next 20 to one group and give the other 10 to the other group.
		* To see whether question wording matters. Sometimes researchers want to know how respondents react to subtle changes in question wording and instructions.
	
	HOW TO DO IT
	You know from demo.php that every survey has $kbSurvey_meta and $kbSurvey_data. To randomly select a survey, we'll separate those.
	You'll see below that this file has $kbSurvey_meta but NOT $kbSurvey_data. However, there are two different files in the /kbSurvey/surveys/randomDemoDir/, and
	those contain the $kbSurvey_data array.
		All we do to make this work is add a 'randomDir' property to the meta array, as shown below. Its value is the name of the subdirectory that contains
	the surveys we'll choose from. It MUST be a subdirectory of the current directory. Each file in this subdir MUST have .php as their extension.
		When you view responses, you will see the responses to each survey displayed separately. You can modify how exactly they display using the 'randomParallel' setting
	in the meta array, below.
		When you view the spreadsheet, there will be a separate spreadsheet for each survey.
*/






$kbSurvey_meta = array(


	// This is the critical setting. Its presence tells the plugin to randomly select a sub-survey. The value should be the name of the subdirectory that holds the surveys.
	'randomDir' => 'randomDemoDir',
	
	// In "View Responses," should we display the results of the sub-surveys one after another (FALSE, default behavior) or side-by-side (TRUE)?
	// Don't set this to TRUE unless the two surveys have the same structure (i.e. same number of tasks, same task types, etc.), or I guarantee this will look screwy.
	'randomParallel' => true,	// This setting only works if there are TWO sub-surveys. More than that and this setting gets ignored.
	

	// the 'resultsMsg' setting is always available (see demo.php for explanation), but you should probably use it if you've got a randomizing questionnaire.
	'resultsMsg' => '<strong>About the Random Survey Demo:</strong> Participants in this survey were presented a randomly-selected questionnaire from those shown below.',


	// The remainder of the meta settings work the same way as the settings in demo.php.
	// Only a handful are shown below; the rest are left at their defaults.
	'title' => 'Random Survey Demo',
	'public' => true,
	'publicResults' => true,
	'inviteResults' => 2,
	'publicResultsOpenLimit' => 2,
	'adminResultsOpenLimit' => 100,
	'maxResponses' => 5000,
);

?>