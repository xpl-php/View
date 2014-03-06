<?php
/**
 * @package Phpf.View
 * @subpackage Manager
 */

namespace Phpf\View;

use Phpf\Util\DataContainer;
use Phpf\Filesystem\Filesystem;

class Manager extends DataContainer {
	
	protected $filesystem;
	
	protected $parsers = array();
	
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
	 * Find and return a View.
	 */
	public function getView( $view, $type = 'php' ){
		
		if ( ! $parser = $this->getParser($type) ){
			throw new \RuntimeException("No parser for view type $type.");
		}
			
		$file = $this->filesystem->locate($view.'.'.$type, 'views');
		
		if ( ! $file )
			return null;
		
		return new View($file, $parser, $this, $this->getData());
	}
	
	/**
	 * Find and return a view part.
	 */
	public function getPart( $name, $type = 'php' ){
			
		$file = $this->filesystem->locate($name.'.'.$type, 'view-parts');
		
		if ( ! $file )
			return null;
		
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
	
}
