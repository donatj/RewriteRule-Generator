<?php

namespace donatj\RewriteGenerator;

trait OctothorpeCommentTrait {

	/**
	 * @inheritdoc
	 */
	public function lineComment( string $from, string $to, int $type ) : string {
		return sprintf('# %s --- %s => %s', RewriteTypes::name($type), $from, $to);
	}

	/**
	 * Generate a comment as a string
	 *
	 * @param string $text
	 * @return string
	 */
	public function comment( string $text ) : string {
		return "# {$text}";
	}

}
