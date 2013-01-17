<?php
/**
 * The default Permission scheme for the Tickets.
 *
 * @package quip
 * @subpackage build
 */
$permissions = array();
$permissions[0][] = $modx->newObject('modAccessPermission',array(
	'name' => 'ticket_delete',
	'description' => 'ticket_delete',
	'value' => true,
));

$permissions[0][] = $modx->newObject('modAccessPermission',array(
	'name' => 'ticket_publish',
	'description' => 'ticket_publish',
	'value' => true,
));

$permissions[0][] = $modx->newObject('modAccessPermission',array(
	'name' => 'ticket_save',
	'description' => 'ticket_save',
	'value' => true,
));

$permissions[0][] = $modx->newObject('modAccessPermission',array(
	'name' => 'comment_save',
	'description' => 'comment_save',
	'value' => true,
));

$permissions[0][] = $modx->newObject('modAccessPermission',array(
	'name' => 'ticket_view_private',
	'description' => 'ticket_view_private',
	'value' => true,
));

$permissions[1][] = $modx->newObject('modAccessPermission',array(
	'name' => 'section_add_children',
	'description' => 'section_add_children',
	'value' => true,
));

return $permissions;