<?php

namespace Phpf\View;

class View extends Part
{

	protected $attachment;

	protected $object_context = true;

	/**
	 * Render the view, optionally in an object context.
	 * 
	 * Rendering in an object context allows for the use of "$this" in view files.
	 * 
	 * @return string Rendered view
	 */
	public function render() {
		
		// tell the manager we're rendering
		$this->notify(); 
		
		if ($this->object_context && 'php' === $this->parser->getType()) {
			extract($this->getAllData(), EXTR_REFS);
			ob_start();
			require $this->file;
			return ob_get_clean();
		} else {
			return $this->parser->parse($this->file, $this->getAllData());
		}
	}
	
	/**
	 * Gets a Part from view manager.
	 *
	 * @param string $part Name of part
	 * @param string $type Type of file
	 */
	public function part($part, $type = 'php') {
		return $this->manager->getPart($part, $type);
	}

	/**
	 * Get/set whether object context should be used for rendering.
	 *
	 * @param null|bool $val Pass boolean to set value.
	 * @return bool|$this If no argument given, returns value, otherwise $this.
	 */
	public function inObjectContext($val = null) {
		if (! isset($val)) {
			return $this->object_context;
		}
		$this->object_context = (bool)$val;
		return $this;
	}

	/**
	 * Attach an object to the view (only one).
	 *
	 * Attached object methods will be available through View.
	 *
	 * @param object $object The object to attach to view.
	 * @return $this
	 * @throws InvalidArgumentException if not passed an object.
	 */
	public function setAttachment($object) {
		if (! is_object($object)) {
			throw new \InvalidArgumentException('Must pass object to attach() - '.gettype($object).' given.');
		}
		$this->attachment = $object;
		return $this;
	}

	/**
	 * Get the attached object.
	 *
	 * @return object Attached object
	 */
	public function getAttachment() {
		return $this->attachment;
	}

	/**
	 * Either:
	 *	- calls a method of attached object
	 * or
	 * 	- calls a callable property, e.g. closure
	 * 
	 * @param string $func Class method called.
	 * @param array $args Array of arguments passed to method.
	 * @return mixed Results of callback, if any.
	 */
	public function __call($func, $args) {

		if (isset($this[$func]) && is_callable($this[$func])) {
			return call_user_func_array($this[$func], $args);
		}

		if (isset($this->attachment) && is_callable(array($this->attachment, $func))) {
			return call_user_func_array(array($this->attachment, $func), $args);
		}
	}
	
}
