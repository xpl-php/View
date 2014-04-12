<?php

namespace Phpf\View;

use Phpf\Util\DataContainer;

class Part extends DataContainer
{

	public $file;

	protected $parser;

	public function __construct($file, Parser\AbstractParser $parser = null, array $data = null) {

		$this->file = $file;

		if (isset($parser)) {
			$this->parser = $parser;
		}

		if (isset($data)) {
			$this->setData($data);
		}
	}

	/**
	 * Render the view part.
	 */
	public function render() {

		if (isset($this->parser) && 'php' !== $this->parser->getType()) {

			return $this->parser->parse($this->file, $this->getData());

		} else {

			extract($this->data);

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

}
