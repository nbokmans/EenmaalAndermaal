<?php
require_once('DAO.php');
require_once('Gebruiker.php');

class Voorwerp
{
	/**
	 * Krijg alle voorwerpen uit gegeven rubrieknummer en uit eventuele kinderrubrieken.
	 *
	 * @param $rubrieknummer int [optional] Rubrieknummer.
	 * @param $amount int Hoeveelheid resultaten.
	 * @param $search String [optional] Zoekterm.
	 * @return array|null|bool Geïndexeerde array met per slot een voorwerp in een associatieve array bij succes.
	 * <code>null</code> als er geen voorwerpen zijn en <code>false</code> bij een fout.
	 */
	public static function getVoorwerpInRubriek($rubrieknummer = null, $amount, $search = null)
	{
		if ($rubrieknummer === 0) $rubrieknummer = null;

		if (($rubrieknummer !== null && (!is_int($rubrieknummer) || $rubrieknummer < 0)) || !is_int($amount) || $amount <= 0) return false;
		if (isset($search) && !is_string($search)) return false;

		$dao = new DAO();
		if ($dao->openConnection() === false) return false;

		$sql = "SELECT TOP($amount) Voorwerp.voorwerpnummer, titel
				 FROM VoorwerpInRubriek
				 INNER JOIN Voorwerp
				 ON voorwerpinrubriek.voorwerpnummer = Voorwerp.voorwerpnummer
				 INNER JOIN Rubriek
				 ON voorwerpinrubriek.rubriek = rubriek.Rubrieknummer";

		if (isset($rubrieknummer) || isset($search)) $sql .= ' WHERE ';

		if (isset($rubrieknummer))
			$sql .= "(rubrieknummer = '$rubrieknummer' OR ouderrubrieknummer = '$rubrieknummer' OR ouderrubrieknummer
			IN (SELECT Rubrieknummer FROM Rubriek WHERE ouderrubrieknummer = '$rubrieknummer'))";

		if (isset($search))
		{
			/* Controleer array voordat er mee gewerkt wordt. */
			$validSearch = false;
			$searchArray = explode(' ', trim($search));
			foreach ($searchArray as $element)
			{
				if (strlen($element) >= 3)
				{
					/* Als je min lengte woord aanpast pas dan ook aan in searchBar.php en hieronder. */
					$validSearch = true;
					break;
				}
			}

			/* Vul search in als deze geldig is. */
			if ($validSearch === true)
			{
				if (isset($rubrieknummer)) $sql .= ' and ';
				$sql .= '(';
				$firstAdd = true;

				foreach ($searchArray as $element)
				{
					/* Als je min lengte woord aanpast pas dan ook aan in searchBar.php en hierboven. */
					if (strlen($element) >= 3)
					{
						if ($firstAdd === true) $firstAdd = false;
						else $sql .= ' and ';
						$sql .= "Voorwerp.Titel like '%" . $element . "%'";
					}
				}

				$sql .= ')';
				unset($searchArray);
				unset($firstAdd);
			}
			unset($validSearch);
		}

		$sql .= " order by Voorwerp.voorwerpnummer desc";

		$stmt = $dao->queryOpen($sql);
		unset($sql);
		if ($stmt === false)
		{
			$dao->closeConnection();
			return false;
		}

		$objects = array();
		$index = 0;
		while ($row = $dao->fetchNextRowArrayAssoc($stmt))
		{
			if ($row === false)
			{
				$dao->queryClose($stmt);
				$dao->closeConnection();
				return false;
			}
			$objects[$index] = $row;
			$index++;
		}
		$dao->queryClose($stmt);
		$dao->closeConnection();

		unset($dao);

		if ($index === 0) return null;

		return $objects;
	}

	/**
	 * Retourneert de locatie van de afbeelding van een product
	 *
	 * @param $productNumber float Het productnummer
	 * @return bool|mixed|null <code>false</code> bij ongeldige parameters en database fouten, <code>mixed</code> bij
	 * succes en <code>null</code> bij serverfouten.
	 */
	public static function getImageLocation($productNumber)
	{
		if (is_float($productNumber) === false) return false;
		if (Voorwerp::isValidProductId($productNumber) === false) return false;

		$dao = new DAO();
		if ($dao->openConnection() === false)
		{
			$dao->closeConnection();
			return null;
		}
		$stmt = $dao->queryOpen("Select DISTINCT TOP(1) Bestand.Bestandsnaam FROM Bestand WHERE Bestand.Voorwerp = $productNumber");
		if ($stmt === false)
		{
			$dao->queryClose($stmt);
			$dao->closeConnection();
			return false;
		}
		$row = $dao->fetchNextRow($stmt);
		if ($row === null)
		{
			$dao->queryClose($stmt);
			$dao->closeConnection();
			return 'unavailable.jpg';
		}
		if ($row === false)
		{
			$dao->queryClose($stmt);
			$dao->closeConnection();
			return false;
		}
		$field = $dao->getField($stmt, 0, SQLSRV_PHPTYPE_STRING("UTF-8"));
		$dao->queryClose($stmt);
		$dao->closeConnection();
		return $field;
	}

	public static function getImages($productId)
	{
		if (is_float($productId) === false) return false;
		if (Voorwerp::isValidProductId($productId) === false) return false;

		$dao = new DAO();
		if ($dao->openConnection() === false)
		{
			$dao->closeConnection();
			return false;
		}
		$stmt = $dao->queryOpen("Select Bestand.Bestandsnaam FROM Bestand WHERE Bestand.Voorwerp = $productId");
		if ($stmt === false)
		{
			$dao->closeConnection();
			return false;
		}
		$objects = array();
		$index = 0;
		while ($row = $dao->fetchNextRowArrayAssoc($stmt))
		{
			if ($row === false)
			{
				$dao->queryClose($stmt);
				$dao->closeConnection();
				return false;
			}
			$objects[$index] = $row;
			$index++;
		}
		if ($index === 0) return null;
		return $objects;
	}

	/**
	 * Checkt of het meegegeven voorwerp ID bestaat.
	 *
	 * @param $productId float Product ID
	 * @return bool|String <code>true</code> als het product bestaat,
	 * <code>null</code> als deze niet bestaat, <code>false</code> bij een fout.
	 */
	public static function isValidProductId($productId)
	{
		if (is_float($productId) === false) return false;

		$dao = new DAO();
		if ($dao->OpenConnection() === false) return false;

		$stmt = $dao->QueryOpen("select count(*) from Voorwerp where Voorwerpnummer = '$productId'");
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
	 * Voegt een bod toe aan een veiling.
	 */

	public static function addBidToAuction($productId, $bid)
	{
		if (is_float($productId) === false) return false;
		if (is_float($bid) === false) return false;
		if (Voorwerp::isValidProductId($productId) === false) return false;
		if (Voorwerp::isValidBid($productId, $bid) === false) return false;
		$dao = new DAO();
		if ($dao->openConnection() === false)
		{
			$dao->closeConnection();
			return false;
		}

		$dateTime = explode(" ", date("Y-m-d H:i:s", strtotime("now")));
		$user = $_SESSION['username'];
		return $dao->query("INSERT INTO Bod VALUES ('$productId', '$bid', '$user', '$dateTime[0]', '$dateTime[1]') ");
	}

	/**
	 *  Controleert of een bod geldig is.
	 */
	public static function isValidBid($productId, $bid)
	{
		if (is_float($productId) === false) return 'false1';
		if (is_float($bid) === false) return 'false2';
		if (Voorwerp::isValidProductId($productId) === false) return 'false3';

		/* Controleer of het bod hoger is dan het vorige hoogste bod. */
		return Voorwerp::getTopBids($productId, 1) === null ? $bid >= Voorwerp::getField('Startprijs', $productId) :
				(Voorwerp::getHighestBid((float)$productId) * 1.05) <= $bid;
	}

	/**
	 * Retourneert de top biedingen.
	 *
	 * @param $voorwerpId float Het productnummer.
	 * @param $count int Het aantal biedingen om op te halen.
	 * @return array|bool|null <code>false</code> bij ongeldige parameters en database fouten, <code>mixed</code> bij
	 * succes en <code>null</code> bij serverfouten.
	 */
	public static function getTopBids($voorwerpId, $count)
	{
		if (is_float($voorwerpId) === false) return false;
		if (is_int($count) === false) return false;

		$dao = new DAO();
		if ($dao->openConnection() === false)
		{
			$dao->closeConnection();
			return false;
		}
		$stmt = $dao->queryOpen("SELECT TOP($count) Bod.Bodbedrag, Bod.Gebruiker FROM Bod WHERE Voorwerp = $voorwerpId ORDER BY Bodbedrag DESC");
		if ($stmt === false)
		{
			$dao->closeConnection();
			return false;
		}
		$objects = array();
		$index = 0;
		while ($row = $dao->fetchNextRowArrayAssoc($stmt))
		{
			if ($row === false)
			{
				$dao->queryClose($stmt);
				$dao->closeConnection();
				return false;
			}
			$objects[$index] = $row;
			$index++;
		}
		if ($index === 0) return null;
		return $objects;
	}

	/**
	 * Retourneert de waarde van een veld.
	 *
	 * @param $veldNaam String De naam van het op te vragen veld.
	 * @param $productId float Het productnummer.
	 * @return bool|mixed  <code>false</code> bij ongeldige parameters en database fouten, <code>mixed</code> bij
	 * succes.
	 */
	public static function getField($veldNaam, $productId)
	{
		if (is_float($productId) === false || is_string($veldNaam) === false) return false;
		if (Voorwerp::isValidProductId($productId) === false) return false;
		$dao = new DAO();

		if ($dao->openConnection() === false) return false;
		$stmt = $dao->queryOpen("SELECT $veldNaam FROM Voorwerp WHERE Voorwerpnummer = $productId");
		if ($stmt === false)
		{
			$dao->closeConnection();
			return false;
		}
		$row = $dao->fetchNextRow($stmt);
		if ($row === null || $row === false)
		{
			$dao->queryClose($stmt);
			$dao->closeConnection();
			return false;
		}
		$field = $dao->getField($stmt, 0, SQLSRV_PHPTYPE_STRING("UTF-8"));
		$dao->queryClose($stmt);
		$dao->closeConnection();
		return $field;
	}

	/**
	 * Retourneert het hoogste bod.
	 *
	 * @param $voorwerpId float Het voorwerpId om het hoogste bod voor op te halen.
	 * @return bool|mixed|null <code>false</code> bij ongeldige parameters en database fouten,
	 * de waarde van het bod of de startprijs als er geen bod is bij succes, <code>null</code> bij serverfouten.
	 */
	public static function getHighestBid($voorwerpId)
	{
		if (is_float($voorwerpId) === false) return false;
		if (Voorwerp::isValidProductId($voorwerpId) === false) return false;

		$dao = new DAO();
		if ($dao->openConnection() === false)
		{
			$dao->closeConnection();
			return false;
		}
		$stmt = $dao->queryOpen("SELECT TOP(1) Bod.Bodbedrag FROM Bod WHERE Bod.Voorwerp = '$voorwerpId' ORDER BY Bod.Bodbedrag DESC");
		if ($stmt === false)
		{
			$dao->closeConnection();
			return false;
		}
		$row = $dao->fetchNextRow($stmt);
		if ($row === null)
		{
			$dao->queryClose($stmt);
			$dao->closeConnection();
			/* Nog geen bod, return de startprijs. */
			return Voorwerp::getField('Startprijs', $voorwerpId);
		}
		if ($row === false)
		{
			$dao->queryClose($stmt);
			$dao->closeConnection();
			return false;
		}
		$field = $dao->getField($stmt, 0, SQLSRV_PHPTYPE_STRING("UTF-8"));
		$dao->queryClose($stmt);
		$dao->closeConnection();
		return $field;

	}

	public static function getAuctionEndDate($productId)
	{
		if (is_float($productId) === false) return false;
		if (Voorwerp::isValidProductId($productId) === false) return false;

		$endDate = Voorwerp::getField("LooptijdBeginDag", $productId);
		$runningTime = Voorwerp::getField("Looptijd", $productId);

		$end = date("Y-m-d", strtotime('+' . $runningTime . ' days', strtotime($endDate)));

		return $end;
	}

	public static function getAuctionEndTime($productId)
	{
		if (is_float($productId) === false) return false;
		if (Voorwerp::isValidProductId($productId) === false) return false;

		$endTime = Voorwerp::getField("LooptijdBeginTijdstip", $productId);
		return $endTime;
	}

	/**
	 * Controleert of een productId als verkoper username heeft.
	 */
	public static function confirmAuctionOwner($username, $pid)
	{
		if (is_string($username) === false) return false;
		if (is_float($pid) === false) return false;
		if (Gebruiker::usernameInUse($username) === false) return false;
		if (Voorwerp::isValidProductId($pid) === false) return false;

		$dao = new DAO();
		if ($dao->openConnection() === false)
		{
			$dao->closeConnection();
			return false;
		}

		$seller = Voorwerp::getField('Verkoper', $pid);
		return ($seller === $username);
	}

	/**
	 * Controleert of opgegeven username de veiling heeft 'gewonnen' om valse feedback te voorkomen.
	 */
	public static function checkAuctionWinner($user, $productId)
	{
		if (is_string($user) === false) return false;
		if (is_float($productId) === false) return false;
		if (Gebruiker::usernameInUse($user) === false) return false;
		if (Voorwerp::isValidProductId($productId) === false) return false;

		$dao = new DAO();
		if ($dao->openConnection() === false)
		{
			$dao->closeConnection();
			return false;
		}

		$stmt = $dao->queryOpen("SELECT TOP(1) b.Gebruiker FROM Bod b WHERE b.Voorwerp = '$productId' ORDER BY b.Bodbedrag DESC");
		if ($stmt === false)
		{
			$dao->closeConnection();
			return false;
		}
		$row = $dao->fetchNextRow($stmt);
		if ($row === null || $row === false)
		{
			$dao->queryClose($stmt);
			$dao->closeConnection();
			return false;
		}
		$field = $dao->getField($stmt, 0, SQLSRV_PHPTYPE_STRING("UTF-8"));
		$dao->queryClose($stmt);
		$dao->closeConnection();
		return $field === $user && Voorwerp::getAuctionStatus($productId) === "Deze aanbieding is al verlopen!";
	}

	/**
	 * Retourneer de huidige status van de veiling in een string.
	 *
	 * @param $productId float Het productnummer.
	 * @return bool|string <code>false</code> bij ongeldig productId,
	 * anders de resterende tijd of "Deze aanbieding is al verlopen!"
	 */
	public static function getAuctionStatus($productId)
	{
		if (is_float($productId) === false) return false;
		if (Voorwerp::isValidProductId($productId) === false) return false;

		$startDate = Voorwerp::getField("LooptijdBeginDag", $productId);
		$startTime = Voorwerp::getField("LooptijdBeginTijdstip", $productId);
		$runningTime = Voorwerp::getField("Looptijd", $productId);
		$time = $startDate . " " . $startTime;
		$end = date("Y-m-d H:i:s", strtotime('+' . $runningTime . ' days', strtotime($time)));
		$startDateTime = new DateTime("now");
		$endDateTime = new DateTime($end);
		$interval = date_diff($endDateTime, $startDateTime, true);
		return strtotime("now") > $endDateTime->getTimestamp() ? "Deze aanbieding is al verlopen!" : $interval->
		format('%d dag(en), %h uur, %i minuten en %s seconden.');
	}

	public static function getPopularProducts()
	{
		$dao = new DAO();
		if ($dao->openConnection() === false) return false;

		$stmt = $dao->queryOpen("select Voorwerp.Voorwerpnummer, Voorwerp.Titel from Voorwerp
		                         where Voorwerp.Voorwerpnummer in (
		                         select top(30) Bod.Voorwerp from Bod
		                         inner join Voorwerp on Voorwerp.Voorwerpnummer = Bod.Voorwerp
		                         inner join Bestand on Voorwerp.Voorwerpnummer = Bestand.Voorwerp
		                         where Voorwerp.IsVeilingGesloten = '0'
		                         group by Bod.Voorwerp order by count(Bod.Voorwerp) desc)");
		if ($stmt === false)
		{
			$dao->closeConnection();
			return false;
		}

		$result = array();
		$index = 0;
		while ($row = $dao->fetchNextRowArrayAssoc($stmt))
		{
			$result[$index] = $row;
			$index++;
		}

		$dao->queryClose($stmt);
		$dao->closeConnection();
		unset($dao);

		if ($row === false) return false;
		if ($index === 0) return null;
		return $result;
	}

	public static function addAuction($title, $desc, $startingPrice, $paymentType, $paymentInstructions, $runtime,
	                                  $shippingCosts, $shippingInstructions, $rId, $sRId)
	{
		/* Verify user input. */
		$errorMessage = "";

		// Titel checken.
		if (strlen($title) < 6 || strlen($title) > 256)
		{
			$errorMessage .= "De titel moet minstens 6 tekens en maximaal 256 tekens zijn!";
			$errorMessage .= "<br>";
		}

		// Description checken.
		if (strlen($desc) < 30 || strlen($desc) > 5000)
		{
			$errorMessage .= "De beschrijving moet minstens 30 tekens en maximaal 5000 tekens zijn!";
			$errorMessage .= "<br>";
		}

		// Startprijs checken
		$startingPrice = str_replace(',', '.', $startingPrice);
		if (Voorwerp::isFloat($startingPrice) === false)
		{
			$errorMessage .= "De startprijs moet een getal zijn!";
			$errorMessage .= "<br>";
		}
		$startingPrice = strpos($startingPrice, '.') !== false ? $startingPrice : $startingPrice .= '.00';
		if ($startingPrice < 1.00)
		{
			$errorMessage .= "De startprijs moet minstens &euro;1,00 zijn!";
			$errorMessage .= "<br>";
		}

		if (preg_match("/^[1-9](?:[0-9]*)[.][0-9][0-9]$/", (String)$startingPrice) === 0)
		{
			$errorMessage .= "De startprijs moet het volgende formaat aanhouden: [cijfer(s)],[cijfer(s)]!";
			$errorMessage .= "<br>";
		}
		if (strlen($startingPrice) > 10)
		{
			$errorMessage .= "De startprijs mag maximaal 10 tekens (incl. komma of punt) zijn!";
			$errorMessage .= "<br>";
		}

// Payment type checken
		if (!is_string($paymentType))
		{
			$errorMessage .= "Het betaaltype moet een of meerdere woorden zijn.";
			$errorMessage .= "<br>";
		}
		if (strlen($paymentType) < 2)
		{
			$errorMessage .= "Het betaaltype moet minstens 2 tekens lang zijn.";
			$errorMessage .= "l: " . strlen($paymentType);
			$errorMessage .= " - " . $paymentType;
			$errorMessage .= "<br>";
		}

// Payment instructies checken
		if (!is_string($paymentInstructions))
		{
			$errorMessage .= "De betaalinstructies moet een of meerdere woorden zijn.";
			$errorMessage .= "<br>";
		}

// Verzendmode checken
		if (!is_string($shippingInstructions))
		{
			$errorMessage .= "De verzendmethode moet een of meerdere woorden zijn.";
			$errorMessage .= "<br>";
		}
		if (strlen($shippingInstructions) < 2)
		{
			$errorMessage .= "De verzendmethode moet minstens 2 tekens lang zijn.";
			$errorMessage .= "<br>";
		}

// Verzendkosten checken
		$shippingCosts = str_replace(',', '.', $shippingCosts);
		if (!Voorwerp::isFloat($shippingCosts))
		{
			$errorMessage .= "De verzendkosten moeten een getal zijn!";
			$errorMessage .= "<br>";
		}
		$shippingCosts = strpos($shippingCosts, '.') !== false ? $shippingCosts : $shippingCosts .= '.00';
		if ($shippingCosts < 0.00)
		{
			$errorMessage .= "De verzendkosten moet minstens &euro;1,00 zijn!";
			$errorMessage .= "<br>";
		}
		if (preg_match("/^[0-9](?:[0-9]*)[.][0-9][0-9]$/", (String)$shippingCosts) === 0)
		{
			$errorMessage .= "De verzendkosten moet het volgende formaat aanhouden: [cijfer(s)],[cijfer(s)]!";
			$errorMessage .= "<br>";
		}
		if (strlen($shippingCosts) > 10)
		{
			$errorMessage .= "De verzendkosten mag maximaal 10 tekens (incl. komma of punt) zijn!";
			$errorMessage .= "<br>";
		}

		/* Replace ' with '' to escape the character in SQL server. */
		$title = str_replace('\'', '\'\'', $title);
		$desc = str_replace('\'', '\'\'', $desc);
		$paymentInstructions = str_replace('\'', '\'\'', $paymentInstructions);
		$shippingInstructions = str_replace('\'', '\'\'', $shippingInstructions);

		/* These variables don't require error checking. */
		$user = $_SESSION['username'];
		$userDetails = Gebruiker::getAccountDetails($user);
		$location = $userDetails['Adresregel1'];
		$country = $userDetails['Land'];
		$date = date('Y-m-d', strtotime('now'));
		$time = date('H:i:s', strtotime('now'));
		$endDate = '01-01-2015';
		$endTime = '0:00';

		$insertQuery = "INSERT INTO VOORWERP VALUES (";
		$insertQuery .= "'" . $title . "'";
		$insertQuery .= ", ";
		$insertQuery .= "'" . $desc . "'";
		$insertQuery .= ", ";
		$insertQuery .= "'" . $startingPrice . "'";
		$insertQuery .= ", ";
		$insertQuery .= "'" . $paymentType . "'";
		$insertQuery .= ", ";
		$insertQuery .= "'" . $paymentInstructions . "'";
		$insertQuery .= ", ";
		$insertQuery .= "'" . $location . "'";
		$insertQuery .= ", ";
		$insertQuery .= "'" . $country . "'";
		$insertQuery .= ", ";
		$insertQuery .= "'" . $runtime . "'";
		$insertQuery .= ", ";
		$insertQuery .= "'" . $date . "'";
		$insertQuery .= ", ";
		$insertQuery .= "'" . $time . "'";
		$insertQuery .= ", ";
		$insertQuery .= "'" . $shippingCosts . "'";
		$insertQuery .= ", ";
		$insertQuery .= "'" . $shippingInstructions . "'";
		$insertQuery .= ", ";
		$insertQuery .= "'" . $user . "'";
		$insertQuery .= ", ";
		$insertQuery .= 'null';
		$insertQuery .= ", ";
		$insertQuery .= "'" . $endDate . "'";
		$insertQuery .= ", ";
		$insertQuery .= "'" . $endTime . "'";
		$insertQuery .= ", ";
		$insertQuery .= '0';
		$insertQuery .= ", ";
		$insertQuery .= 'null';
		$insertQuery .= ")";

		if ($errorMessage === "")
		{
			$dao = new DAO();
			if ($dao->openConnection() === false)
			{
				$dao->closeConnection();
				return false;
			}
			if ($dao->query($insertQuery))
			{
				$pid = Voorwerp::getLatestPID();
				$rubriekId = $sRId == -1 ? $rId : $sRId;
				$rubriekQuery = $dao->query("INSERT INTO VoorwerpInRubriek VALUES ('$pid', '$rubriekId')");
				$dao->closeConnection();
				if ($rubriekQuery)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		return $errorMessage;
	}

	public function isFloat($f)
	{
		return ($f == (string)(float)$f);
	}

	public static function getLatestPID()
	{
		$dao = new DAO();
		if ($dao->openConnection() === false)
		{
			$dao->closeConnection();
			return false;
		}
		$stmt = $dao->queryOpen("SELECT TOP(1) Voorwerp.Voorwerpnummer FROM Voorwerp ORDER BY Voorwerp.Voorwerpnummer DESC");
		if ($stmt === false)
		{
			$dao->closeConnection();
			return false;
		}
		$row = $dao->fetchNextRow($stmt);
		if ($row === null || $row === false)
		{
			$dao->queryClose($stmt);
			$dao->closeConnection();
			return false;
		}
		$field = $dao->getField($stmt, 0, SQLSRV_PHPTYPE_STRING("UTF-8"));
		$dao->queryClose($stmt);
		$dao->closeConnection();
		return $field;
	}

	/**
	 * Sluit alle veilingen die verlopen zijn.
	 *
	 * @return bool <code>true</code> bij succes,
	 * <code>null</code> als er geen veilingen zijn, <code>false</code> bij databasefout.
	 */
	public static function closeEndedAuctions()
	{
		$dao = new DAO();
		$dao->openConnection();

		/*$stmtVoorwerpnummer = $dao->queryOpen("select Voorwerpnummer from Voorwerp where IsVeilingGesloten = '0'");
		if($stmtVoorwerpnummer === false)
		{
			$dao->closeConnection();
			return false;
		}

		$voorwerpnummersToBeClosed = array();
		$index = 0;
		while($rowVoorwerpnummer = $dao->fetchNextRow($stmtVoorwerpnummer))
		{
			$voorwerpnummer = $dao->getField($stmtVoorwerpnummer, 0, SQLSRV_PHPTYPE_INT);

			if (Voorwerp::getAuctionStatus((float)$voorwerpnummer) ===
				'Deze aanbieding is al verlopen!')
			{
				$voorwerpnummersToBeClosed[$index] = $voorwerpnummer;
				$index++;
			}
		}
		if ($rowVoorwerpnummer === false)
		{
			$dao->closeConnection();
			return false;
		}
		if ($index === 0)
		{
			$dao->closeConnection();
			return null;
		}*/

		$date = date('m-d-Y');
		$stmt = $dao->queryOpen("select Voorwerpnummer from voorwerp where IsVeilingGesloten = '0' and dateadd(d,Looptijd,LooptijdBeginDag) < '$date'");

		while ($row = $dao->fetchNextRowArrayIndex($stmt))
		{
			$closeNumber = $row[0];

			$verkoopArray = Voorwerp::getTopBids((float)$closeNumber, 1);
			if ($verkoopArray === false)
			{
				$verkoopArray = null;
			}

			if ($verkoopArray !== null)
			{
				$koper = $verkoopArray[0]['Gebruiker'];
				$verkoopPrijs = $verkoopArray[0]['Bodbedrag'];
			}
			else
			{
				$koper = null;
				$verkoopPrijs = null;
			}

			if ($dao->query("update Voorwerp set
			                 IsVeilingGesloten = '1' " .
							($koper === null ? "" : ",Koper = '$koper' ") .
							($verkoopPrijs === null ? "" : ",Verkoopprijs = $verkoopPrijs ") .
							"where Voorwerpnummer = $closeNumber") === false
			)
			{
				$dao->closeConnection();
				return false;
			}

			if ($koper !== null) Voorwerp::sendAuctionWonMail($koper, $closeNumber, $verkoopPrijs);
		}

		$dao->closeConnection();
		unset($dao);

		return true;
	}

	/**
	 * Stuur een mailtje dat de gebruiker een veiling gewonnen heeft.
	 *
	 * @param $buyer String koper.
	 * @param $productnumber float Productnummer.
	 * @param $price float Prijs.
	 *
	 * @return bool <code>true</code>
	 */
	public static function sendAuctionWonMail($buyer, $productnumber, $price)
	{
		$userDetails = Gebruiker::getAccountDetails($buyer);
		$email = $userDetails['Emailadres'];
		unset($userDetails);

		$headers = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'To: ' . $email . "\r\n";
		$headers .= 'From: noreply@eenmaalandermaal.nl' . "\r\n";
		$message = '
		<!DOCTYPE HTML>
		<html lang="nl" dir="ltr">
		<head>
			<title>EenmaalAndermaal - Veiling gewonnen</title>
		</head>
		<body>
			<div style="background-color:#EBEBEB; padding:25px;	height: 100%;">
				<div style="background-color:#FAFAFA;
					padding:20px;
					text-align:center;
					border:1px solid #eeeeee;
					border-radius:10px;
					margin:0 auto;
					width:700px;
				">
					<div>
						<h1 style="
							font-size:35px;
							color: #606060;
							font-family:Arial,sans-serif;
						">Eenmaal Andermaal</h1>
					</div>
					<img style="width: 330px;" src="http://iproject35.icasites.nl/logo.png" alt="EenmaalAndermaal Logo"/>
					<hr style="
						margin-top: 35px;
						margin-bottom: 25px;
						border: 1px solid #606060;
					">
					<div style="
							padding:5px;
							margin:0 auto;
							width:550px;
							text-align:left;
							color:#606060;

							font-size:14px;
							font-family:Arial,sans-serif;

						">
						<h2 style="font-size:18px;">U heeft een veiling gewonnen</h2>
						<div >
							<p><br>
								Geachte ' . $buyer . ',<br><br>
								Onlangs heeft u een bod geplaatst op een veiling met het artikelnummer "' . $productnumber . '".<br>
								U kunt de veiling bekijken op:<br>
									http://iproject35.icasites.nl/productpagina.php?productID=' . $productnumber .
								' <br><br>
								Uw bod bedraagt: "&euro;' . $price . '".
								<br><br>
								U kunt feedback geven op:
								 		http://iproject35.icasites.nl/voorwerpfeedback.php?user=' .
				Voorwerp::getField('Verkoper', (float)$productnumber) . '&productId=' . $productnumber . '
								<br>
								Met vriendelijke groet,<br>
								<br>
								EenmaalAndermaal Verkocht<br>
								http://iproject35.icasites.nl<br>
								info@iproject35.icasites.nl<br>

							</p>
						</div>
					</div>
				</div>
			</div><p>'
				. date("Y-m-d H:i:s") .
				'</p></body>
				</html>
				';
		return (mail($email, 'EenmaalAndermaal - Veiling gewonnen', $message, $headers));
	}
}
