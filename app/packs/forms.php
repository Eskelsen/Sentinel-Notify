<?php

# Forms

/*
 * Form Control Functions
 *
*/

/*
 * Input Validate Functions
 *
*/

function validEmail($email){
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return false;
	}
    [$username, $domain] = explode('@', $email);
    return checkdnsrr($domain, 'MX');
}

/*
 * Input Filter Functions
 *
*/

function filterEmail($in){
	return strtolower(trim($in)); // ToChange
}

function filterHash($in){
    $in = trim($in);
    return preg_replace("/[^A-Za-z0-9]/",'',$in);
}

function filterName($in){
    $in = trim($in);
    return filter_var($in, FILTER_SANITIZE_STRING);
}

function filterNumbers($in){
    return preg_replace('/[^0-9]/','',$in);
}
