<?php

namespace Phpf\View;

use Phpf\Util\DataContainer;
use Phpf\View\Parser\AbstractParser;

class View extends DataContainer {
	
	public $file;
		
	protected $parser;
	
	protected $attachment;
	
	protected $object_context = true;
	
	/**
	 * Set the file, parser, and any initial data.
	 */
	public function __construct( $file, AbstractParser $parser, array $data = array() ){
		
		$this->file = $file;
		$this->parser = $parser;
		$this->setData($data);
	}
	
	/**
	 * Render the view, optionally in context of the object.
	 */
	public function render(){
		
		if ( $this->object_context ){
			
			ob_start();
			
			extract($this->data);
			
			require $this->file;
			
			return ob_get_clean();
		
		} else {
		
			return $this->parser->parse($this->file, $this->getData());
		}
	}
	
	/**
	 * Get/set whether object context should be used for rendering.
	 */
	public function inObjectContext( $val = null ){
		
		if ( empty($val) )
			return $this->object_context;
		
		$this->object_context = (bool) $val;
		
		return $this;
	}
	
	/**
	 * Attach an object to the view. Only one.
	 */
	public function attach( $object ){
		
		if ( !is_object($object) ){
			throw new \InvalidArgumentException('Must pass object to attach() - ' . gettype($object) . ' given.');
		}
		
		$this->attachment = $object;
		
		return $this;
	}
	
	/**
	 * Get the attached object.
	 */
	public function getAttachment(){
		return $this->attachment;
	}
	
	/**
	 * Either:
	 * 1. Echoes a data value string.
	 * 2. Calls a method on attached object.
	 */
	public function __call( $func, $args ){
		
		if ( $this->exists($func) ){
			
			// $this->content() ==> echo $this->data['content']
			if ( is_string($this->get($func)) && !is_callable($this->get($func)) ){
				echo $this->get($func);
				return;
			}
			
			return call_user_func_array($this->get($func), $args);
		}
		
		if ( is_callable(array($this->attachment, $func)) ){
				
			return call_user_func_array(array($this->attachment, $func), $args);
		}
	}
	
	/**
	 * Returns rendered view (string).
	 */
	public function __toString(){
		return $this->render();
	}
	
}
