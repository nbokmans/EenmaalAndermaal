<?php
session_start();
require_once('php/Gebruiker.php');

if (array_key_exists('login', $_POST))
{
	$loginReturn = Gebruiker::login($_POST['username'], $_POST['password'], (bool)$_POST['rememberLogin'], false);
	if ($loginReturn === false)
	{
		Gebruiker::logout();
		$GLOBALS['userLoggedIn'] = false;
	}
	else if ($loginReturn === 'banned')
	{
		Gebruiker::logout();
		$GLOBALS['userBanned'] = true;
		$GLOBALS['userLoggedIn'] = null;
	}
	unset($loginReturn);
	unset($_POST['username']);
	unset($_POST['password']);
	unset($_POST['rememberLogin']);
}
else if (array_key_exists('logout', $_POST)) Gebruiker::logout();

if (!isset($GLOBALS['userLoggedIn'])) $GLOBALS['userLoggedIn'] = Gebruiker::loggedIn();

if ($GLOBALS['userLoggedIn'] === null && !array_key_exists('logout', $_POST) && array_key_exists('username', $_COOKIE) &&
		array_key_exists('password', $_COOKIE))
{
	$loginReturn = Gebruiker::login($_COOKIE['username'], $_COOKIE['password'], false, true);
	if ($loginReturn === true)
		$GLOBALS['userLoggedIn'] = true;
	if ($loginReturn === false)
	{
		Gebruiker::logout();
		$GLOBALS['userLoggedIn'] = false;
	}
	else if ($loginReturn === 'banned')
	{
		Gebruiker::logout();
		$GLOBALS['userBanned'] = true;
		$GLOBALS['userLoggedIn'] = null;
	}
	unset($loginReturn);
	unset($_POST['username']);
	unset($_POST['password']);
	unset($_POST['rememberLogin']);
}
