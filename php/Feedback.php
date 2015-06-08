<?php
require_once('DAO.php');
require_once('Gebruiker.php');
require_once('Voorwerp.php');

class Feedback
{
	/**
	 * Geef de hoeveelheid gegeven van een bepaald type feedback of van alle types feedbaack terug.
	 *
	 * @param $username String gebruikersnaam
	 * @param $feedbackType String type feedback. '-', '0', '+' of 'all'.
	 * @return bool|int|null <code>false</code> bij foutieve parameters en database fouten. <code>null</code> bij geen
	 * rij-resultaten. <code>int</code> bij succes.
	 */
	public static function getFeedbackCount($username, $feedbackType)
	{
		$validFeedbackTypes = array("-", "0", "+", "all");
		if (is_string($username) === false) return false;
		if (Gebruiker::usernameInUse($username) === false) return false;
		if (in_array($feedbackType, $validFeedbackTypes) === false) return false;

		$dao = new DAO();
		if ($dao->openConnection() === false)
		{
			return false;
		}

		$query = "SELECT COUNT(*) FROM Feedback WHERE Gebruikersnaam = '$username'" .
				($feedbackType === "all" ? "" : "AND Feedbacksoort = '$feedbackType'");
		$stmt = $dao->queryOpen($query);
		if ($stmt === false)
		{
			$dao->closeConnection();
			return false;
		}
		$row = $dao->fetchNextRow($stmt);
		if ($row === false)
		{
			$dao->queryClose($stmt);
			$dao->closeConnection();
			return false;
		}
		if ($row === null)
		{
			$dao->queryClose($stmt);
			$dao->closeConnection();
			return null;
		}
		$field = $dao->getField($stmt, 0, SQLSRV_PHPTYPE_INT);
		$dao->queryClose($stmt);
		$dao->closeConnection();
		return $field;
	}

	public static function addFeedback($pid, $user, $fb, $datetime, $comment)
	{
		if (is_float($pid) === false) return 'false1';
		if (is_string($user) === false) return 'false2';
		if ((is_string($fb) || strlen($fb) == 1) === false) return 'false3';
		if (is_string($comment) === false) return 'false4';
		if (Voorwerp::isValidProductId($pid) === false) return 'false5';
		if (Gebruiker::usernameInUse($user) === false) return 'false6';

		$dao = new DAO();
		if ($dao->openConnection() === false)
		{
			$dao->closeConnection();
			return false;
		}
		$timeHMS = date('H:i:s', strtotime('now'));
		return $dao->query("INSERT INTO Feedback VALUES ('$pid', '$user', '$fb', '$datetime', '$timeHMS', '$comment')");
	}
} 