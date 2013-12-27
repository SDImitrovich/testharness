<?php

// gets the specified GET or POST variable, trimmed
// returns an empty string if the variable isn't defined
function trimmed_or_empty($scope, $var)	
{
	switch (strtoupper($scope) )
	{
		case "GET":
			return (isset($_GET[$var])) ? trim($_GET[$var]) : '';
		case "POST":
			return (isset($_POST[$var])) ? trim($_POST[$var]) : '';
		default:
			return '';
	}	
}

// gets the specified GET or POST variable, converted to int if possible
// returns zero if the variable isn't defined
function int_or_zero($scope, $var)	
{
	switch (strtoupper($scope) )
	{
		case "GET":
			return (isset($_GET[$var])) ? intval($_GET[$var]) : 0;
		case "POST":
			return (isset($_POST[$var])) ? intval($_POST[$var]) : 0;
		default:
			return '';
	}	
}

// returns TRUE if the specified string is not empty
function is_not_empty_string($str)
{
	return strlen(trim($str)) > 0;
}

// generates a set of random numbers within specified array,
// without duplication
// they keys will not be preserved - this assumes that 
// the array is not an associative array and the keys aren't important
function random_range($list, $count)
{
	// if count > to range, we can't generate $count of unique values
	if ($count > count($list) ) { return $list; }
	
	$retval = array();
	if (shuffle($list) )
	{
		// these are keys into the $keys array, so we'll need to do
		// double-dereferencing to get to actual values
		$randKeys = array_rand($list, $count);
		foreach ($randKeys as $key)
		{
			array_push($retval, $list[$key]);
		}
	}
	return $retval;
}
?>
