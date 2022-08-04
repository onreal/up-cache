<?php

namespace Upio\UpCache\Helpers;

use ReflectionClass;

class Validators {
	/**
	 * @param $src
	 * @return bool
	 */
	public static function validateSourceOrigin($src): bool
	{
		return strpos($src, get_site_url()) !== false;
	}

	/**
	 * @param $rules
	 * @return bool
	 */
	public static function validateRule($rules): bool {
		if (empty($rules)) {
			return false;
		}
		if (!self::validateRuleByKey($rules)) {
			return false;
		}

		return true;
	}

	/**
	 * Here we use reflection in order to get the types enum constants from an abstract class
	 * and validate with the incoming rules type.
	 * @param $rules
	 * @return bool
	 */
	private static function validateRuleByKey ($rules): bool {
		$rulesType = array_keys( $rules );
		// TODO check performance here, for the sake of time
		$lifecycleTypes = new ReflectionClass('Upio\UpCache\Enums\LifecycleType');
		$allowed_types = array_values( $lifecycleTypes->getConstants() );
		foreach ( $rulesType as $type ) {
			if ( !in_array( $type, $allowed_types ) ) {
				return false;
			}
		}
		return true;
	}
}