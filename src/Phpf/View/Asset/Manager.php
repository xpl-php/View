<?php

namespace Phpf\View\Asset;

use Phpf\Filesystem\Filesystem;
use Phpf\Util\Path;
use Phpf\Util\iManager;

class Manager implements iManager {
	
	protected $finder;
	
	protected $assets = array();
	
	protected $actions = array();
	
	protected $working_loc;
	
	const LOCATION_HEAD = 'head';
	
	const LOCATION_BODY = 'body';
	
	public function __construct( Filesystem &$finder ){
		$this->finder =& $finder;
	}
	
	final public function manages(){
		return 'assets';
	}
	
	public function addDirectory( $asset_type, $path ){
		$this->finder->add($path, 'assets');
		return $this;
	}
	
	public function addCssDirectory( $path ){
		return $this->addDirectory('css', $path);
	}
	
	public function addJsDirectory( $path ){
		return $this->addDirectory('js', $path);
	}
	
	public function add( $type, $name, $location = self::LOCATION_HEAD ){
		
		// override location when adding via in()
		if ( isset($this->working_loc) ){
			$location = $this->working_loc;
		}
		
		$this->assets[$type][ $location ][ $name ] = $name;
		
		return $this;
	}
	
	public function css( $name, $location = self::LOCATION_HEAD ){
		return $this->add('css', $name, $location);
	}
	
	public function js( $name, $location = self::LOCATION_HEAD ){
		return $this->add('js', $name, $location);
	}
	
	public function in( $location, \Closure $func ){
		$this->actions[$location][] = $func;
		return $this;
	}
	
	public function head(){
		return $this->assets(self::LOCATION_HEAD);
	}
	
	public function body(){
		return $this->assets(self::LOCATION_BODY);
	}
	
	public function assets( $location, $type = null ){
		
		$doCss = empty($type) || 'css' === $type;
		$doJs = empty($type) || 'js' === $type;
		
		$tags = array();
		
		// do actions, which add assets
		if ( !empty($this->actions[$location]) ){
			$this->working_loc = $location;
			foreach($this->actions[$location] as $exec){
				$tags[] = $exec($this);	
			}
			unset($this->working_loc);
		}
		
		if ( $doCss && !empty($this->assets['css'][$location]) ){
			foreach($this->assets['css'][$location] as $file){
				if ( $tag = $this->getTag($file) ){
					$tags[] = $tag;
				}
			}
		}
		
		if ( $doJs && !empty($this->assets['js'][$location]) ){
			foreach($this->assets['js'][$location] as $file){
				if ( $tag = $this->getTag($file) ){
					$tags[] = $tag;
				}
			}
		}
		
		return empty($tags) ? '' : implode("\n", $tags);
	}
	
	public function getTag( $name, $type = null ){
		
		$output = '';
		
		// Already a URL starting with "http" or "//" (like Google)
		if ( 0 === strpos($name, 'http') || 0 === strpos($name, '//') ){
			return $this->generateTag($name);
		}
		
		if ( isset($type) ) {
			$ext = '.'.$type;
		} elseif ( false !== $pos = strrpos($name, '.') ){
			// extract file extension from $name
			$ext = substr($name, $pos); // .ext
			$name = substr($name, 0, $pos);
		} 
		
		if ( $file = $this->finder->locate($name.$ext, 'assets') ){
			$url = \Phpf\Util\Path::url($file);
			return $this->generateTag($url);
		}
		
		return null;
	}
	
	protected function generateTag($value){
		
		if ( \Phpf\Util\Str::endsWith($value, '.css') ){
			return \Phpf\Util\Html::link($value);
		} 
		
		if ( \Phpf\Util\Str::endsWith($value, '.js') ){
			return \Phpf\Util\Html::script($value);
		}
		
		throw new \RuntimeException("No tag generator for '$value'");
	}
	
}
