<?php

function template_part($template) {
	return di('views')->locateFile($template);
}

function asset_url($file) {
	
	if (defined('ASSETS_PATH') && file_exists(ASSETS_PATH.$file)) {
		return file_url(ASSETS_PATH.$file);
	}
	
	return '/'.ltrim($file);
}
