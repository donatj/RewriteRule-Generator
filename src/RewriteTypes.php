<?php

namespace donatj\RewriteGenerator;

abstract class RewriteTypes {

	public const SERVER_REWRITE     = 1;
	public const PERMANENT_REDIRECT = 2;
	public const TEMPORARY_REDIRECT = 3;
	public const SEE_OTHER = 4;
	public const TEMPORARY_REDIRECT_PRESERVE_METHOD = 5;
	public const PERMANENT_REDIRECT_PRESERVE_METHOD = 6;

	public static function name( int $type ) : string {
		return match ($type) {
			self::SERVER_REWRITE     => 'Rewrite',
			self::PERMANENT_REDIRECT => '301',
			self::TEMPORARY_REDIRECT => '302',
			self::SEE_OTHER => '303',
			self::TEMPORARY_REDIRECT_PRESERVE_METHOD => '307',
			self::PERMANENT_REDIRECT_PRESERVE_METHOD => '308',
			default                  => throw new \InvalidArgumentException('invalid type: ' . $type, $type),
		};
	}

}
