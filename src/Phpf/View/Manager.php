<?php
/**
 * @package Phpf.View
 * @subpackage Manager
 */

namespace Phpf\View;

use Phpf\Util\DataContainer;
use Phpf\Util\iEventable;
use Phpf\Util\iManager;
use Phpf\Filesystem\Filesystem;

class Manager extends DataContainer implements iEventable, iManager 
{
	
	protected $filesystem;
	
	protected $events;
	
	protected $parsers = array();
	
	protected $defaults = array();
	
	protected $actions = array();
	
	protected $layout_regions = array();
	
	protected $layouts = array();
	
	/**
	 * Construct manager with Finder and Parser (optional)
	 */
	public function __construct( Filesystem &$filesystem, Parser\AbstractParser $parser = null ){
			
		$this->filesystem =& $filesystem;
		
		if ( isset($parser) ){
			$this->addParser($parser);
		}
	}
	
	/**
	 * Implement iManager
	 * Manages 'views'
	 */
	final public function manages(){
		return 'views';
	}
	
	/**
	 * Find and return a View.
	 */
	public function getView( $view, $type = 'php' ){
		
		if ( ! $parser = $this->getParser($type) ){
			throw new \RuntimeException("No parser for view type $type.");
		}
			
		$file = $this->filesystem->locate($view.'.'.$type, 'views');
		
		if ( ! $file ){
			return null;
		}
		
		return new View($file, $parser, $this, $this->getData());
	}
	
	/**
	 * Find and return a view part.
	 */
	public function getPart( $name, $type = 'php' ){
			
		$file = $this->filesystem->locate($name.'.'.$type, 'view-parts');
		
		if ( ! $file ){
			return null;
		}
		
		return new Part($file, $this->getParser($type), $this->getData());	
	}
	
	public function getLayout( $name, $class = 'Phpf\View\Layout' ){
		
		if ( ! isset($this->layout_regions[$name]) ){
			throw new \OutOfBoundsException("No defined layout with name '$name'.");
		}
		
		if ($this->layouts[$name] instanceof Layout)
			return $this->layouts[$name];
		
		$regions = $this->getLayoutRegions($name);
		
		return $this->layouts[$name] = new $class($name, $regions);
	}
	
	public function addLayout( $name, array $regions ){
		$this->layout_regions[$name] = $regions;
		return $this;
	}
	
	protected function getLayoutRegions( $name ){
		
		if ( ! isset($this->layout_regions[$name]) ){
			throw new \OutOfBoundsException("No defined layout with name '$name'.");
		}
		
		return $this->layout_regions[$name];
	}
	
	/**
	 * Add a view parser
	 */
	public function addParser( Parser\AbstractParser $parser ){
		$this->parsers[ $parser->getType() ] = $parser;
		return $this;
	}
	
	/**
	 * Get a registered parser for given type.
	 */
	public function getParser( $type ){
		return isset($this->parsers[$type]) ? $this->parsers[$type] : null;                              
	}
	
	/**
	 * Set a default value
	 */
	public function setDefault( $var, $val ){
		$this->defaults[$var] = $val;
		return $this;
	}
	
	/**
	 * Provide a default value through a closure.
	 */
	public function provideDefault( $var, \Closure $call ){
		$this->defaults[$var] = $call;
		return $this;
	}
	
	/**
	 * Get a default value.
	 */
	public function getDefault( $var ){
		
		if ( ! isset($this->defaults[$var]) ){
			return null;
		}
		
		$default = $this->defaults[$var];                              
		
		if ($default instanceof \Closure){
			$default = $default($this);
		}
		
		return $default;
	}
	
	/**
	 * Adds a callback to perform on action.
	 */
	public function on( $action, $call ){
		
		if ( isset($this->events) ){
			$this->onEvent($action, $call);
		} else {
			$this->actions[$action][] = $call;
		}
		
		return $this;
	}
	
	/**
	 * Triggers action callbacks.
	 */
	public function trigger( $action, $view = null ) {
		
		if (isset($this->events)) {
			return $this->triggerEvent($action, $view);
		} 
		
		$return = array();
		if (!empty($this->actions[$action])) {
			foreach( $this->actions[$action] as $call ) {
				$return[] = $call($this, $view, $return);
			}
		}
		
		return $return;
	}
	
	/**
	 * Sets the Events library Container as a property.
	 */
	public function setEvents( \Phpf\Event\Container &$eventContainer ){
		$this->events =& $eventContainer;
		return $this;
	}
	
	/**
	 * Whether Event library is available.
	 */
	public function eventsAvailable(){
		return isset($this->events);
	}
	
	protected function onEvent($action, $callable) {
		return $this->events->on($action, $callable);
	}
	
	protected function triggerEvent($action, View $view) {
		return $this->events->trigger($action, $view, $this);
	}
}
