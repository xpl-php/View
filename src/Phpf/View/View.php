<?php

namespace Phpf\View;

use Phpf\View\Parser\AbstractParser;

class View extends Part {
	
	protected $manager;
	
	protected $attachment;
	
	protected $object_context = true;
	
	/**
	 * Set the file, parser, and any initial data.
	 */
	public function __construct( $file, AbstractParser $parser, Manager &$manager, array $data = array() ){
		parent::__construct($file, $parser, $data);
		$this->manager =& $manager;
	}
	
	/**
	 * Get/set whether object context should be used for rendering.
	 */
	public function inObjectContext( $val = null ){
		
		if ( ! isset($val) )
			return $this->object_context;
		
		$this->object_context = (bool) $val;
		
		return $this;
	}
	
	/**
	 * Attach an object to the view. Only one.
	 */
	public function attach( $object ){
		
		if ( ! is_object($object) ){
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
	 * Render the view, optionally in context of the object.
	 */
	public function render(){
		
		$this->manager->trigger('view.render', array('view' => $this));
		
		if ( $this->object_context && 'php' === $this->parser->getType() ){
			
			extract($this->data);
			
			ob_start();
			
			require $this->file;
			
			return ob_get_clean();
		
		} else {
		
			return $this->parser->parse($this->file, $this->getData());
		}
	}
	
	/**
	 * Does one of:
	 * 1. If part() is called, renders given part.
	 * 2. Calls a method on attached object.
	 * 3. Calls a closure set as a property.
	 * 4. Echoes a scalar property.
	 */
	public function __call( $func, $args ){
		
		if ( 'part' === $func ){
			$type = isset($args[1]) ? $args[1] : 'php';
			return $this->manager->getPart($args[0], $type);
		}
		
		if ( is_callable(array($this->attachment, $func)) ){
			return call_user_func_array(array($this->attachment, $func), $args);
		}
		
		if ( $this->exists($func) ){
			
			$prop = $this->get($func);
			
			// if Closure:
			if ( is_callable($prop) ){
				return call_user_func_array($prop, $args);
			}
			
			if ( is_scalar($prop) ){
				// $this->myscalar() ==> echo $this->data['myscalar']
				echo $prop;
				return;
			}
		}
		
		$triggerArgs = array('function' => $func, 'args' => $args, 'view' => $this);
		
		return $this->manager->trigger('view.call', $triggerArgs);
	}
	
}
