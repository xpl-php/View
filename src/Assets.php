<?php

namespace Phpf\View;

class Assets {
	
	protected $events;
	protected $assets = array();
	
	public function __construct($events = null) {
		if (isset($events)) {
			$this->events = $events;
		}
	}
	
	public function isRegistered($handle) {
		return isset($this->assets[$handle]);
	}
	
	public function isEnqueued($handle) {
		return isset($this->assets[$handle]) && $this->assets[$handle]->isEnqueued();
	}
	
	public function isRendered($handle) {
		return isset($this->assets[$handle]) && $this->assets[$handle]->isRendered();
	}
	
	public function register($handle, $uri, $attrs = array()){
		return $this->assets[$handle] = new Asset($handle, $uri, $attrs);
	}
	
	public function get($handle) {
		return isset($this->assets[$handle]) ? $this->assets[$handle] : null;
	}
	
	public function enqueue($handle, $location = null) {
			
		if (! isset($this->assets[$handle])) {
			throw new InvalidArgumentException("Cannot enqueue unknown asset '$handle'.");
		}
		
		$this->assets[$handle]->enqueue($location);
		
		return $this;
	}
	
	public function dequeue($handle) {
			
		if ($this->isEnqueued($handle)) {
			unset($this->assets[$handle]);
		}
		
		return $this;
	}
	
	public function render($handle){
		
		if (! isset($this->assets[$handle])) {
			throw new InvalidArgumentException("Cannot render unknown asset '$handle'.");
		}
		
		if ($this->assets[$handle]->isRendered()) {
			return '';
		}
		
		return $this->assets[$handle]->render();
	}
	
	public function renderLocation($location) {
		$s = '';
		
		if (isset($this->events)) {
			$this->events->trigger('assets.render.'.$location);
		}
		
		foreach($this->assets as $handle => &$object) {
			if ($object->isEnqueued() && ! $object->isRendered() && $object->isLocation($location)) {
				$s .= $object->render();
			}
		}
		
		return $s;
	}
	
	public function renderEnqueued() {
		$s = '';
		
		if (isset($this->events)) {
			$this->events->trigger('assets.render.enqueued');
		}
		
		foreach($this->assets as $handle => &$object) {
			if ($object->isEnqueued() && ! $object->isRendered()) {
				$s .= $object->render();
			}
		}
		
		return $s;
	}
	
}
