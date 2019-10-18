<?php

// https://github.com/leewrightdev/AppSheetTest1

require_once 'HttpCall.php';
require_once 'UserClasses.php';

$oUsers = new UserList();
$oUsers->getYoungest();

exit;

/**
 * Various tests used while developing
 */
print_r (UserDetails::isValidPhoneNumber('(425) 591-5588'));
print_r (UserDetails::isValidPhoneNumber('425-591-5588'));
print_r (UserDetails::isValidPhoneNumber('4xx-591-5588'));
print_r (UserDetails::isValidPhoneNumber('0000-591-5588'));

$oDetails = new UserDetails(15);
print_r($oDetails);
$oDetails = new UserDetails('bad data');
$oDetails = new UserDetails(105);

