<?php
require_once('DAO.php');

class Bestand
{
	/**
	 * Voeg een afbeelding van een product toe aan de database.
	 *
	 * @param $productnumber float Productnummer.
	 * @param $filename String Bestandsnaam (zonder upload/products/).
	 * @return bool <code>true</code> bij succes, anders <code>false</code>.
	 */
	public static function addFile($productnumber, $filename)
	{
		if (!is_float($productnumber) || !is_string($filename)) return false;

		$dao = new DAO();
		$dao->openConnection();

		$result = $dao->query("insert into Bestand (Voorwerp, Bestandsnaam) values
		                       ($productnumber, '$filename')");

		$dao->closeConnection();

		return $result;
	}
}
