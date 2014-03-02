<?php
/**
 * @package library.view
 * @subpackage functions
 */

/**
 * Registers a template directory with the Template\Locator
 */
function register_template_dir( $dirpath, $priority = 10, $position = 'top' ){
	View\Finder::i()->register_template_dir( $dirpath, $priority, $position );
}

/**
 * Registers a block directory with the Template\Locator
 */
function register_block_dir( $dirpath, $priority = 10, $position = 'top' ){
	View\Finder::i()->register_block_dir( $dirpath, $priority, $position );
}

/**
* Returns instance of the current theme object.
* We refer to the class alias to avoid having to map it.
*/
function current_theme(){
	return Current_Theme::i();
}

/**
 * Returns current theme's template directory.
 */
function get_theme_template_dir(){
	return current_theme()->get_template_dir();
}

/**
 * Returns current theme's block directory.
 */
function get_theme_block_dir(){
	return current_theme()->get_block_dir();
}

/**
 * Returns a Template\Block object.
 * 
 * @param string $name The Block name (i.e. its file name)
 * @param array|null $localize Variables to localize in the block.
 * @return Template\Block The block.
 */
function get_content_block( $name, array $localize = array() ){
	return View\Manager::i()->create_block( $name, $localize );
}

/**
 * Gets and prints a Template\Block.
 */
function content_block( $name, array $vars = array() ){
	echo get_content_block( $name, $vars );
}

/**
 * Returns a Template\File object.
 * 
 * @param string $name The template name.
 * @param array|null $data Data to import into the template.
 * @return Template\File The template object.
 */
function get_template( $name, array $data = null ){
	return View\Manager::i()->create_template( $name, $data );
}

/**
 * Gets a prints a Template\File.
 */
function template( $name, array $data = null ){
	echo View\Manager::i()->create_template( $name, $data );
}

/**
 * Sets the current template.
 */
function set_current_template( $file, array $data = null ){
	View\Manager::i()->set_current_template( $file, $data );
}

/**
 * Returns true if the current template is set.
 */	
function current_template_exists(){
	return View\Manager::i()->current_template_exists();
}

/**
 * Returns the current template object.
 */
function get_current_template(){
	return View\Manager::i()->get_current_template();	
}

/**
 * Returns current template object, optionally creating if not set
 * and a name/filepath is passed.
 */
function current_template( $file = null, array $data = null ){
	if ( ! current_template_exists() ){
		if ( empty( $file ) ){
			trigger_error( 'No current template to get.' );
			return null;
		}
		set_current_template( $file, $data );
	}
	return get_current_template();
}

/**
 * Returns array of the current template's variables.
 * Used for localizing variables in views (blocks and templates).
 */
function get_template_vars(){
	return get_current_template()->get_vars();
}

/**
 * Flushes cache group for template and block paths.
 */
function flush_view_cache(){
	View\Finder::i()->flush_cache();
	return;
}

/**
 * Returns buffered contents of file with localized variables (default by reference).
 * 
 * @param string $file Path to file.
 * @param array|null $vars Variables to localize in file.
 * @param int $extract_flags Flag constants for extract(). Default is EXTR_REFS
 * @return string Buffered file contents.
 */
function ob_file_contents( $__FILE__, array $vars = null, $extract_flags = EXTR_SKIP ){
		
	static $__URL__;
	
	if ( ! is_readable($__FILE__) ) {
		trigger_error("Unreadable file passed to ob_file_contents() - $__FILE__", E_USER_NOTICE);
		return '';
	}
	
	if ( ! isset($__URL__) )
		$__URL__ = trim(current_url(), '/');
	
	ob_start();
	
	if ( ! empty($vars) ){
		extract($vars, $extract_flags);
	}
	
	unset($vars, $extract_flags);
	
	include $__FILE__;
	
	return ob_get_clean();
}
