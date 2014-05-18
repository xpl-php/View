<?php

namespace Phpf\View;

use Phpf\Common\DataContainer;
use Phpf\View\Parser\ViewParserInterface;

class Part extends DataContainer implements \SplSubject
{

	public $file;
	
	protected $parser;
	
	protected $manager;
	
	/**
	 * Set the file, manager, parser, and any initial data.
	 * 
	 * @param string $file View file path.
	 * @param \Phpf\View\Parser\ViewParserInterface $parser Parser for the view.
	 * @param \Phpf\View\Manager $manager View manager instance.
	 * @param array $data [Optional] Array of view data to set.
	 * @return void
	 */
	public function __construct($file, Manager $manager, ViewParserInterface $parser, array $data = null) {

		$this->file = $file;
		
		$this->attach($manager);
		
		$this->parser = $parser;
		
		if (isset($data)) {
			$this->setData($data);
		}
	}
	
	public function getAllData() {
		return array_merge($this->manager->getData(), $this->data);
	}

	/**
	 * Render the view part.
	 */
	public function render() {
		
		if (isset($this->parser) && 'php' !== $this->parser->getType()) {
			return $this->parser->parse($this->file, $this->getAllData());
		} else {
			extract($this->getAllData(), EXTR_REFS);
			ob_start();
			require $this->file;
			return ob_get_clean();
		}
	}

	/**
	 * Returns rendered part (string).
	 */
	public function __toString() {
		return $this->render();
	}

	public function get($var) {
		return $this->manager->get($var);
	}
	
	public function set($var, $val) {
		$this->manager->set($var, $val);
		return $this;
	}
	
	public function exists($var) {
		return $this->manager->exists($var);
	}
	
	public function remove($var) {
		$this->manager->remove($var);
		return $this;
	}

	/**
	 * Updates manager when view is rendered.
	 * 
	 * Allows manager to trigger an event, if events are available.
	 * 
	 * [SplSubject]
	 * 
	 * @return void
	 */
	public function notify() {
		$this->manager->update($this);
	}
	
	/**
	 * Adds manager as an observer.
	 * 
	 * [SplSubject]
	 * 
	 * @param SplObserver $manager \Phpf\View\Manager instance.
	 * @return void
	 */
	public function attach(\SplObserver $manager) {
		$this->manager = $manager;
	}
	
	/**
	 * Removes manager as observer.
	 * 
	 * [SplSubject]
	 * 
	 * @param SplObserver $manager \Phpf\View\Manager instance.
	 * @return void
	 */
	public function detach(\SplObserver $manager) {
		if ($manager == $this->manager) {
			unset($this->manager);
		}
	}
	
}
