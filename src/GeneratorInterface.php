<?php

namespace donatj\RewriteGenerator;

interface GeneratorInterface {

	/**
	 * Generate the Rewrite
	 *
	 * @param string $from
	 * @param string $to
	 * @param int    $type From the RewriteTypes enum
	 * @return string
	 * @throws \donatj\RewriteGenerator\Exceptions\GenerationException
	 */
	public function generateRewrite( string $from, string $to, int $type ) : string;

	/**
	 * Generate the Proceeding "Line Comment"
	 *
	 * @param string $from
	 * @param string $to
	 * @param int    $type From the RewriteTypes enum
	 * @return string
	 */
	public function lineComment( string $from, string $to, int $type ) : string;

	/**
	 * Generate a comment as a string
	 *
	 * @param string $text
	 * @return string
	 */
	public function comment( string $text ) : string;

}
