<?php

namespace Phpf\View;

use Phpf\Common\DataContainer;
use Phpf\App\ManagerInterface;
use Phpf\View\Parser\ViewParserInterface;
use Phpf\Filesystem\Filesystem;
use RuntimeException;

class Manager extends DataContainer implements ManagerInterface, \SplObserver
{
	
	/**
	 * Filesystem object.
	 * @var Phpf\Filesystem
	 */
	protected $filesystem;

	/**
	 * Assets object.
	 * @var Phpf\View\Assets
	 */
	protected $assets;
	
	/**
	 * Array of parser objects.
	 * Uses delegation pattern - view parsing is delegated to a parser
	 * @var array
	 */
	protected $parsers = array();

	/**
	 * Views requested and found.
	 * @var array
	 */
	protected $views = array();

	/**
	 * An events object.
	 * @var object
	 */
	protected $events;

	/**
	 * Construct manager with Filesystem and optionally an events object.
	 * 
	 * @param \Phpf\Filesystem $filesystem Filesystem instance.
	 * @param object $events [Optional] Events object with 'on' and 'trigger' methods.
	 * @return void
	 */
	public function __construct(Filesystem $filesystem, $events = null) {

		$this->filesystem = $filesystem;
		
		if (isset($events)) {
			$this->events = $events;
		}
		
		// add standard PHP view parser
		$this->addParser(new Parser\Php);
	}
	
	/**
	 * Returns the last returned view, if any.
	 */
	public function getCurrentView() {
		return empty($this->views) ? null : reset($this->views);
	}

	/**
	 * Find and return a View.
	 */
	public function getView($view, $type = 'php') {
		
		$view .= '.' . $type;
		
		if (isset($this->views[$view])) {
			return $this->views[$view];
		}
		
		if (! $parser = $this->getParser($type)) {
			throw new RuntimeException("No parser for view type $type.");
		}
		
		if (! $file = $this->filesystem->locate($view, 'views')) {
			return null;
		}
		
		return $this->views[$view] = new View($file, $this, $parser);
	}

	/**
	 * Find and return a view part.
	 */
	public function getPart($name, $type = 'php') {

		if (! $parser = $this->getParser($type)) {
			throw new RuntimeException("No parser for view type $type.");
		}
		
		if (! $file = $this->filesystem->locate($name.'.'.$type, 'view-parts')) {
			return null;
		}

		return new Part($file, $this, $parser);
	}
	
	/**
	 * Add a view parser
	 * 
	 * @param Phpf\View\Parser\ViewParserInterface $parser
	 * @return $this
	 */
	public function addParser(ViewParserInterface $parser) {
		$this->parsers[$parser->getType()] = $parser;
		return $this;
	}

	/**
	 * Get a registered parser for given type.
	 * 
	 * @param string $type Type
	 * @return Phpf\View\Parser\ViewParserInterface Parser for type.
	 */
	public function getParser($type) {
		return isset($this->parsers[$type]) ? $this->parsers[$type] : null;
	}

	/**
	 * Whether events library is available.
	 */
	public function usingEvents() {
		return isset($this->events);
	}

	/**
	 * Adds a callback to perform on action.
	 */
	public function on($action, $call, $priority = 10) {
		if (! isset($this->events)) {
			throw new RuntimeException("Cannot bind event without events object.");
		}
		$this->events->on($action, $call, $priority);
		return $this;
	}

	/**
	 * Triggers action callbacks.
	 */
	public function trigger($action, $view = null) {
		if (! isset($this->events)) {
			throw new RuntimeException("Cannot trigger event without events object.");
		}
		return $this->events->trigger($action, $view, $this);
	}
	
	/**
	 * Called when a view is being rendered.
	 * 
	 * [SplObserver]
	 * 
	 * @param View $view The view being rendered
	 * @return void
	 */
	public function update(\SplSubject $view) {
		if (isset($this->events)) {
			$this->events->trigger('view.render', $view, $this);
		}
	}
	
	/**
	 * Sets assets object if not set
	 */
	public function useAssets(Assets $assets = null) {
			
		if (! isset($this->assets)) {
				
			if (isset($assets)) {
				$this->assets = $assets;
			} else {
				if (isset($this->events)) {
					$this->assets = new Assets($this->events);
				} else {
					$this->assets = new Assets();
				}
			}
		}
		
		return $this;
	}

	/**
	 * Returns true if assets set
	 */
	public function usingAssets() {
		return isset($this->assets);
	}
	
	/**
	 * Returns Assets object
	 */
	public function getAssets() {
			
		if (! isset($this->assets)) {
			$this->useAssets();
		}
		
		return $this->assets;
	}
	
	public function registerAsset($handle, $uri, $attrs = array(), $enqueue = false) {
			
		if (! isset($this->assets)) {
			$this->useAssets();
		}
		
		$this->assets->register($handle, $uri, $attrs, $enqueue);
		
		return $this;
	}
	
	public function enqueueAsset($handle) {
		
		if (! isset($this->assets)) {
			throw new RuntimeException("Must enable assets to enqueue.");
		}
		
		$this->assets->enqueue($handle);
		
		return $this;
	}
	
	public function renderAsset($handle) {
		
		if (! isset($this->assets)) {
			throw new RuntimeException("Must enable assets to render.");
		}
		
		return $this->assets->render($handle);
	}
	
	/**
	 * Implement iManager
	 * Manages 'views'
	 */
	final public function manages() {
		return 'views';
	}
	
}
