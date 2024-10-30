<?php

/*
	Since this is a sub-survey of randomDemo.php, it does not contain $kbSurvey_meta.
	See randomDemo.php for information about this survey. See demo.php for general instructions.
*/







$kbSurvey_data = array(
	array(
		'type' => 'Ins',
		'prompt' => '<strong>Instructions: </strong> To see why this survey is called "Random Survey Demo," click "View Results" after submitting your response.',
	),
	array(
		'type' => 'MC',
		'prompt' => '<p>Imagine that the United States is preparing for the outbreak of an unusual Asian disease, which is expected to kill 600 people unless we can implement countermeasures. Two alternative programs have been proposed. Suppose that scientists have estimated that the two programs would have the following effects:</p><ul><li>If program A is adopted, 400 people will die.</li><li>If program B is adopted, there is a one-third probability nobody will die, and a two-thirds probability that 600 people will die.</li></ul><p>Which of these two programs do you favor?</p>',
		'options' => array(
			'Program A',
			'Program B',
		),
	),
);

?>