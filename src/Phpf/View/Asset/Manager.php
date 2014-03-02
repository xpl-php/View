<?php

namespace Phpf\View\Asset;

use Phpf\Util\Filesystem\Finder;
use Phpf\Util\Path;
use Closure;

class Manager {
	
	protected $finder;
	
	protected $assets = array();
	
	protected $actions = array();
	
	protected $working_loc;
	
	const LOCATION_HEAD = 'head';
	
	const LOCATION_BODY = 'body';
	
	public function __construct( Finder $finder ){
		$this->finder = $finder;
	}
	
	public function addDirectory( $asset_type, $path ){
		$this->finder->registerDirectory($path, $asset_type);
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
	
	public function head(){
		return $this->assets(self::LOCATION_HEAD);
	}
	
	public function body(){
		return $this->assets(self::LOCATION_BODY);
	}
	
	public function in( $location, Closure $func ){
		$this->actions[$location][] = $func;
		return $this;
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
				if ( $tag = $this->getTag('css', $file) ){
					$tags[] = $tag;
				}
			}
		}
		
		if ( $doJs && !empty($this->assets['js'][$location]) ){
			foreach($this->assets['js'][$location] as $file){
				if ( $tag = $this->getTag('js', $file) ){
					$tags[] = $tag;
				}
			}
		}
		
		return empty($tags) ? '' : implode("\n", $tags);
	}
	
	public function getTag( $type, $name ){
		
		$output = '';
		
		// Already a URL starting with "http" or "//" (like Google)
		if ( 0 === strpos($name, 'http') || 0 === strpos($name, '//') ){
			return $this->generateTag($type, $name);
		}
		
		// extract file extension from $name
		if ( false !== $pos = strrpos($name, '.') ){
			$ext = substr($name, $pos);
			$name = substr($name, 0, $pos);
		} else {
			$ext = $type;
		}
		
		if ( $file = $this->finder->locateFile($name, $type, $ext) ){
			$url = \Phpf\Util\Path::url($file);
			return $this->generateTag($type, $url);
		}
		
		return null;
	}
	
	protected function generateTag($type, $value){
		
		if ( 'css' === $type ){
			return \Phpf\Util\Html::link($value);
		} 
		
		if ( 'js' === $type ){
			return \Phpf\Util\Html::script($value);
		}
	}
	
}