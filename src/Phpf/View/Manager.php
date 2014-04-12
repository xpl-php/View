<?php
/**
 * @package Phpf.View
 * @subpackage Manager
 */

namespace Phpf\View;

use Phpf\Util\DataContainer;
use Phpf\Util\iEventable;
use Phpf\Util\iManager;
use Phpf\Filesystem\Filesystem;

class Manager extends DataContainer implements iEventable, iManager
{

	protected $filesystem;

	protected $events;

	protected $assets;

	protected $parsers = array();

	protected $defaults = array();

	protected $actions = array();

	protected $views = array();

	/**
	 * Construct manager with Finder and Parser (optional)
	 */
	public function __construct(Filesystem &$filesystem, Parser\AbstractParser $parser = null, Assets $assets = null) {

		$this->filesystem = &$filesystem;

		if (isset($parser))
			$this->addParser($parser);

		if (isset($assets))
			$this->assets = $assets;
	}

	/**
	 * Sets assets object if not set
	 */
	public function useAssets() {

		if (! isset($this->assets)) {
			$this->assets = new Assets();
		}

		return $this;
	}

	/**
	 * Returns Assets object
	 */
	public function getAssets() {
		return $this->assets;
	}
	
	/**
	 * Returns true if assets set
	 */
	public function usingAssets() {
		return isset($this->assets);
	}
	
	/**
	 * Implement iManager
	 * Manages 'views'
	 */
	final public function manages() {
		return 'views';
	}
	
	public function getCurrentView() {
		if (empty($this->views))
			return null;
		return reset($this->views);
	}

	/**
	 * Find and return a View.
	 */
	public function getView($view, $type = 'php') {
		
		$name = $view .'.'. $type;
		
		if (isset($this->views[$name])) {
			return $this->views[$name];
		}
		
		if (! $parser = $this->getParser($type)) {
			throw new \RuntimeException("No parser for view type $type.");
		}

		$file = $this->filesystem->locate($name, 'views');

		if (! $file) {
			return null;
		}
		
		return $this->views[$name] = new View($file, $parser, $this, $this->getData());
	}

	/**
	 * Find and return a view part.
	 */
	public function getPart($name, $type = 'php') {

		$file = $this->filesystem->locate($name.'.'.$type, 'view-parts');

		if (! $file) {
			return null;
		}

		return new Part($file, $this->getParser($type), $this->getData());
	}
	
	/**
	 * Add a view parser
	 */
	public function addParser(Parser\AbstractParser $parser) {
		$this->parsers[$parser->getType()] = $parser;
		return $this;
	}

	/**
	 * Get a registered parser for given type.
	 */
	public function getParser($type) {
		return isset($this->parsers[$type]) ? $this->parsers[$type] : null;
	}

	/**
	 * Set a default value
	 */
	public function setDefault($var, $val) {
		$this->defaults[$var] = $val;
		return $this;
	}

	/**
	 * Provide a default value through a closure.
	 */
	public function provideDefault($var, \Closure $call) {
		$this->defaults[$var] = $call;
		return $this;
	}

	/**
	 * Get a default value.
	 */
	public function getDefault($var) {

		if (! isset($this->defaults[$var])) {
			return null;
		}

		$default = $this->defaults[$var];

		if ($default instanceof \Closure) {
			$default = $default($this);
		}

		return $default;
	}

	/**
	 * Adds a callback to perform on action.
	 */
	public function on($action, $call) {

		if (isset($this->events)) {
			$this->events->on($action, $callable);
		} else {
			$this->actions[$action][] = $call;
		}

		return $this;
	}

	/**
	 * Triggers action callbacks.
	 */
	public function trigger($action, $view = null) {

		if (isset($this->events)) {
			return $this->events->trigger($action, $view, $this);
		}

		$return = array();
		if (! empty($this->actions[$action])) {
			foreach ( $this->actions[$action] as $call ) {
				// note passing null for compat with event objs
				$return[] = $call(null, $view, $this);
			}
		}

		return $return;
	}

	/**
	 * Sets the Events library Container as a property.
	 */
	public function setEvents(\Phpf\Event\Container &$container) {
		$this->events = &$container;
		return $this;
	}

	/**
	 * Whether Event library is available.
	 */
	public function eventsAvailable() {
		return isset($this->events);
	}

}
