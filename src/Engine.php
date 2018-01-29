<?php

namespace donatj\RewriteGenerator;

class Engine {

	/**
	 * @var \donatj\RewriteGenerator\GeneratorInterface
	 */
	private $generator;

	public function __construct( GeneratorInterface $generator ) {
		$this->generator = $generator;
	}


	public function generate( string $input, int $type, bool $comments ) : string {
		$errors = 0;
		$output = '';

		$input = preg_replace('/(\t| )+/', '	', $input); // Spacing Cleanup

		$lines = explode(PHP_EOL, $input);

		if( trim($input) != '' ) {
			foreach( $lines as $line ) {
				$line = trim($line);
				if( $line == '' ) {
					continue;
				}
				$explodedLine = explode("\t", $line);

				if( count($explodedLine) != 2 ) {
					$output .= $this->generator->comment('ERROR: Malformed Line Skipped: ' . $line);
					$output .= "\n";
					$errors += 1;
					continue;
				}

				try {
					if( $comments ) {
						$output .= $this->generator->lineComment($explodedLine[0], $explodedLine[1], $type);
						$output .= "\n";
					}

					$output .= $this->generator->generateRewrite($explodedLine[0], $explodedLine[1], $type);
					$output .= "\n\n";
				} catch( \Exception $e ) {
					$output .= $this->generator->comment('ERROR: ' . $e->getMessage() . ': ' . $line);
					$output .= "\n";
					$errors += 1;
				}
			}
		}

		if( $errors > 0 ) {
			$output = $this->generator->comment("WARNING: Input contained {$errors} error(s)") . "\n\n{$output}";
		}

		return $output;
	}

}