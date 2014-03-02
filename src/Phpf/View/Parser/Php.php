<?php

namespace Phpf\View\Parser;

class Php extends AbstractParser {
	
	public function getType(){
		return 'php';
	}
	
	public function parse( $file, array $data = array() ){
	
		$obClean = function ($__FILE__, $__DATA__){
			
			extract($__DATA__, EXTR_REFS);
			ob_start();
			
			try {
				// Load the view within the current scope
				include $__FILE__;
			} catch (\Exception $exception) {
				
				// Delete the output buffer
				ob_end_clean();
				
				// Re-throw the exception
				throw $exception;
			}
			
			// Get the captured output and close the buffer
			return ob_get_clean();
		};
		
		return $obClean($file, $data);
	}
	
}
