<?php
/* @var array $scriptProperties */
if (!empty($cacheKey) && $output = $modx->cacheManager->get('tickets/latest.'.$cacheKey)) {
	return $output;
}

/* @var Tickets $Tickets */
$Tickets = $modx->getService('tickets','Tickets',$modx->getOption('tickets.core_path',null,$modx->getOption('core_path').'components/tickets/').'model/tickets/',$scriptProperties);
$Tickets->initialize($modx->context->key, $scriptProperties);
/* @var pdoFetch $pdoFetch */
$pdoFetch = $modx->getService('pdofetch','pdoFetch', MODX_CORE_PATH.'components/pdotools/model/pdotools/',$scriptProperties);
$pdoFetch->addTime('pdoTools loaded.');

if (empty($action)) {$action = 'comments';}
$where = ($action == 'tickets') ? array('class_key' => 'Ticket') : array();

if (empty($showUnpublished)) {$where['Ticket.published'] = 1;}
if (empty($showHidden)) {$where['Ticket.hidemenu'] = 0;}
if (empty($showDeleted)) {$where['Ticket.deleted'] = 0;}
if (!empty($user)) {
	$user = array_map('trim', explode(',', $user));
	$user_id = $user_username = array();
	foreach ($user as $v) {
		if (is_numeric($v)) {$user_id[] = $v;}
		else {$user_username[] = $v;}
	}
	if (!empty($user_id) && !empty($user_username)) {
		$where[] = '(`User`.`id` IN ('.implode(',',$user_id).') OR `User`.`username` IN (\''.implode('\',\'',$user_username).'\'))';
	}
	else if (!empty($user_id)) {$where['User.id:IN'] = $user_id;}
	else if (!empty($user_username)) {$where['User.username:IN'] = $user_username;}
}

// Filter by ids
if (!empty($resources)){
	$resources = array_map('trim', explode(',', $resources));
	$in = $out = array();
	foreach ($resources as $v) {
		if (!is_numeric($v)) {continue;}
		if ($v < 0) {$out[] = abs($v);}
		else {$in[] = $v;}
	}
	if (!empty($in)) {$where['id:IN'] = $in;}
	if (!empty($out)) {$where['id:NOT IN'] = $out;}
}
// Filter by parents
else {
	if (!empty($parents) && $parents > 0) {
		$pids = array_map('trim', explode(',', $parents));
		$parents = $pids;
		if (!empty($depth) && $depth > 0) {
			foreach ($pids as $v) {
				if (!is_numeric($v)) {continue;}
				$parents = array_merge($parents, $modx->getChildIds($v, $depth));
			}
		}
		if (!empty($parents)) {
			$where['Ticket.parent:IN'] = $parents;
		}
	}
}

// Adding custom where parameters
if (!empty($scriptProperties['where'])) {
	$tmp = $modx->fromJSON($scriptProperties['where']);
	if (is_array($tmp)) {
		$where = array_merge($where, $tmp);
	}
}
unset($scriptProperties['where']);
$pdoFetch->addTime('"Where" expression built.');

// Joining tables
if ($action == 'comments') {
	$resourceColumns = !empty($includeContent) ?  $modx->getSelectColumns('Ticket', 'Ticket', 'ticket.') : $modx->getSelectColumns('Ticket', 'Ticket', 'ticket.', array('content'), true);
	$commentColumns = !empty($includeContent) ?  $modx->getSelectColumns('TicketComment', 'TicketComment') : $modx->getSelectColumns('TicketComment', 'TicketComment', '', array('text','raw'), true);
	$class = 'TicketComment';
	$innerJoin = array(
		empty($user)
			? '{"class":"TicketThread","alias":"Thread","on":"TicketComment.id=Thread.comment_last AND Thread.closed=0 AND Thread.deleted=0"}'
			: '{"class":"TicketThread","alias":"Thread","on":"TicketComment.thread=Thread.id AND Thread.closed=0 AND Thread.deleted=0"}'
		,'{"class":"modResource","alias":"Ticket","on":"Ticket.id=Thread.resource"}'
	);
	$leftJoin = array(
		'{"class":"modResource","alias":"Section","on":"Section.id=Ticket.parent"}'
		,'{"class":"modUser","alias":"User","on":"User.id=TicketComment.createdby"}'
		,'{"class":"modUserProfile","alias":"Profile","on":"Profile.internalKey=User.id"}'
	);
	$select = array(
		'"TicketComment":"'.$commentColumns.'"'
		,'"Ticket":"'.$resourceColumns.'"'
	);
	$groupby = empty($user) ? 'Ticket.id' : 'TicketComment.id';
	$where['TicketComment.deleted'] = 0;
}
else if ($action == 'tickets') {
	$resourceColumns = !empty($includeContent) ?  $modx->getSelectColumns('Ticket', 'Ticket') : $modx->getSelectColumns('Ticket', 'Ticket', '', array('content'), true);
	$class = 'Ticket';
	$innerJoin = array(
		'{"class":"TicketThread","alias":"Thread","on":"Thread.resource=Ticket.id AND Thread.closed=0 AND Thread.deleted=0"}'
	);
	$leftJoin = array(
		'{"class":"TicketComment","alias":"TicketComment","on":"TicketComment.thread=Thread.id AND TicketComment.published=1"}'
		,'{"class":"TicketsSection","alias":"Section","on":"Section.id=Ticket.parent"}'
		,'{"class":"modUser","alias":"User","on":"User.id=Ticket.createdby"}'
		,'{"class":"modUserProfile","alias":"Profile","on":"Profile.internalKey=User.id"}'
	);
	$select = array(
		'"Ticket":"'.$resourceColumns.'"'
		,'"Thread":"`Thread`.`id` as `thread`"'
	);
	$groupby = 'Ticket.id';
}
else {return 'wrong action.';}

// Fields to select
$sectionColumns = $modx->getSelectColumns('TicketsSection', 'Section', 'section.', array('content'), true);
$userColumns = $modx->getSelectColumns('modUser', 'User', '', array('username'));
$profileColumns = $modx->getSelectColumns('modUserProfile', 'Profile', '', array('id'), true);

$select = array_merge($select, array(
	'"Section":"'.$sectionColumns.'"'
	,'"User":"'.$userColumns.'"'
	,'"Profile":"'.$profileColumns.'"'
));

$default = array(
	'class' => $class
	,'where' => $modx->toJSON($where)
	,'innerJoin' => '['.implode(',',$innerJoin).']'
	,'leftJoin' => '['.implode(',',$leftJoin).']'
	,'select' => '{'.implode(',',$select).'}'
	,'sortby' => 'createdon'
	,'sortdir' => 'DESC'
	,'groupby' => $groupby
	,'return' => 'data'
	,'nestedChunkPrefix' => 'tickets_'
);

// Merge all properties and run!
$pdoFetch->setConfig(array_merge($default, $scriptProperties));
$pdoFetch->addTime('Query parameters are prepared.');
$rows = $pdoFetch->run();

// Processing rows
$output = '';
if (!empty($rows) && is_array($rows)) {
	foreach ($rows as $k => $row) {

		// Processing main fields
		$row['comments'] = $modx->getCount('TicketComment', array('thread' => $row['thread'], 'published' => 1));
		$row['idx'] = $pdoFetch->idx++;

		if ($class == 'Ticket') {
			$row['date_ago'] = $Tickets->dateFormat($row['createdon']);
			$properties = is_string($row['properties'])
				? $modx->fromJSON($row['properties'])
				: $row['properties'];
			if (empty($properties['process_tags'])) {
				foreach ($row as $field => $value) {
					$row[$field] = str_replace(array('[',']'), array('&#91;','&#93;'), $value);
				}
			}
		}
		else {
			$row['resource'] = $row['ticket.id'];
			$row = $Tickets->prepareComment($row);
		}

		// Processing chunk
		$tpl = $pdoFetch->defineChunk($row);
		$output[] = empty($tpl)
			? '<pre>'.$pdoFetch->getChunk('', $row).'</pre>'
			: $pdoFetch->getChunk($tpl, $row, $pdoFetch->config['fastMode']);
	}
	$pdoFetch->addTime('Returning processed chunks');
	if (empty($outputSeparator)) {$outputSeparator = "\n";}
	if (!empty($output)) {
		$output = implode($outputSeparator, $output);
	}
}

if (!empty($cacheKey)) {
	$modx->cacheManager->set('tickets/latest.'.$cacheKey, $output, 1800);
}

if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
	$output .= '<pre class="TicketLatestLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

if (!empty($toPlaceholder)) {
	$modx->setPlaceholder($toPlaceholder, $output);
}
else {
	return $output;
}