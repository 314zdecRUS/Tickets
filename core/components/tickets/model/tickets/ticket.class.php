<?php
/**
 * The Ticket CRC for Tickets.
 *
 * @package tickets
 */

require_once MODX_CORE_PATH.'model/modx/modprocessor.class.php';
require_once MODX_CORE_PATH.'model/modx/processors/resource/create.class.php';
require_once MODX_CORE_PATH.'model/modx/processors/resource/update.class.php';

class Ticket extends modResource {
	public $showInContextMenu = false;

	function __construct(xPDO & $xpdo) {
		parent :: __construct($xpdo);
		$this->set('class_key','Ticket');
	}

	/**
	 * {@inheritDoc}
	 * @return mixed
	 */
	public static function getControllerPath(xPDO &$modx) {
		return $modx->getOption('tickets.core_path',null,$modx->getOption('core_path').'components/tickets/').'controllers/ticket/';
	}

	/**
	 * {@inheritDoc}
	 * @return mixed
	 */
	public function getContextMenuText() {
		$this->xpdo->lexicon->load('tickets:default');
		return array(
			'text_create' => $this->xpdo->lexicon('tickets'),
			'text_create_here' => $this->xpdo->lexicon('ticket_create_here'),
		);
	}

	/**
	 * {@inheritDoc}
	 * @return mixed
	 */
	public function getResourceTypeName() {
		$this->xpdo->lexicon->load('tickets:default');
		return $this->xpdo->lexicon('ticket');
	}

	/**
	 * {@inheritDoc}
	 * @return mixed
	 */
	public function get($k, $format = null, $formatTemplate= null) {
		$value = parent::get($k, $format, $formatTemplate);
		if (in_array($k, array('pagetitle','longtitle','introtext','description','content'))) {
			$value = str_replace(array('[',']','`'),array('&#91;','&#93;','&#96;'), $value);
		}
		return $value;
	}

	/**
	 * {@inheritDoc}
	 * @return mixed
	 */
	public function getContent(array $options = array()) {
		$content = parent::get('content');
		if (!in_array('Tickets', get_declared_classes())) {
			require 'tickets.class.php';
		}
		if (!isset($this->xpdo->Tickets) || !is_object($this->xpdo->Tickets) || !($this->xpdo->Tickets instanceof Tickets)) {
			$this->xpdo->Tickets = new Tickets($this->xpdo, array());
		}
		$content = $this->xpdo->Tickets->Jevix($content, 'Ticket');
		return $content;
	}
}



/**
 * Overrides the modResourceCreateProcessor to provide custom processor functionality for the Ticket type
 *
 * @package tickets
 */
class TicketCreateProcessor extends modResourceCreateProcessor {
	public $permission = 'ticket_save';
	public $languageTopics = array('access','resource','tickets:default');
	public $published = 0;

	/**
	 * {@inheritDoc}
	 * @return mixed
	 */
	public function beforeSet() {
		$this->published = $this->getProperty('published', 0);
		if (!$this->getProperty('content')) {
			$this->addFieldError('content', $this->modx->lexicon('field_required'));
		}
		if (!$this->getProperty('pagetitle')) {
			$this->addFieldError('pagetitle', $this->modx->lexicon('field_required'));
		}
		if ($this->hasErrors()) {return false;}
		$this->setProperties(array(
			'class_key' => 'Ticket'
			,'show_in_tree' => 0
			,'published' => 0
			,'hidemenu' => 1
			,'syncsite' => 0
			,'isfolder' => 1
		));
		return parent::beforeSet();
	}

	/**
	 * Make sure parent exists and user can add_children to the parent
	 * @return boolean|string
	 */
	public function checkParentPermissions() {
		$parent = null;
		$parentId = intval($this->getProperty('parent'));
		if ($parentId > 0) {
			$this->parentResource = $this->modx->getObject('TicketsSection',$parentId);
			if ($this->parentResource->get('class_key') != 'TicketsSection') {
				return $this->modx->lexicon('ticket_err_wrong_parent');
			}
			if ($this->parentResource) {
				if (!$this->parentResource->checkPolicy('section_add_children')) {
					return $this->modx->lexicon('ticket_err_wrong_parent') . $this->modx->lexicon('ticket_err_access_denied');
				}
			} else {
				return $this->modx->lexicon('resource_err_nfs', array('id' => $parentId));
			}
		}
		else {
			return $this->modx->lexicon('ticket_err_access_denied');
		}
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @return mixed
	 */
	public function afterSave() {
		$this->object->fromArray(array(
				'alias' => $this->object->id
				,'published' => $this->published
				,'isfolder' => 1
				,'publishedon' => time()
				,'publishedby' => $this->modx->user->id
				,'template' => $this->modx->getOption('tickets.default_template', null, $this->modx->getOption('default_template'), true)
		));

		$this->object->save();
		return parent::afterSave();
	}

	/**
	 * {@inheritDoc}
	 * @return mixed
	 */
	public function cleanup() {
		$results = $this->modx->cacheManager->generateContext($this->modx->context->key);
		$this->modx->context->resourceMap = $results['resourceMap'];
		$this->modx->context->aliasMap = $results['aliasMap'];
		return parent::cleanup();
	}

}



/**
 * Overrides the modResourceUpdateProcessor to provide custom processor functionality for the Ticket type
 *
 * @package tickets
 */
class TicketUpdateProcessor extends modResourceUpdateProcessor {
	public $permission = 'ticket_save';
	public $languageTopics = array('resource','tickets:default');
	public $published = 0;

	/**
	 * {@inheritDoc}
	 * @return mixed
	 */
	public function beforeSet() {
		$this->published = $this->getProperty('published', 0);
		if (!$this->getProperty('pagetitle')) {
			$this->addFieldError('pagetitle', $this->modx->lexicon('field_required'));
		}
		if (!$this->getProperty('content')) {
			$this->addFieldError('content', $this->modx->lexicon('field_required'));
		}
		if ($this->hasErrors()) {return false;}
		if ($this->object->get('createdby') != $this->modx->user->id && !$this->modx->hasPermission('edit_document')) {
			return $this->modx->lexicon('ticket_err_wrong_user');
		}
		$this->setProperties(array(
			'class_key' => 'Ticket'
			,'show_in_tree' => 0
			,'published' => 0
			,'hidemenu' => 1
			,'syncsite' => 0
			,'isfolder' => 1
		));
		return parent::beforeSet();
	}

	/**
	 * {@inheritDoc}
	 * @return mixed
	 */
	public function afterSave() {
		$this->object->fromArray(array(
			'alias' => $this->object->id
			,'published' => $this->published
			,'publishedon' => $this->published ? time() : 0
			,'publishedby' => $this->published ? $this->modx->user->id : 0
			,'isfolder' => 1
			,'editedon' => time()
			,'editedby' => $this->modx->user->id
		));
		$this->object->save();
		return parent::afterSave();
	}

	/**
	 * {@inheritDoc}
	 * @return mixed
	 */
	public function cleanup() {
		$results = $this->modx->cacheManager->generateContext($this->modx->context->key);
		$this->modx->context->resourceMap = $results['resourceMap'];
		$this->modx->context->aliasMap = $results['aliasMap'];

		$cache = $this->modx->cacheManager->getCacheProvider($this->modx->getOption('cache_resource_key', null, 'resource'));
		$key = $this->object->getCacheKey();
		$cache->delete($key, array('deleteTop' => true));
		$cache->delete($key);
		return parent::cleanup();
	}
}