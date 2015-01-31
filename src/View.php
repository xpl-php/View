<?php

namespace xpl\View;

class View extends \xpl\Common\Structure\BaseMap
{

	/**
	 * @var string
	 */
	protected $template;
	
	/**
	 * @var string
	 */
	protected $content;
	
	/**
	 * Constructor.
	 * 
	 * @param string $template [Optional] Template file path.
	 */
	public function __construct($template = null) {
		if (isset($template)) {
			$this->setTemplate($template);
		}
	}
	
	/**
	 * Sets the template file.
	 * 
	 * @param string $template Template file path.
	 */
	public function setTemplate($template) {
		$this->template = $template;
	}
	
	/**
	 * Returns the template file path.
	 * 
	 * @return string The template file.
	 */
	public function getTemplate() {
		return $this->template;
	}
	
	/**
	 * Sets the view content.
	 * 
	 * @param string|callable|object $content Content string, callable, or an object with '__toString' method.
	 * @return $this
	 */
	public function setContent($content) {
		
		$filtered = $this->filterContent($content);
		
		if (false === $filtered) {
			throw new \InvalidArgumentException("Non-scalar content.");
		}
		
		$this->content = $filtered;
		
		return $this;
	}
	
	/**
	 * Returns the content.
	 * 
	 * @param boolean $force_str Whether to force the content to a string. Default true.
	 * @return string|mixed Content string (default), or possibly another type if $force_str is false.
	 */
	public function getContent($force_str = true) {
		
		if (empty($this->content)) {
			return null;
		}
		
		if (is_callable($this->content)) {
			return $this->invoke($this->content);
		}
		
		return $force_str ? (string)$this->content : $this->content;
	}
	
	/**
	 * Alias of getContent()
	 */
	public function content($force_str = true) {
		return $this->getContent($force_str);
	}
	
	/**
	 * Returns the view as a string using its current template.
	 * 
	 * @return string
	 */
	public function __toString() {
		return empty($this->template) ? '' : $this->includeFile($this->template);
	}
	
	/**
	 * Includes a file within the object's scope.
	 * 
	 * View data is extracted so as to be accessible as variables within the file.
	 * 
	 * @param string $__file File path.
	 * @return string Captured content of file.
	 */
	public function includeFile($__file) {
		
		ob_start();
		
		extract($this->_data, EXTR_SKIP);
		
		include $__file;
		
		return ob_get_clean();
	}
	
	/**
	 * Attempts to forward a function call to a data item.
	 * 
	 * @param callable $func
	 * @param array $args
	 * @return mixed
	 */
	public function __call($func, array $args) {
		
		if (isset($this->_data[$func])) {
				
			if (is_callable($this->_data[$func])) {
				return $this->invoke($this->_data[$func], $args);
			}
			
			return $this->_data[$func];
		}
	}
	
	/**
	 * Alias of 'import'
	 */
	public function addData($data) {
		$this->import($data);
	}
	
	/**
	 * Invokes a callable and returns the result. 
	 * 
	 * If given a closure, it is bound to the object ($this is available).
	 * 
	 * @param callable $callable
	 * @param array $args
	 */
	protected function invoke($callable, array $args = array()) {
		
		if ($callable instanceof \Closure) {
			$callable = $callable->bindTo($this, get_class($this));
		}
		
		return empty($args) ? $callable() : call_user_func_array($callable, $args);
	}
	
	/**
	 * Filters potential content and returns false if invalid.
	 * 
	 * @param mixed $content
	 * @return mixed
	 */
	protected function filterContent($content) {
			
		if (is_scalar($content) || is_callable($content)) {
			return $content;
		}
		
		if (method_exists($content, '__toString')) {
			return (string)$content;
		}
		
		return false;
	}
	
}
