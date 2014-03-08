<?php
/**
 * @package Phpf.View
 * @subpackage Manager
 */

namespace Phpf\View;

use Phpf\Util\DataContainer;
use Phpf\Filesystem\Filesystem;
use Phpf\Util\iEventable;
use Phpf\Util\iManager;

class Manager extends DataContainer implements iEventable, iManager {
	
	protected $filesystem;
	
	protected $parsers = array();
	
	protected $defaults = array();
	
	protected $actions = array();
	
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
			return $this->trigger('getView', array('name' => $view, 'type' => $type));
		}
		
		return new View($file, $parser, $this, $this->getData());
	}
	
	/**
	 * Find and return a view part.
	 */
	public function getPart( $name, $type = 'php' ){
			
		$file = $this->filesystem->locate($name.'.'.$type, 'view-parts');
		
		if ( ! $file ){
			return $this->trigger('getPart', array('name' => $name, 'type' => $type));
		}
		
		return new Part($file, $this->getParser($type), $this->getData());	
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
		$this->defaults[ $var ] = $val;
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
		
		$default = isset($this->defaults[$var]) ? $this->defaults[$var] : null;                              
		
		if ( $default instanceof \Closure){
			$default = $default($this);
		}
		
		return $default;
	}
	
	/**
	 * Adds a callback to perform on action.
	 */
	public function on( $action, \Closure $call ){
		
		$this->actions[$action][] = $call;
		
		return $this;
	}
	
	/**
	 * Triggers action callbacks.
	 */
	public function trigger( $action, array $args = array() ){
			
		$r = null;
		
		if ( ! empty($this->actions[$action]) ){
				
			foreach($this->actions[$action] as $closure){
					
				$r = $closure($this, $args, $r);
			}
		}
		
		return $r;
	}
	
}
