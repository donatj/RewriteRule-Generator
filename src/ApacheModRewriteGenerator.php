<?php

namespace donatj\RewriteGenerator;

use donatj\RewriteGenerator\Exceptions\AmbiguousRelativeHostException;
use donatj\RewriteGenerator\Exceptions\UnhandledUrlException;
use InvalidArgumentException;

class ApacheModRewriteGenerator implements GeneratorInterface {

	use OctothorpeCommentTrait;

	/**
	 * @inheritdoc
	 */
	public function generateRewrite( string $from, string $to, int $type ) : string {
		$parsedFrom = parse_url($from);
		$parsedTo   = parse_url($to);

		if( !empty($parsedFrom['fragment']) ) {
			throw new UnhandledUrlException(
				'"FROM" URI fragments cannot be handled - fragments are not sent in the request to the server.'
			);
		}

		if( !empty($parsedTo['fragment']) ) {
			throw new UnhandledUrlException(
				'"TO" URI fragments are not supported at this time.'
			);
		}

		$toScheme = $parsedTo['scheme'] ?? '';

		$fromHost = $parsedFrom['host'] ?? '';
		$toHost   = $parsedTo['host'] ?? '';

		$fromQuery = $parsedFrom['query'] ?? '';
		$toQuery   = $parsedTo['query'] ?? '';

		$fromPath = urldecode($parsedFrom['path'] ?? '');
		$toPath   = urldecode($parsedTo['path'] ?? '');

		$output = '';

		if( !$fromHost && $toHost ) {
			throw new AmbiguousRelativeHostException(
				'Unclear relative host. When the "FROM" URI specifies a HOST the "TO" MUST specify a HOST as well.'
			);
		}

		if( $toHost && $fromHost !== $toHost ) {
			$output .= 'RewriteCond %{HTTP_HOST} ^' . preg_quote($fromHost, ' ') . '$';
			$output .= "\n";
			$prefix = "{$toScheme}://{$toHost}/";
		} else {
			$prefix = '/';
		}

		$explodedQuery = explode('&', $fromQuery);
		foreach( $explodedQuery as $qs ) {
			if( $qs !== '' ) {
				$output .= 'RewriteCond %{QUERY_STRING} (?:^|&)' . preg_quote($qs, ' ') . '(?:$|&)';
				$output .= "\n";
			}
		}

		$output .= 'RewriteRule ^' . preg_quote(ltrim($fromPath, '/'), ' ') . '$ ' . $this->escapeSubstitution($prefix . ltrim($toPath, '/')) . '?' . $toQuery;

		return match ($type) {
			RewriteTypes::SERVER_REWRITE     => "{$output}&%{QUERY_STRING}",
			RewriteTypes::PERMANENT_REDIRECT => "{$output} [L,R=301]",
			default                          => throw new InvalidArgumentException("Unhandled RewriteType: {$type}", $type),
		};
	}

	private function escapeSubstitution( string $input ) : string {
		$result = preg_replace('/[-\s%$\\\\]/', '\\\\$0', $input);
		if( $result === null ) {
			throw new \RuntimeException('preg_replace failed - ' . preg_last_error());
		}

		return $result;
	}

}
