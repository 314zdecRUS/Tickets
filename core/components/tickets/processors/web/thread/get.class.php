<?php
class GetThreadProcessor extends modProcessor {
	public $classKey = 'TicketThread';
	public $languageTopics = array('tickets:default');
	private $object;
	private $comments;
	private $total = 0;


	/**
	 * {@inheritDoc}
	 * @return boolean
	 */
	public function initialize() {
		$thread = $this->getProperty('thread');
		if (!$this->object = $this->modx->getObject($this->classKey, array('name' => $thread))) {
			$this->object = $this->modx->newObject($this->classKey);
			$this->object->fromArray(array(
				'name' => $thread
				,'createdby' => $this->modx->user->id
				,'createdon' => date('Y-m-d H:i:s')
				,'resource' => $this->modx->resource->id
			));
			$this->object->save();
		}
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @return boolean
	 */
	public function process() {
		$this->getComments();
		$this->buildTree();
		return $this->cleanup();
	}

	/**
	 * {@inheritDoc}
	 * @return array
	 */
	public function getLanguageTopics() {
		return $this->languageTopics;
	}

	/*
	 * Returns all comments of the resource with given id
	 * */
	public function getComments() {
		$res = array();
		$result = null;
		$q = $this->modx->newQuery('TicketComment');
		$q->select($this->modx->getSelectColumns('TicketComment','TicketComment'));
		$q->select('`TicketThread`.`resource`');
		$q->leftJoin('TicketThread','TicketThread','`TicketThread`.`id` = `TicketComment`.`thread`');
		$q->where(array('thread' => $this->object->id));
		$q->sortby('id','ASC');
		$q->prepare();
		if ($q->prepare() && $q->stmt->execute()) {
			while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
				$res[$row['id']] = $row;
			}
			$this->total = count($res);
			$this->comments = $res;
		}
	}


	public function buildTree() {
		$data = $this->comments;
		$this->comments = array();
		foreach($data as $id => &$row){
			if(empty($row['parent'])){
				$this->comments[$id] = &$row;
			}
			else{
				$data[$row['parent']]['children'][$id] = &$row;
			}
		}
	}


	public function cleanup() {
		return $this->outputArray($this->comments, $this->total);
	}

}
return 'GetThreadProcessor';