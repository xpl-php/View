<?php
/**
 * @package Phpf.View
 * @subpackage Manager
 */

namespace Phpf\View;

use Phpf\Util\DataContainer;
use Phpf\Util\Filesystem\Finder;

class Manager extends DataContainer {
	
	protected $finder;
	
	protected $parsers = array();
	
	/**
	 * Construct manager with Finder and Parser (optional)
	 */
	public function __construct( Finder $finder, Parser\AbstractParser $parser = null ){
			
		$this->finder = $finder;
		
		if ( isset($parser) ){
			$this->addParser($parser);
		}
	}
	
	/**
	 * Add a view parser
	 */
	public function addParser( Parser\AbstractParser $parser ){
		$this->parsers[ $parser->getType() ] = $parser;
	}
	
	/**
	 * Get a registered parser for given type.
	 */
	public function getParser( $type ){
		return isset($this->parsers[$type]) ? $this->parsers[$type] : null;                              
	}
	
	/**
	 * Find and return a View.
	 */
	public function getView( $view, $type = 'php' ){
			
		$file = $this->finder->locateFile($view, 'views', $type);
		
		if ( !$file )
			return null;
		
		if ( ! $parser = $this->getParser($type) ){
			throw new \Exception("No parser for view type $type.");
		}
		
		return new View($file, $parser, $this->getData());
	}

}
