<?php
if (empty($scriptProperties['thread'])) {$scriptProperties['thread'] = 'resource-'.$modx->resource->id;}

$Tickets = $modx->getService('tickets','Tickets',$modx->getOption('tickets.core_path',null,$modx->getOption('core_path').'components/tickets/').'model/tickets/',$scriptProperties);
if (!($Tickets instanceof Tickets)) return '';

if ((empty($action) || $action == 'getComments') && !empty($_REQUEST['action'])) {$action = $_REQUEST['action'];}
if (empty($action)) {$action = 'getComments';}

$output = null;
switch ($action) {
	case 'previewComment': $output = $Tickets->previewComment($scriptProperties); break;
	case 'saveComment': $output = $Tickets->saveComment($scriptProperties); break;
	case 'getComments': $output = $Tickets->getCommentThread($scriptProperties['thread']); break;
}

if (is_array($output)) {
	$output = json_encode($output);
}

// Support for ajax requests
if (!empty($output) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
	//$output = preg_replace('/\[\[(.*?)\]\]/', '', $output);
	$maxIterations= (integer) $modx->getOption('parser_max_iterations', null, 10);
	$modx->getParser()->processElementTags('', $output, false, false, '[[', ']]', array(), $maxIterations);
	$modx->getParser()->processElementTags('', $output, true, true, '[[', ']]', array(), $maxIterations);

	echo $output;
	exit;
}

return $output;