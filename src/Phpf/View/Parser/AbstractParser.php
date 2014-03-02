<?php
/**
 * @package Phpf.View
 * @subpackage Parser.AbstractParser
 */

namespace Phpf\View\Parser;

abstract class AbstractParser {
	
	abstract public function getType();
	
	abstract public function parse( $file, array $data = array() );
	
}
