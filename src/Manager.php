<?php

namespace xpl\View;

class Manager
{
	
	/**
	 * Template locator.
	 * 
	 * @var \xpl\View\TemplateLocator
	 */
	protected $locator;
	
	/**
	 * View factory.
	 * 
	 * @var \xpl\View\Factory
	 */
	protected $factory;
	
	/**
	 * Views.
	 * 
	 * @var array
	 */
	protected $views;
	
	/**
	 * Constructor
	 */
	public function __construct(TemplateLocator $locator, Factory $factory = null) {
		$this->locator = $locator;
		$this->factory = $factory ?: new Factory();
		$this->views = array();
	}
	
	/**
	 * Produces a view using the template found by the given filename.
	 * 
	 * @param string $filename Filename of the template to use (relative to template path).
	 */
	public function getView($filename) {
		
		if (! isset($this->views[$filename])) {
			
			$template = $this->locator->__invoke($filename);
			
			$this->views[$filename] = $this->factory->__invoke($template);
		}
		
		return $this->views[$filename];
	}
	
	/**
	 * Sets the template locator.
	 * 
	 * @param \xpl\View\TemplateLocator $locator
	 */
	public function setTemplateLocator(TemplateLocator $locator) {
		$this->locator = $locator;
	}
	
	/**
	 * Returns the template locator.
	 * 
	 * @return \xpl\View\TemplateLocator
	 */
	public function getTemplateLocator() {
		return $this->locator;
	}
	
	/**
	 * Sets the view factory.
	 * 
	 * @param \xpl\View\Factory
	 */
	public function setFactory(Factory $factory) {
		$this->factory = $factory;
	}
	
	/**
	 * Returns the view factory.
	 * 
	 * @return \xpl\View\Factory
	 */
	public function getFactory() {
		return $this->factory;
	}
	
}
