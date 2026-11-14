<?php

namespace donatj\RewriteGenerator;

use donatj\RewriteGenerator\Exceptions\UnhandledUrlException;
use InvalidArgumentException;

class CaddyGenerator implements GeneratorInterface {

	use OctothorpeCommentTrait;

	private int $matcherNumber = 0;

	public function generateRewrite( string $from, string $to, int $type ) : string {
		$source = parse_url($from);
		$target = parse_url($to);
		if( $source === false || $target === false ) {
			throw new UnhandledUrlException('Invalid URL');
		}

		foreach( [$source, $target] as $url ) {
			if( isset($url['fragment']) || isset($url['user']) || isset($url['pass']) || isset($url['port']) ) {
				throw new UnhandledUrlException('Fragments, credentials, and ports are not supported by the Caddy prototype');
			}

			if( isset($url['host']) && !isset($url['scheme']) ) {
				throw new UnhandledUrlException('A host requires an HTTP or HTTPS scheme');
			}

			if( isset($url['scheme']) && !in_array($url['scheme'], ['http', 'https'], true) ) {
				throw new UnhandledUrlException('Only HTTP and HTTPS URLs are supported');
			}
		}

		$fromHost = $source['host'] ?? '';
		$toHost = $target['host'] ?? '';
		if( $toHost !== '' && $fromHost === '' ) {
			throw new UnhandledUrlException('A destination host requires a source host');
		}

		if( $type === RewriteTypes::SERVER_REWRITE && $toHost !== '' && $toHost !== $fromHost ) {
			throw new UnhandledUrlException('Caddy cannot rewrite a request to another host');
		}

		foreach( [$fromHost, $toHost] as $host ) {
			if( $host !== '' && !preg_match('/\A[a-zA-Z0-9.-]+\z/', $host) ) {
				throw new UnhandledUrlException('Unsupported host in Caddy prototype');
			}
		}

		$fromPath = '/' . ltrim($source['path'] ?? '', '/');
		$toPath = '/' . ltrim($target['path'] ?? '', '/');
		foreach( [$fromPath, $toPath] as $path ) {
			if( !preg_match('#\A[a-zA-Z0-9/._~-]+\z#', $path) ) {
				throw new UnhandledUrlException('Unsupported path in Caddy prototype');
			}
		}

		$query = $this->queryPairs($source['query'] ?? '');
		$this->queryPairs($target['query'] ?? '');
		$targetQuery = $target['query'] ?? '';
		$destination = $toPath . ($targetQuery !== '' ? '?' . $targetQuery : '');
		if( $toHost !== '' && $toHost !== $fromHost ) {
			$destination = ($target['scheme'] ?? 'http') . '://' . $toHost . $destination;
		}

		$matcher = '@generated' . ++$this->matcherNumber;
		$output = "{$matcher} {\n";
		$output .= "\tpath_regexp ^" . str_replace('.', '\\.', $fromPath) . "\$\n";
		if( $toHost !== '' && $toHost !== $fromHost ) {
			$output .= "\thost {$fromHost}\n";
		}

		if( $query !== [] ) {
			$output .= "\tquery " . implode(' ', $query) . "\n";
		}

		$output .= "}\n";

		return match ($type) {
			RewriteTypes::PERMANENT_REDIRECT => $output . "redir {$matcher} {$destination} 301",
			RewriteTypes::SERVER_REWRITE => $output . "rewrite {$matcher} {$destination}" . ($targetQuery !== '' ? '&{query}' : ''),
			default => throw new InvalidArgumentException("Unhandled RewriteType: {$type}", $type),
		};
	}

	/** @return list<string> */
	private function queryPairs( string $query ) : array {
		if( $query === '' ) {
			return [];
		}

		$pairs = [];
		$keys = [];
		foreach( explode('&', $query) as $pair ) {
			if( !preg_match('/\A([a-zA-Z0-9._~-]+)=([a-zA-Z0-9._~-]*)\z/', $pair, $matches) || isset($keys[$matches[1]]) ) {
				throw new UnhandledUrlException('Unsupported or repeated query parameter in Caddy prototype');
			}

			$keys[$matches[1]] = true;
			$pairs[] = $pair;
		}

		return $pairs;
	}

}
