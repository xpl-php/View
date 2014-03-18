<?php

namespace Phpf\View;

class Assets {
	
	protected $assets = array();
	
	protected $enqueued = array();
	
	protected $rendered = array();
	
	public function add( $handle, $uri, $deps = array() ){
		$this->assets[$handle] = array($uri, $deps);
		return $this;
	}
	
	public function get($handle) {
		return isset($this->assets[$handle]) ? $this->assets[$handle] : null;
	}
	
	public function exists($handle) {
		return isset($this->assets[$handle]);
	}
	
	public function enqueued($handle) {
		return isset($this->enqueued[$handle]);
	}
	
	public function enqueue($handle) {
		
		if (! $this->exists($handle)) {
			trigger_error("Cannot enqueue unknown asset '$handle'.");
			return null;
		}
		
		list($uri, $deps) = $this->get($handle);
		
		if (!empty($deps)) {
			try {
				$this->enqueueDependencies($deps);
			} catch (UnsatisfiedDependencyException $e) {
				trigger_error("Unsatisfied dependency '$e->getMessage()' for asset '$handle'.");
				return null;
			}
		}
		
		if (false === strpos($uri, '://') && 0 !== strpos($uri, '//')) {
			$uri = \Phpf\Util\Path::url($uri);
		}
		
		$this->enqueued[$handle] = $uri;
		
		return true;
	}
	
	public function render($handle){
		
		if (! $this->enqueued($handle)) {
				
			$success = $this->enqueue($handle);
			
			if (true !== $success) {
				trigger_error("Cannot render asset '$handle' - cannot enqueue.");
				return '';
			}
		}
		
		$uri = $this->enqueued[$handle];
		
		if (\Phpf\Util\Str::endsWith($uri, '.css')) {
			return \Phpf\Util\Html::link($uri);
		} elseif (\Phpf\Util\Str::endsWith($uri, '.js')) {
			return \Phpf\Util\Html::script($uri);
		}
		
		trigger_error("Cannot render asset '$handle' - unknown asset type.");
		
		return '';
	}
	
	protected function enqueueDependencies(array $deps) {
	
		foreach($deps as $dep) {
			
			if (! $this->enqueued($dep)) {
				
				if (! $this->exists($dep)) {
					throw new UnsatisfiedDependencyException($dep);
				}
				
				$this->enqueue($dep);
			}
		}
	}
	
}

class UnsatisfiedDependencyException extends \RuntimeException 
{
}
