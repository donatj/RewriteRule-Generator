<?php

namespace donatj\RewriteGenerator;

abstract class RewriteTypes {

	public const SERVER_REWRITE     = 1;
	public const PERMANENT_REDIRECT = 2;

	public static function name( int $type ) : string {
		return match ($type) {
			self::SERVER_REWRITE     => 'Rewrite',
			self::PERMANENT_REDIRECT => '301',
			default                  => throw new \InvalidArgumentException('invalid type', $type),
		};
	}

}
