<?php

namespace App\Libraries;

class Install
{

	/**
	 * Fetches the array of every string combination of CRUDX permissions
	 *
     * Used when building enum values for the databases
     *
	 * @return string[]
	 */
	static public function getAllPermissionStringCombinations() {
        // Create array of every combination of permissions - string 5 characters long of 1 and 0
        $permissionTypes = ["0", "1"];
        $permission_string_length = 5;
        return self::allPossibleCombinations($permissionTypes, $permission_string_length);
    }

    /**
	 * Creates an array of every possible CRUDX permissions string where "1" is allowed and "0" is unallowed
     *
     * @var string[] $types             | The strings to combine, in our case "1" and "0"
     * @var int      $length            | The length of the final string combination, for CRUDX it's 5
     * @var string   $permission_string | The string we are currently building
     *
	 * @return string[]
	 */
    static private function allPossibleCombinations(array $types, int $length, string $permission_string = '') {
        if(strlen($permission_string) == $length) return [ $permission_string ];
        $combinations = [];

        for($ii = 0; $ii < count($types); $ii++) {
            $combinations = array_merge($combinations, self::allPossibleCombinations($types, $length, $permission_string . $types[$ii]));
        }

        return $combinations;
    }

}
