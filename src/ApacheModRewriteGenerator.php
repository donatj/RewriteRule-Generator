<?php

namespace donatj\RewriteGenerator;

use donatj\RewriteGenerator\Exceptions\AmbiguousRelativeHostException;

class ApacheModRewriteGenerator implements GeneratorInterface {

	use OctothorpeCommentTrait;

	/**
	 * @inheritdoc
	 */
	public function generateRewrite( string $from, string $to, int $type ) : string {
		$parsedFrom = parse_url($from);
		$parsedTo   = parse_url($to);

		$toScheme = $parsedTo['scheme'] ?? '';

		$fromHost = $parsedFrom['host'] ?? '';
		$toHost   = $parsedTo['host'] ?? '';

		$fromQuery = $parsedFrom['query'] ?? '';
		$toQuery   = $parsedTo['query'] ?? '';

		$fromPath = $parsedFrom['path'] ?? '';
		$toPath   = $parsedTo['path'] ?? '';

		$output = '';

		if( !$fromHost && $toHost ) {
			throw new AmbiguousRelativeHostException('Unclear relative host. When the "FROM" URI specifies a HOST the "TO" MUST specify a HOST as well.');
		}
		if( $toHost && $fromHost !== $toHost ) {
			$output .= 'RewriteCond %{HTTP_HOST} ^' . preg_quote($fromHost) . '$';
			$output .= "\n";
			$prefix = $toScheme . '://' . $toHost . '/';
		} else {
			$prefix = '/';
		}

		$explodedQuery = explode('&', $fromQuery);
		foreach( $explodedQuery as $qs ) {
			if( $qs !== '' ) {
				$output .= 'RewriteCond %{QUERY_STRING} (^|&)' . preg_quote($qs) . '($|&)';
				$output .= "\n";
			}
		}

		$output .= 'RewriteRule ^' . preg_quote(ltrim($fromPath, '/')) . '$ ' . $prefix . ltrim($toPath, '/') . '?' . $toQuery;

		switch( $type ) {
			case RewriteTypes::SERVER_REWRITE:
				$output .= '&%{QUERY_STRING}';
				break;
			case RewriteTypes::PERMANENT_REDIRECT:
				$output .= ' [L,R=301]';
				break;
			default:
				throw new \InvalidArgumentException('Unhandled RewriteType: ' . $type, $type);
		}

		return $output;
	}

}
