<?php

namespace Phpf\View;

use ArrayAccess;
use Countable;

class Layout implements ArrayAccess, Countable {
	
	public $name;
	
	protected $regions = array();
	
	protected $regional_classes = array();
	
	protected $defaults = array();
	
	public function __construct( $name, array $regions ){
		$this->name = $name;
		$this->regions = array_fill_keys($regions, '');
	}
	
	public function get( $var ){
		isset($this->regions[$var]) ? $this->regions[$var] : $this->getDefault($var);
	}
	
	public function set( $var, $val ){
			
		if ( ! array_key_exists($var, $this->regions) ){
			throw new \OutOfBoundsException("Region $var is not defined.");
		}
		
		if ( !empty($this->regional_classes[$var]) ){
			if ( !in_array(get_class($val), $this->regional_classes[$var]) ){
				$message = 'Invalid region class for layout - allowed classes are '.implode(', ', $this->regional_classes[$var]);
				throw new \OutOfBoundsException($message);
			}
		}
		
		$this->regions[$var] = $val;
		
		return $this;
	}
	
	public function exists( $var ){
		return array_key_exists($var, $this->regions);
	}
	
	public function remove( $var ){
		unset($this->regions[$var]);
	}
	
	public function defineRegion( $name, $allowed_classes = array() ){
		$this->regions[$name] = true;
		$this->regional_classes[$name] = $allowed_classes;
		return $this;
	}
	
	public function setDefault( $region, $value ){
		$this->defaults[$region] = $value;
		return $this;
	}
	
	public function getDefault( $region ){
		return isset($this->defaults[$region]) ? $this->defaults[$region] : null;
	}
	
	public function offsetGet( $index ){
		return $this->get($index);
	}
	
	public function offsetSet( $index, $newval ){
		$this->set($index, $newval);
	}
	
	public function offsetExists( $index ){
		return $this->exists($index);
	}
	
	public function offsetUnset( $index ){
		$this->remove($index);
	}
	
	public function count(){
		return count($this->regions);
	}
	
}
