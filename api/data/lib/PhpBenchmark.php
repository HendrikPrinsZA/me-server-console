<?php
class PhpBenchmark {

	private static function test_Math($count = 100000) {
		$time_start = microtime(true);
		$mathFunctions = array("abs", "acos", "asin", "atan", "bindec", "floor", "exp", "sin", "tan", "pi", "is_finite", "is_nan", "sqrt");
		foreach ($mathFunctions as $key => $function) {
			if (!function_exists($function)) unset($mathFunctions[$key]);
		}
		for ($i=0; $i < $count; $i++) {
			foreach ($mathFunctions as $function) {
				$r = call_user_func_array($function, array($i));
			}
		}
		return number_format(microtime(true) - $time_start, 3);
	}
	
	
	private static function test_StringManipulation($count = 100000) {
		$time_start = microtime(true);
		$stringFunctions = array("addslashes", "chunk_split", "metaphone", "strip_tags", "md5", "sha1", "strtoupper", "strtolower", "strrev", "strlen", "soundex", "ord");
		foreach ($stringFunctions as $key => $function) {
			if (!function_exists($function)) unset($stringFunctions[$key]);
		}
		$string = "the quick brown fox jumps over the lazy dog";
		for ($i=0; $i < $count; $i++) {
			foreach ($stringFunctions as $function) {
				$r = call_user_func_array($function, array($string));
			}
		}
		return number_format(microtime(true) - $time_start, 3);
	}


	private static function test_Loops($count = 9000000) {
		$time_start = microtime(true);
		for($i = 0; $i < $count; ++$i);
		$i = 0; while($i < $count) ++$i;
		return number_format(microtime(true) - $time_start, 3);
	}

	
	private static function test_IfElse($count = 5000000) {
		$time_start = microtime(true);
		for ($i=0; $i < $count; $i++) {
			if ($i == -1) {
			} elseif ($i == -2) {
			} else if ($i == -3) {
			}
		}
		return number_format(microtime(true) - $time_start, 3);
	}	
	
	public static function run ($echo = true) {
		$total 	     = 0;
		$math  	     = self::test_Math();
		$string      = self::test_StringManipulation();
		$loops       = self::test_Loops();
		$conditions  = self::test_IfElse();
		return array(
			'total'      => ($math + $string + $loops + $conditions),
			'math'       => $math,
			'string'     => $string,
			'loops'      => $loops,
			'conditions' => $conditions
		);
	}

}	
?>