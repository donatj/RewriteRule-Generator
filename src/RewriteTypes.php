<?php

namespace donatj\RewriteGenerator;

abstract class RewriteTypes {

	const SERVER_REWRITE     = 1;
	const PERMANENT_REDIRECT = 2;
        const TEMPORARY_REDIRECT = 3;

	public static function name( int $type ) : string {
		switch( $type ) {
			case self::SERVER_REWRITE:
				return 'Rewrite';
			case self::PERMANENT_REDIRECT:
				return '301';
			case self::TEMPORARY_REDIRECT:
				return '302';
		}

		throw new \InvalidArgumentException('invalid type', $type);
	}

}
