<?php
require_once('DAO.php');

class Gebruiker
{
	/**
	 * Kijkt of een emailadres al in gebruik is.
	 *
	 * @param $email String emailadres.
	 * @return bool <code>true</code> als een emailadres in gebruik is,
	 * <code>null</code> als deze niet in gebruik in, <code>false</code> bij een fout.
	 */
	public static function emailInUse($email)
	{
		if (!is_string($email)) return false;
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

		$dao = new DAO();
		$dao->openConnection();

		$stmt = $dao->queryOpen("select count(*) from Gebruiker where Emailadres = '$email'");
		if ($stmt === false)
		{
			$dao->closeConnection();
			return false;
		}
		if ($dao->fetchNextRow($stmt) !== true)
		{
			$dao->queryClose($stmt);
			$dao->closeConnection();
			return false;
		}

		$result = $dao->getField($stmt, 0);

		$dao->queryClose($stmt);
		$dao->closeConnection();

		if ($result === false) return false;
		if ($result === 1) return true;

		return null;
	}

	/**
	 * Registreer een gebruiker.<br/>
	 * <i>Let op:</i> Deze functie controleert <i>niet</i> of de input geldig is.
	 *
	 * @param $username String Gebruikersnaam.
	 * @param $password String Wachtwoord als SHA512 hash.
	 * @param $firstName String Voornaam.
	 * @param $lastName String Achternaam.
	 * @param $address1 String Adresregel 1.
	 * @param $address2 String Adresregel 2.
	 * @param $postalCode String Postcode.
	 * @param $residence String Plaatsnaam.
	 * @param $country String Land.
	 * @param $dayOfBirth String GeboorteDag.
	 * @param $email String Emailadres.
	 * @param $questionNumber String Vraagnummer.
	 * @param $questionAnswer String Vraag antwoord.
	 * @return bool <code>true</code> bij succes, <code>false</code> bij fout.
	 */
	public static function register($username, $password, $firstName, $lastName, $address1, $address2, $postalCode, $residence,
	                                $country, $dayOfBirth, $email, $questionNumber, $questionAnswer)
	{
		$dao = new DAO();
		$dao->openConnection();

		$return = $dao->query("insert into [Gebruiker] ([Gebruikersnaam], [Voornaam], [Achternaam], [Adresregel1], [Adresregel2],
			                        [Postcode], [Plaatsnaam], [Land], [GeboorteDag], [Emailadres], [Wachtwoord],
			                        [Vraagnummer], [Antwoord], [IsVerkoper], [IsBanned])
			values ('$username', '$firstName', '$lastName', '$address1', '$address2', '$postalCode', '$residence', '$country',
			        '$dayOfBirth', '$email', '$password', '$questionNumber', '$questionAnswer', '0', '0')");

		$dao->closeConnection();
		unset($dao);

		return $return;
	}

	/**
	 * Voeg een telefoonnummer toe aan de account van een gebruiker.
	 *
	 * @param $username String Gebruikersnaam.
	 * @param $phonenumber String Telefoonnummer.
	 * @param $follownumber int [optional] Volgnummer.
	 * @return bool <code>true</code> bij succes, <code>false</code> bij een fout.
	 */
	public static function addPhoneNumber($username, $phonenumber, $follownumber = null)
	{
		if (!is_string($username)) return false;
		if (!is_string($phonenumber)) return false;
		if ($follownumber !== null && !is_int($follownumber)) return false;
		if (strlen($phonenumber) < 10 || strlen($phonenumber) > 13) return false;
		if (preg_match("/^([\+0-9][0-9]*)$/", $phonenumber) === 0) return false;
		if (Gebruiker::usernameInUse($username) !== true) return false;

		$dao = new DAO();
		if ($dao->openConnection() === false) return false;;

		if ($follownumber === null)
		{
			$stmt = $dao->queryOpen("select max(Volgnummer) from Gebruikerstelefoon where Gebruikersnaam = '$username'");
			if ($stmt === false)
			{
				$dao->closeConnection();
				return false;
			}

			$fetchResult = $dao->fetchNextRow($stmt);
			if ($fetchResult === false)
			{
				$dao->queryClose($stmt);
				$dao->closeConnection();
				return false;
			}
			if ($fetchResult === null) $follownumber = 1;
			else
			{
				$follownumber = $dao->getField($stmt, 0);
				if ($follownumber === false)
				{
					unset($fetchResult);
					$dao->queryClose($stmt);
					$dao->closeConnection();
				}
				$follownumber++;
			}
			unset($fetchResult);

			$dao->queryClose($stmt);
		}

		$result = $dao->query("insert into Gebruikerstelefoon(Gebruikersnaam, Volgnummer, Telefoonnummer) values
		                      ('$username', '$follownumber', '$phonenumber')");

		$dao->closeConnection();
		unset($dao);

		return $result;
	}

	/**
	 * Checkt of de gebruikersnaam al bestaat.
	 *
	 * @param $username String Gebruikersnaam.
	 * @return bool|string <code>true</code> als de gebruikersnaam in gebruik is,
	 * <code>null</code> als deze niet in gebruik is, <code>false</code> bij een fout.
	 */
	public static function usernameInUse($username)
	{
		if (!is_string($username)) return false;

		$dao = new DAO();
		if ($dao->OpenConnection() === false) return false;

		$stmt = $dao->QueryOpen("select count(*) from Gebruiker where Gebruikersnaam = '$username'");
		if ($stmt === false)
		{
			$dao->closeConnection();
			return false;
		}
		$rowCheck = $dao->FetchNextRow($stmt);
		if ($rowCheck === false || $rowCheck === null)
		{
			$dao->queryClose($stmt);
			$dao->closeConnection();
			return false;
		}
		unset($rowCheck);

		$value = $dao->GetField($stmt, 0);
		$dao->QueryClose($stmt);
		$dao->CloseConnection();
		unset($dao);

		if ($value === false) return false;
		if ($value === 1) return true;
		return null;
	}

	/**
	 * Verwijder een telefoonnummer van een gebruiker.
	 *
	 * @param $username String Gebruikersnaam.
	 * @param $phonenumber String Telefoonnummer.
	 * @return bool <code>true</code> bij succes, <code>false</code> bij een fout.
	 */
	public static function removePhoneNumber($username, $phonenumber)
	{
		if (!is_string($username)) return false;
		if (!is_string($phonenumber)) return false;
		if (strlen($phonenumber) < 10 || strlen($phonenumber) > 13) return false;
		if (preg_match("/^([\+0-9][0-9]*)$/", $phonenumber) === 0) return false;
		if (Gebruiker::usernameInUse($username) !== true) return false;

		$dao = new DAO();
		if ($dao->openConnection() === false) return false;

		$result = $dao->query("delete from Gebruikerstelefoon where
		Gebruikersnaam = '$username' and Telefoonnummer = '$phonenumber'");

		$dao->closeConnection();
		unset($dao);

		return $result;
	}

	/**
	 * Haalt van een gebruiker de gegevens uit de database.
	 * @param $username String Gebruikersnaam.
	 * @return array|bool Geeft een array weer met de gegevens van een gebruiker,
	 * <code>null</code> bij een niet-bestaande gebruiker, <code>false</code> bij fout.
	 */
	public static function getAccountDetails($username)
	{
		if (!is_string($username)) return false;

		$dao = new DAO();
		if ($dao->openConnection() === false) return false;
		$stmt = $dao->queryOpen("SELECT * FROM Gebruiker WHERE Gebruikersnaam = '$username'");
		if ($stmt === false)
		{
			$dao->closeConnection();
			return false;
		}
		$row = $dao->fetchNextRowArrayAssoc($stmt);

		$dao->queryClose($stmt);
		$dao->closeConnection();

		return $row;
	}

	/**
	 * Checkt of de ingevoerde geboortedatum groter is dan 14 jaar.
	 *
	 * @param $birthday String Geboortedatum jjjj-mm-dd
	 * @return bool|string <code>true</code> als deze geldig is en ouder dan 14 jaar,
	 * <code>ongeldig</code> bij ongeldige invoer, <code>minimaal</code> als de leeftijd kleiner is dan 14.
	 */
	public static function checkBirthday($birthday)
	{
		if (strlen($birthday) !== 10) return 'ongeldig';

		$geboortedatumArray = explode('-', $birthday); // yyyy-mm-dd.
		if (sizeof($geboortedatumArray) !== 3) return 'ongeldig';

		if (!checkdate($geboortedatumArray[1], $geboortedatumArray[2], $geboortedatumArray[0]))
			return 'ongeldig';

		/* Bereken leeftijd. Aangepast van bron: https://stackoverflow.com/questions/3776682/php-calculate-age. */
		if ((date("md", date("U", mktime(0, 0, 0, $geboortedatumArray[1],
						$geboortedatumArray[2], $geboortedatumArray[0]))) > date("md")
						? ((date("Y") - $geboortedatumArray[0]) - 2)
						: (date("Y") - $geboortedatumArray[0]))
				< 14
		)
			return 'minimaal';

		return true;
	}

	/**
	 * Haalt de bied geschiedenis van de gebruiker uit de database.
	 *
	 * @param $username String Gebruikersnaam.
	 * @return array|bool|null Geeft een array met de bied geschiedenis van de gebruiker weer, <code>false</code> bij fout.
	 */
	public static function getBids($username)
	{
		if (!is_string($username)) return false;

		$dao = new DAO();
		if ($dao->openConnection() === false) return false;

		$stmt = $dao->queryOpen("SELECT voorwerp.voorwerpnummer, voorwerp.titel, MAX(bod.bodbedrag) AS Hoogste_bod
FROM Bod
	INNER JOIN Voorwerp
		ON Bod.voorwerp = Voorwerp.voorwerpnummer
WHERE bod.Gebruiker = '$username'
GROUP BY voorwerp.voorwerpnummer, voorwerp.titel, voorwerp.beschrijving
		");
		if ($stmt === false)
		{
			$dao->closeConnection();
			return false;
		}

		$objects = array();
		$index = 0;
		while ($row = $dao->fetchNextRowArrayAssoc($stmt))
		{
			$objects[$index] = array(
					'voorwerpnummer' => $row['voorwerpnummer'],
					'titel'          => $row['titel'],
					'Hoogste_bod'    => $row['Hoogste_bod'],
			);
			$index++;
		}
		if ($row === false)
		{
			$dao->queryClose($stmt);
			$dao->closeConnection();
			return false;
		}

		$dao->queryClose($stmt);
		$dao->closeConnection();
		unset($dao);

		if ($index === 0) return null;

		return $objects;
	}

	/**
	 * Haalt lle veilingen van de gebruiker uit de database.
	 *
	 * @param $username String Gebruikersnaam.
	 * @return array|bool|null Geeft een array met alle veilingen van de gebruiker, <code>false</code> bij fout.
	 */
	public static function getAuctions($username)
	{
		if (!is_string($username)) return false;

		$dao = new DAO();
		if ($dao->openConnection() === false) return false;

		$stmt = $dao->queryOpen("SELECT v.Voorwerpnummer, v.Titel FROM Voorwerp v WHERE v.Verkoper = '$username'");
		if ($stmt === false)
		{
			$dao->closeConnection();
			return false;
		}

		$objects = array();
		$index = 0;
		while ($row = $dao->fetchNextRowArrayAssoc($stmt))
		{
			$objects[$index] = array(
					'voorwerpnummer' => $row['Voorwerpnummer'],
					'titel'          => $row['Titel'],
			);
			$index++;
		}
		if ($row === false)
		{
			$dao->queryClose($stmt);
			$dao->closeConnection();
			return false;
		}

		$dao->queryClose($stmt);
		$dao->closeConnection();
		unset($dao);

		if ($index === 0) return null;

		return $objects;
	}

	/**
	 * Haalt van een bepaald opject de afbeelding op.
	 *
	 * @param $objectnumber float objectnummer.
	 * @param $numberOfPictures int hoeveelheid afbeeldingen.
	 * @return array|bool|null Geeft een array met een afbeelding van een bepaald opject weer, <code>false</code> bij fout.
	 */
	public static function getBidsImage($objectnumber, $numberOfPictures)
	{
		if (!is_float($objectnumber) || !is_int($numberOfPictures)) return false;

		$dao = new DAO();
		if ($dao->openConnection() === false) return false;
		$stmt = $dao->queryOpen("SELECT TOP($numberOfPictures)Bestandsnaam
								FROM Bestand
								WHERE voorwerp= '$objectnumber'");
		if ($stmt === false)
		{
			$dao->closeConnection();
			return false;
		}

		$objects = array();
		$index = 0;
		while ($row = $dao->fetchNextRowArrayAssoc($stmt))
		{
			$objects[$index] = array(
					'Bestandsnaam' => $row['Bestandsnaam'],
			);
			$index++;
		}
		if ($row === false)
		{
			$dao->queryClose($stmt);
			$dao->closeConnection();
			return false;
		}
		$dao->queryClose($stmt);
		$dao->closeConnection();

		unset($dao);

		if ($index === 0) return null;

		return $objects;
	}

	/**
	 * Haalt de object op die een verkoper heeft toegevoegd.
	 * @param $username String gebruikersnaam
	 * @return array|bool|null Geeft een array met de toegevoegde objecten van de gebruiker weer,
	 * <code>null</code> bij een niet bestaande gebruiker, <code>false</code> bij fout.
	 */
	public static function getSellersObjects($username)
	{
		if (!is_string($username)) return false;

		$dao = new DAO();
		if ($dao->openConnection() === false) return false;
		$stmt = $dao->queryOpen("SELECT voorwerp.titel, voorwerp.beschrijving, voorwerp.startprijs
								 FROM voorwerp
								 WHERE voorwerp.verkoper = '$username' ");
		if ($stmt === false)
		{
			$dao->closeConnection();
			return false;
		}
		$row = $dao->fetchNextRowArrayAssoc($stmt);

		$dao->queryClose($stmt);
		$dao->closeConnection();

		return $row;
	}

	/**
	 * Logt een gebruiker in. De login wordt gecontroleerd.
	 *
	 * @param $username String Gebruikersnaam.
	 * @param $password String Wachtwoord. (met of zonder hash, zie $hashed)
	 * @param $remember bool [optional] <code>true</code>Om gegevens in cookie op te slaan, <code>false</code> voor sessie.
	 * @param $hashed bool [optional] <code>true</code> als het wachtwoord al gehashed is, anders <code>false</code>.
	 * @return bool|null <code>true</code> als de login geldig is en als de gebruiker ingelogd is,
	 * <code>null</code> als deze niet geldig is, <code>false</code> bij een fout.
	 */
	public static function login($username, $password, $remember = false, $hashed = true)
	{
		if (!is_bool($remember) || !is_bool($hashed)) return false;
		if (!is_string($username) || !is_string($password)) return null;
		$details = Gebruiker::getAccountDetails($username);
		if ($details['IsBanned']) return 'banned';
		if ($hashed)
		{
			if (strlen($password) !== 128) return null;
		}
		else $password = strtolower(hash('sha512', $password, false));
		$valid = Gebruiker::isLoginValid($username, $password);
		if ($valid === false || $valid === null) return $valid;
		unset($valid);

		/*
		 * Dit breekt PHPUnit omdat daar geen sessie bestaat.
		 * Je mag er van uit gaan dat iedere PHP installatie session aan heeft staan.
		 * if (session_status() !== PHP_SESSION_ACTIVE) return false;
		 */
		if ($remember)
		{
			/* Onthoud gebruiker voor één week. */
			$cookieExpireDate = strtotime('+1 week');
			setcookie('username', $username, $cookieExpireDate);
			setcookie('password', $password, $cookieExpireDate);
			unset($cookieExpireDate);
		}
		$_SESSION['username'] = $username;
		$_SESSION['password'] = $password;

		return true;
	}

	/**
	 * Valideert de gegeven logingegevens.
	 *
	 * @param $username String Gebruikersnaam.
	 * @param $password String Wachtwoord. (met of zonder hash, zie $hashed)
	 * @param $hashed bool [optional] <code>true</code> als het wachtwoord al gehashed is, anders <code>false</code>.
	 * @return bool|null <code>true</code> als de login geldig is,
	 * <code>null</code> als deze niet geldig is, <code>false</code> bij een fout.
	 */
	public static function isLoginValid($username, $password, $hashed = true)
	{
		if (!is_bool($hashed)) return false;
		if (!is_string($username) || !is_string($password)) return null;

		if ($hashed)
		{
			if (strlen($password) !== 128) return null;
		}
		else $password = strtolower(hash('sha512', $password, false));

		$dao = new DAO();
		if ($dao->openConnection() === false) return false;

		$stmt = $dao->queryOpen("select count(*) from Gebruiker where Gebruikersnaam = '$username' and Wachtwoord = '$password'");
		if ($stmt === false) return false;

		$fetchResult = $dao->fetchNextRow($stmt);
		if ($fetchResult !== true)
		{
			$dao->queryClose($stmt);
			$dao->closeConnection();
			return false;
		}

		$value = $dao->getField($stmt, 0);

		$dao->queryClose($stmt);
		$dao->closeConnection();
		unset($dao);

		if ($value === false) return false;
		if ($value === 1) return true;
		return null;
	}

	/**
	 * Logt een gebruiker uit.
	 *
	 * @return bool <code>true</code> bij succes,
	 * <code>null</code> als er de gebruiker niet ingelogd is, <code>false</code> bij een fout.
	 */
	public static function logout()
	{
		$loggedIn = Gebruiker::loggedIn();
		if ($loggedIn !== true) return $loggedIn;
		unset($loggedIn);

		unset($_SESSION['username']);
		unset($_SESSION['password']);
		if (array_key_exists('username', $_COOKIE)) setcookie('username', null, 0);
		if (array_key_exists('password', $_COOKIE)) setcookie('password', null, 0);

		return true;
	}

	/**
	 * Kijkt of een gebruiker ingelogd is.
	 *
	 * @return bool|null <code>true</code> als een gebruiker ingelogd,
	 * <code>null</code> als deze niet ingelogd is, <code>false</code> bij een fout.
	 */
	public static function loggedIn()
	{
		if (!array_key_exists('username', $_SESSION) || !array_key_exists('password', $_SESSION)) return null;
		return Gebruiker::isLoginValid($_SESSION['username'], $_SESSION['password']);
	}

	/**
	 *  Retourneert de geheime vraag van de gebruiker.
	 *
	 * @param $username String gebruikersnaam
	 * @return bool|String|null Retourneert de geheime vraag van $username, <code>false</code> Als $username geen string
	 * is OF er een databasefout is, <code>null</code> als de gebruikersnaam $username niet bestaat OF er geen
	 * resultaten zijn van de query.
	 */
	public static function getQuestion($username)
	{
		if (is_string($username) === false) return false;
		if (!Gebruiker::usernameInUse($username)) return null;

		$dao = new DAO();
		if ($dao->openConnection() === false) return false;

		$stmt = $dao->queryOpen("Select v.Vraag FROM Vraag v INNER JOIN Gebruiker g ON g.Gebruikersnaam =
		 '$username' AND g.vraagnummer = v.vraagnummer");
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
		$field = $dao->getField($stmt, 0, SQLSRV_PHPTYPE_STRING("UTF-8"));
		$dao->queryClose($stmt);
		$dao->closeConnection();
		return $field;
	}

	/**
	 *  Retourneert het antwoord van de geheime vraag van de gebruiker.
	 *
	 * @param $username String gebruikersnaam
	 * @return bool|String|null Retourneert het antwoord van de geheime antwoord van $username, <code>false</code> Als $username geen string
	 * is OF er een databasefout is, <code>null</code> als de gebruikersnaam $username niet bestaat OF er geen
	 * resultaten zijn van de query.
	 */
	public static function getHiddenAnswer($username)
	{
		if (is_string($username) === false) return false;
		if (!Gebruiker::usernameInUse($username)) return null;

		$dao = new DAO();
		if ($dao->openConnection() === false) return false;

		$stmt = $dao->queryOpen("Select g.Antwoord FROM Gebruiker g WHERE g.Gebruikersnaam = '$username'");
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
		$field = $dao->getField($stmt, 0, SQLSRV_PHPTYPE_STRING("UTF-8"));
		$dao->queryClose($stmt);
		$dao->closeConnection();
		return $field;
	}

	/**
	 * Verander de waarde van een veld in de database van een gebruiker.
	 * Gebruik om het wachtwoord te veranderen {@link changePassword}.
	 *
	 * @param $username String Gebruikersnaam.
	 * @param $field String Naam van het veld. <i>Hoofdlettergevoelig!</i>
	 * @param $value mixed Waarde van het veld.
	 * @return bool|null <code>true</code> bij succes,
	 * <code>null</code> bij een ongeldige gebruiker, <code>false</code> bij een fout.
	 */
	public static function changeField($username, $field, $value)
	{
		if (!is_string($username) || !is_string($field)) return false;
		$userCheck = Gebruiker::usernameInUse($username);
		if ($userCheck === false) return false;
		if ($userCheck === null) return null;
		unset($userCheck);

		$dao = new DAO();
		$dao->openConnection();

		$result = $dao->query("update Gebruiker set $field = '$value' where Gebruikersnaam = '$username'");

		$dao->closeConnection();
		unset($dao);

		return $result;
	}

	/**
	 * Verander het wachtwoord van een gebruiker.
	 * Gebruiker voor andere velden {@link changeField}.
	 *
	 * @param $username String Gebruikersnaam.
	 * @param $newPassword String Het nieuwe wachtwoord (niet ge-hash'd, dat doet de functie).
	 * @return bool|null <code>true</code> bij succes,
	 * <code>null</code> bij een ongeldige gebruiker, <code>false</code> bij een fout.
	 */
	public static function changePassword($username, $newPassword)
	{
		if (!is_string($username) || !is_string($newPassword)) return false;
		if (strlen($newPassword) < 6 || strlen($newPassword) > 128) return false;
		if (!Gebruiker::usernameInUse($username)) return null;

		$hashedPass = strtolower(hash('sha512', $newPassword, false));

		$dao = new DAO();
		if ($dao->openConnection() === false) return false;
		if ($dao->query("UPDATE Gebruiker SET Wachtwoord  = '$hashedPass' WHERE Gebruiker.Gebruikersnaam = '$username'") === false)
		{
			$dao->closeConnection();
			return false;
		}
		$dao->closeConnection();
		return true;
	}

	/**
	 * @param $username String Gebruikersnaam.
	 * @param $amount null|int Hoeveel nummers om terug te sturen, <code>null</code> voor alles.
	 * @return bool|array Geindexeerde array met in iedere slot een associatieve array met gegevens,
	 * <code>null</code> bij een niet bestaande gebruiker of een gebruiker zonder telefoonnummer,
	 * <code>false</code> bij een fout.
	 */
	public static function getPhoneNumbers($username, $amount = null)
	{
		if (!is_string($username)) return false;
		if (isset($amount) && !is_int($amount)) return false;
		$userCheck = Gebruiker::usernameInUse($username);
		if ($userCheck === false) return false;
		if ($userCheck === null) return null;
		unset($userCheck);

		$dao = new DAO();
		if ($dao->openConnection() === false) return false;

		if (isset($amount))
			$sql = "select top($amount) Volgnummer, Telefoonnummer from Gebruikerstelefoon where Gebruikersnaam = '$username' order by Volgnummer asc";
		else $sql = "select Volgnummer, Telefoonnummer from Gebruikerstelefoon where Gebruikersnaam = '$username' order by Volgnummer asc";

		$stmt = $dao->queryOpen($sql);
		unset($sql);
		if ($stmt === false)
		{
			$dao->closeConnection();
			return false;
		}

		$index = 0;
		$result = array();
		while ($row = $dao->fetchNextRowArrayAssoc($stmt))
		{
			$result[$index] = $row;
			$index++;
		}
		if ($row === false)
		{
			$dao->queryClose($stmt);
			$dao->closeConnection();
			return false;
		}

		$dao->closeConnection();
		unset($dao);

		if ($index === 0) return null;
		unset($index);

		return $result;
	}

	public static function setSellerStatus($user, $ccOrBank = true, $verification, $cc = "", $bank = "", $bankrekening = "")
	{
		if (Gebruiker::usernameInUse($user) === false) return false;
		$verification = "post"; //Database accepteert alleen post en creditcard!
		$insertVerkoperQuery = "INSERT INTO Verkoper VALUES ('$user', ";
		if ($ccOrBank)
		{
			$insertVerkoperQuery .= "'nvt', 'nvt', '$verification', '$cc'";
		}
		else
		{
			$insertVerkoperQuery .= "'$bank', '$bankrekening', '$verification', 'nvt'";
		}
		$insertVerkoperQuery .= ")";

		$dao = new DAO();
		if ($dao->openConnection() === false)
		{
			$dao->closeConnection();
			return false;
		}
		$setIsVerkoperTrueQuery = "UPDATE Gebruiker SET IsVerkoper = 1 WHERE Gebruikersnaam = '$user'";
		return $dao->query($insertVerkoperQuery) && $dao->query($setIsVerkoperTrueQuery);
	}
}
