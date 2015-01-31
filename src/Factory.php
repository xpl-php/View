<?php

namespace xpl\View;

class Factory 
{
	
	/**
	 * View class.
	 * 
	 * @var string
	 */
	protected $class = 'xpl\View\View';
	
	/**
	 * Sets the class to use for views.
	 * 
	 * @param string $class View classname.
	 */
	public function setClass($class) {
		
		if (empty($class) || ! is_string($class)) {
			throw new \InvalidArgumentException("Expecting non-empty string, given: ".gettype($class));
		}
		
		$this->class = $class;
	}
	
	/**
	 * Returns the class to use for views.
	 * 
	 * @return string View classname.
	 */
	public function getClass() {
		return $this->class;
	}
	
	/**
	 * Creates a view using the given template file.
	 * 
	 * @param string $template Template file path.
	 * @return \xpl\View\View
	 */
	public function __invoke($template) {
		
		$class = $this->getClass();
		
		return new $class($template);
	}
	
}
