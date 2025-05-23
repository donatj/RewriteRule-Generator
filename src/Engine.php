<?php

namespace donatj\RewriteGenerator;

use donatj\RewriteGenerator\Exceptions\GenerationException;

class Engine {

	private int $lastErrorCount = 0;

	public function __construct(
		private readonly GeneratorInterface $generator
	) {}

	public function generate( string $input, int $type, bool $comments ) : string {
		$errors = 0;
		$output = '';

		$input = preg_replace('/\h+/', "\t", $input); // Spacing Cleanup
		if( $input === null ) {
			throw new \RuntimeException('preg_replace failed - ' . preg_last_error());
		}

		$lines = explode(PHP_EOL, $input);

		if( trim($input) !== '' ) {
			foreach( $lines as $line ) {
				$line = trim($line);
				if( $line === '' ) {
					continue;
				}

				$explodedLine = explode("\t", $line);

				if( count($explodedLine) !== 2 ) {
					$output .= $this->generator->comment('ERROR: Malformed Line Skipped: ' . $line);
					$output .= "\n\n";
					$errors++;

					continue;
				}

				try {
					if( $comments ) {
						$output .= $this->generator->lineComment($explodedLine[0], $explodedLine[1], $type);
						$output .= "\n";
					}

					$output .= $this->generator->generateRewrite($explodedLine[0], $explodedLine[1], $type);
					$output .= "\n\n";
				} catch( GenerationException $e ) {
					$output .= $this->generator->comment('ERROR: ' . $e->getMessage() . ': ' . $line);
					$output .= "\n";
					$errors++;
				}
			}
		}

		if( $errors > 0 ) {
			$output = $this->generator->comment("WARNING: Input contained {$errors} error(s)") . "\n\n{$output}";
		}

		$this->lastErrorCount = $errors;

		return rtrim($output) . "\n";
	}

	public function getLastErrorCount() : int {
		return $this->lastErrorCount;
	}

}
