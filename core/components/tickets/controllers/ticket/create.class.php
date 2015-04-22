<?php

/**
 * The create manager controller for Ticket.
 *
 * @package tickets
 */
class TicketCreateManagerController extends ResourceCreateManagerController {
	/** @var TicketsSection $resource */
	public $parent;
	/** @var Ticket $resource */
	public $resource;


	/**
	 * Returns language topics
	 * @return array
	 */
	public function getLanguageTopics() {
		return array('resource', 'tickets:default');
	}


	/**
	 * Check for any permissions or requirements to load page
	 * @return bool
	 */
	public function checkPermissions() {
		return $this->modx->hasPermission('new_document');
	}


	/**
	 * Return the default template for this resource
	 *
	 * @return int
	 */
	public function getDefaultTemplate() {
		$properties = $this->parent->getProperties();

		return $properties['template'];
	}


	/**
	 * Register custom CSS/JS for the page
	 * @return void
	 */
	public function loadCustomCssJs() {
		$properties = $this->parent->getProperties();
		$this->resourceArray = array_merge($this->resourceArray, $properties);
		$this->resourceArray['properties'] = $properties;

		/** @var Tickets $Tickets */
		$Tickets = $this->modx->getService('Tickets');
		$ticketsJsUrl = $Tickets->config['jsUrl'] . 'mgr/';
		$mgrUrl = $this->modx->getOption('manager_url', null, MODX_MANAGER_URL);

		$Tickets->loadManagerFiles($this, array(
			'config' => true,
			'utils' => true,
			//'css' => true,
			'ticket' => true,
		));
		$this->addJavascript($mgrUrl . 'assets/modext/util/datetime.js');
		$this->addJavascript($mgrUrl . 'assets/modext/widgets/element/modx.panel.tv.renders.js');
		$this->addJavascript($mgrUrl . 'assets/modext/widgets/resource/modx.grid.resource.security.local.js');
		$this->addJavascript($mgrUrl . 'assets/modext/widgets/resource/modx.panel.resource.tv.js');
		$this->addJavascript($mgrUrl . 'assets/modext/widgets/resource/modx.panel.resource.js');
		$this->addJavascript($mgrUrl . 'assets/modext/sections/resource/create.js');
		$this->addLastJavascript($ticketsJsUrl . 'ticket/create.js');

		$ready = array(
			'xtype' => 'tickets-page-ticket-create',
			'record' => $this->resourceArray,
			'publish_document' => (int)$this->canPublish,
			'canSave' => (int)$this->canSave,
			'show_tvs' => (int)!empty($this->tvCounts),
			'mode' => 'create',
		);

		$this->addHtml('
		<script type="text/javascript">
		// <![CDATA[
		MODx.config.publish_document = ' . (int)$this->canPublish . ';
		MODx.config.default_template = ' . $this->modx->getOption('tickets.default_template', null, $this->modx->getOption('default_template'), true) . ';
		MODx.onDocFormRender = "' . $this->onDocFormRender . '";
		MODx.ctx = "' . $this->ctx . '";
		Ext.onReady(function() {
			MODx.load(' . $this->modx->toJSON($ready) . ');
		});
		// ]]>
		</script>');

		$this->loadRichTextEditor();
	}

}
