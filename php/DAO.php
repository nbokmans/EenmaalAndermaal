<?php

/**
 * Een Data Access Object (DAO) verzorgt alle communicatie tussen PHP en de database.
 */
class DAO
{
	private $conn;

	/**
	 * Fetch de volgende row van de gegeven SQL resource.
	 *
	 * @param $stmt resource SQL resource.
	 * @return null|bool <code>true</code> bij succes, <code>null</code> als er geen rows meer zijn, <code>false</code> bij een fout.
	 */
	public static function fetchNextRow($stmt)
	{
		return sqlsrv_fetch($stmt);
	}

	/**
	 * Fetch de volgende row in associatieve array vorm van de gegeven SQL resource.
	 *
	 * @param $stmt resource SQL resource.
	 * @return array|null|bool Associatieve array met gegevens bij success, <code>null</code> als er geen rows meer zijn, <code>false</code> bij een fout.
	 */
	public static function fetchNextRowArrayAssoc($stmt)
	{
		return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
	}

	/**
	 * Fetch de volgende row in geïndexeerde array vorm met index van de gegeven SQL resource.
	 *
	 * @param $stmt resource SQL resource.
	 * @return array|null|bool Geïndexeerde array met gegevens bij success, <code>null</code> als er geen rows meer zijn, <code>false</code> bij een fout.
	 */
	public static function fetchNextRowArrayIndex($stmt)
	{
		return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC);
	}

	/**
	 * Geeft de waarde van het gegeven veld in de gegeven SQL resource terug.
	 *
	 * @param $stmt resource SQL resource, van een row.
	 * @param $index int [optional] Index van veld in huidige row wat opgevraagd wordt.
	 * @param $get_as_type int [optional] Een SQLSRV constante. <code>SQLSRV_PHPTYPE_*</code>.
	 * @return mixed Waarde van het veld in de index van de gegeven row, <code>false</code> bij een fout.
	 */
	public static function getField($stmt, $index = 0, $get_as_type = SQLSRV_PHPTYPE_INT)
	{
		return sqlsrv_get_field($stmt, $index, $get_as_type);
	}

	/**
	 * Open een connectie met de database. Als er parameters missen worden standaardwaardes gebruikt.
	 * Als er al een connectie open is wordt deze gesloten om vervolgens een nieuwe connectie aan te maken.
	 *
	 * @param $serverName String [optional] Naam of adres van de server.
	 * @param $database String [optional] Naam van de database.
	 * @param $username String [optional] Gebruikersnaam.
	 * @param $password String [optional] Wachtwoord.
	 * @return bool <code>true</code> bij succes, <code>false</code> bij fout.
	 */
	public function openConnection($serverName = 'mssql.iproject35.icasites.nl', $database = 'iproject35',
	                               $username = 'iproject35', $password = 'M84qPwG5')
	{
		/* Melvin database:
		public function openConnection($serverName = 'server.melvinvermeeren.com', $database = 'iproject35',
	                               $username = 'sa', $password = 'root')
		*/
		if (!isset($serverName)) return false;

		$connectionOptions = array
		(
				'Database' => $database,
				'UID'      => $username,
				'PWD'      => $password
		);

		if (isset($this->conn)) $this->closeConnection();

		$this->conn = sqlsrv_connect($serverName, $connectionOptions);
		if ($this->conn === false)
		{
			unset($this->conn);
			return false;
		}

		return true;
	}

	/**
	 * Sluit de connectie met de database.
	 *
	 * @return bool <code>true</code> bij succes of als de connectie al gesloten is, <code>false</code> bij fout.
	 */
	public function closeConnection()
	{
		if (!isset($this->conn)) return true;

		$return = sqlsrv_close($this->conn);
		unset($this->conn);

		return $return;
	}

	/**
	 * Loop een query en sluit deze meteen af.<br/>
	 *
	 * @param $sql String SQL query.
	 * @return bool <code>true</code> bij succes, <code>false</code> bij fout.
	 */
	function query($sql)
	{
		$stmt = $this->queryOpen($sql);
		if ($stmt === false) return false;

		if ($this->queryClose($stmt) === false) return false;

		return true;
	}

	/**
	 * Run een query op de huidige connectie.<br/>
	 *
	 * @param $sql String SQL query.
	 * @return resource|bool SQL resouce om gegevens uit te halen, bij falen <code>false</code>.
	 */
	public function queryOpen($sql)
	{
		if (!isset($this->conn)) return false;

		return sqlsrv_query($this->conn, $sql);
	}

	/**
	 * Sluit een query af.
	 *
	 * @param $stmt resource SQL query.
	 * @return bool <code>true</code> bij succes, <code>false</code> bij fout.
	 */
	public function queryClose(&$stmt)
	{
		$return = sqlsrv_free_stmt($stmt);
		unset($stmt);

		return $return;
	}
}
