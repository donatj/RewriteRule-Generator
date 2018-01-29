<?php

namespace donatj\RewriteGenerator;

abstract class RewriteTypes {

	const SERVER_REWRITE     = 1;
	const PERMANENT_REDIRECT = 2;

	public static function name( int $type ) {
		switch( $type ) {
			case self::SERVER_REWRITE:
				return 'Rewrite';
			case self::PERMANENT_REDIRECT:
				return '301';
			default:
				throw new \InvalidArgumentException('invalid type', $type);
		}
	}

}
