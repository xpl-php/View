<?php
/**
 * @package Phpf\View
 */

namespace Phpf\View\Parser;

interface ViewParserInterface {
	
	public function getType();
	
	public function parse($file, array $data = array());
	
}
