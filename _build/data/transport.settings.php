<?php
/**
 * Loads system settings into build
 *
 * @package tickets
 * @subpackage build
 */
$settings = array();

$settings[0]= $modx->newObject('modSystemSetting');
$settings[0]->fromArray(array(
	'key' => 'tickets.enable_editor'
	,'value' => 'true'
	,'xtype' => 'combo-boolean'
	,'namespace' => 'tickets'
	,'area' => 'Editor'
),'',true,true);

$settings[1]= $modx->newObject('modSystemSetting');
$settings[1]->fromArray(array(
	'key' => 'tickets.editor_config.ticket'
	,'value' => '{onTab: {keepDefault:false, replaceWith:"	"}
	,markupSet: [
		{name:"Bold", className: "btn-bold", key:"B", openWith:"<b>", closeWith:"</b>" }
		,{name:"Italic", className: "btn-italic", key:"I", openWith:"<i>", closeWith:"</i>"  }
		,{name:"Underline", className: "btn-underline", key:"U", openWith:"<u>", closeWith:"</u>" }
		,{name:"Stroke through", className: "btn-stroke", key:"S", openWith:"<s>", closeWith:"</s>" }
		,{separator:"---------------" }
		,{name:"Bulleted List", className: "btn-bulleted", openWith:"	<li>", closeWith:"</li>", multiline:true, openBlockWith:"<ul>\n", closeBlockWith:"\n</ul>"}
		,{name:"Numeric List", className: "btn-numeric", openWith:"	<li>", closeWith:"</li>", multiline:true, openBlockWith:"<ol>\n", closeBlockWith:"\n</ol>"}
		,{separator:"---------------" }
		,{name:"Code", className: "btn-code", openWith:"<code>", closeWith:"</code>"}
		,{name:"Link", className: "btn-link", openWith:"<a href=\"[![Link:!:http://]!]\">", closeWith:"</a>" }
		,{name:"Picture", className: "btn-picture", replaceWith:"<img src=\"[![Source:!:http://]!]\" />" }
		,{name:"Cut", className: "btn-cut", openWith:"<cut/>" }
	]}'
	,'xtype' => 'textarea'
	,'namespace' => 'tickets'
	,'area' => 'Editor'
),'',true,true);

$settings[2]= $modx->newObject('modSystemSetting');
$settings[2]->fromArray(array(
	'key' => 'tickets.editor_config.comment'
	,'value' => '{onTab: {keepDefault:false, replaceWith:"	"}
	,markupSet: [
		{name:"Bold", className: "btn-bold", key:"B", openWith:"<b>", closeWith:"</b>" }
		,{name:"Italic", className: "btn-italic", key:"I", openWith:"<i>", closeWith:"</i>"  }
		,{name:"Underline", className: "btn-underline", key:"U", openWith:"<u>", closeWith:"</u>" }
		,{name:"Stroke through", className: "btn-stroke", key:"S", openWith:"<s>", closeWith:"</s>" }
		,{separator:"---------------" }
		,{name:"Bulleted List", className: "btn-bulleted", openWith:"	<li>", closeWith:"</li>", multiline:true, openBlockWith:"<ul>\n", closeBlockWith:"\n</ul>"}
		,{name:"Numeric List", className: "btn-numeric", openWith:"	<li>", closeWith:"</li>", multiline:true, openBlockWith:"<ol>\n", closeBlockWith:"\n</ol>"}
		,{separator:"---------------" }
		,{name:"Code", className: "btn-code", openWith:"<code>", closeWith:"</code>"}
		,{name:"Link", className: "btn-link", openWith:"<a href=\"[![Link:!:http://]!]\">", closeWith:"</a>" }
		,{name:"Picture", className: "btn-picture", replaceWith:"<img src=\"[![Source:!:http://]!]\" />" }
		,{name:"Cut", className: "btn-cut", openWith:"<cut/>" }
	]}'
	,'xtype' => 'textarea'
	,'namespace' => 'tickets'
	,'area' => 'Editor'
),'',true,true);


return $settings;