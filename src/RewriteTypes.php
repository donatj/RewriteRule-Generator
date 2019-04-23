<?php

namespace donatj\RewriteGenerator;

abstract class RewriteTypes {

	public const SERVER_REWRITE     = 1;
	public const PERMANENT_REDIRECT = 2;

	public static function name( int $type ) : string {
		switch( $type ) {
			case self::SERVER_REWRITE:
				return 'Rewrite';
			case self::PERMANENT_REDIRECT:
				return '301';
		}

		throw new \InvalidArgumentException('invalid type', $type);
	}

}
