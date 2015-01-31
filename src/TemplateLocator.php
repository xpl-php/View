<?php

namespace xpl\View;

class TemplateLocator 
{
	
	protected $path;
	protected $dirs = array();
	protected $extension = 'php';
	
	public function __construct($template_path, $file_extension = 'php') {
		
		if (! is_dir($template_path)) {
			throw new \InvalidArgumentException("Invalid template directory: '$template_path'.");
		}
		
		$this->path = rtrim($template_path, '/\\').DIRECTORY_SEPARATOR;
		$this->setFileExtension($file_extension);
	}
	
	public function setFileExtension($ext) {
		$this->extension = ltrim($ext, '.');
	}
	
	public function getFileExtension() {
		return $this->extension;
	}
	
	public function getPath() {
		return $this->path;
	}
	
	public function addDir($dir) {
		$this->dirs[] = trim($dir, '/\\').DIRECTORY_SEPARATOR;
	}
	
	public function removeDir($dir) {
		if ($key = array_search(trim($dir, '/\\').DIRECTORY_SEPARATOR, $this->dirs, true)) {
			unset($this->dirs[$key]);
		}
	}
	
	public function hasDir($dir) {
		return in_array(trim($dir, '/\\').DIRECTORY_SEPARATOR, $this->dirs, true);
	}
	
	public function getDirs() {
		return $this->dirs;
	}
	
	public function __invoke($filename) {
		
		$filename = ltrim($filename, '/\\');
		
		if (! pathinfo($filename, PATHINFO_EXTENSION)) {
			$filename .= '.'.$this->extension;
		}
		
		if (file_exists($this->path.$filename)) {
			return $this->path.$filename;
		}
		
		if (! empty($this->dirs)) {
				
			foreach($this->dirs as $dir) {
				
				if (is_readable($this->path.$dir.$filename)) {
				
					return $this->path.$dir.$filename;
				}
			}
		}
		
		throw new \RuntimeException("Could not locate template: '$filename'.");
	}
	
}
