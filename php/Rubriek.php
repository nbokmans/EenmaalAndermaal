<?php
require_once('DAO.php');

class Rubriek
{
	/**
	 * Haalt de rubrieknamen uit de database
	 * @return array|bool|null Geeft een array met rubrieknamen mee, <code>false</code> bij fout.
	 */
	public static function getRubriekname()
	{
		$dao = new DAO();
		if ($dao->openConnection() === false) return false;

		$stmt = $dao->queryOpen("SELECT Rubrieknaam, Rubrieknummer
								 FROM rubriek
								 WHERE OuderRubrieknummer = '1' ORDER BY Rubrieknaam ASC");

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
			$objects[$index] = array(
					'Rubrieknaam' => $row['Rubrieknaam'],
					'Rubrieknummer' => $row['Rubrieknummer']
			);
			$index++;
		}
		$dao->queryClose($stmt);
		$dao->closeConnection();

		unset($dao);

		if ($index === 0) return null;

		return $objects;

	}

	/**
	 * @param $rubrieknumber Interger rubrieknummer.
	 * @return array|bool|null Geeft een array met de subrubrieken van een bepaalde rubriek mee, <code>false</code> bij fout.
	 */
	public static function getSubRubriekName($rubrieknumber)
	{
		$dao = new DAO();
		if ($dao->openConnection() === false) return false;

		$stmt = $dao->queryOpen("SELECT Rubrieknaam, rubrieknummer
								 FROM rubriek
								 WHERE OuderRubrieknummer = '$rubrieknumber' ORDER BY Rubrieknaam ASC");

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
			$objects[$index] = array(
					'Rubrieknaam' => $row['Rubrieknaam'],
					'rubrieknummer' => $row['rubrieknummer']
			);
			$index++;
		}
		$dao->queryClose($stmt);
		$dao->closeConnection();

		unset($dao);

		if ($index === 0) return null;

		return $objects;
	}


}

?>