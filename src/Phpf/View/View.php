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
	 * Render the view, optionally in context of the object.
	 * @return string Rendered view
	 */
	public function render(){
		
		$this->manager->trigger('view.render', $this);
		
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
	 * Get/set whether object context should be used for rendering.
	 * 
	 * @param null|bool $val Pass boolean to set value.
	 * @return bool|$this If no argument given, returns value, otherwise $this.
	 */
	public function inObjectContext( $val = null ){
		
		if ( ! isset($val) )
			return $this->object_context;
		
		$this->object_context = (bool) $val;
		
		return $this;
	}
	
	/**
	 * Attach an object to the view (only one).
	 * 
	 * Attached object methods will be available through View.
	 * 
	 * @param object $object The object to attach to view
	 * @return $this
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
	 * 
	 * @return object Attached object
	 */
	public function getAttachment(){
		return $this->attachment;
	}
	
	/**
	 * Gets a Part from view manager.
	 * 
	 * @param string $part Name of part
	 * @param string $type Type of file
	 */
	public function part( $part, $type = 'php' ){
		return $this->manager->getPart($part, $type);
	}
	
	/**
	 * Either:
	 *	- calls a method of attached object
	 * or 
	 * 	- calls a callable property, e.g. closure
	 */
	public function __call( $func, $args ){
		
		if ( isset($this->attachment) && is_callable(array($this->attachment, $func)) ){
			return call_user_func_array(array($this->attachment, $func), $args);
		}
		
		if ( !empty($this[$func]) && is_callable($this[$func]) ){
			return call_user_func_array($this[$func], $args);
		}
	}
	
}
