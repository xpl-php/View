<?php

namespace Phpf\View;

use Phpf\Util\Html;

class Asset {
	
	protected $handle;
	protected $uri;
	protected $attributes;
	protected $enqueued = false;
	protected $rendered = false;
	protected $location;
	
	public function __construct($handle, $uri, array $attributes = array()) {
		$this->handle = $handle;
		$this->uri = $uri;
		$this->attributes = $attributes;
	}
	
	public function setAttributes(array $attributes) {
		$this->attributes = $attributes;
		return $this;
	}
	
	public function addAttribute($name, $value) {
		$this->attributes[$name] = $value;
		return $this;
	}
	
	public function hasAttribute($name) {
		return isset($this->attributes[$name]);
	}
	
	public function getAttribute($name) {
		return $this->attributes[$name];
	}
	
	public function isEnqueued() {
		return $this->enqueued;
	}
	
	public function isRendered() {
		return $this->rendered;
	}
	
	public function enqueue($location = null) {
		
		$this->enqueued = true;
		
		if (isset($location)) {
			$this->location = $location;
		}
		
		return $this;
	}
	
	public function isLocation($location = null) {
			
		if (isset($this->location)) {
			return $location === $this->location;
		}
		
		return false;
	}
	
	public function render() {
		
		if (! isset($this->attributes['id'])) {
			$this->attributes['id'] = $this->handle;
		}
		
		if (0 !== strpos($this->uri, '/') && false === strpos($this->uri, '://')) {
			$this->uri = get_url($this->uri);
		}
		
		$html = '';
		
		if ('.css' === substr($this->uri, -4)) {
			$html = Html::link($this->uri, $this->attributes);
		} else if ('.js' === substr($this->uri, -3)) {
			$html = Html::script($this->uri, $this->attributes);
		}
		
		$this->rendered = true;
		
		return $html;
	}
	
}
