<?php
require_once('DAO.php');

class DAOTest extends PHPUnit_Framework_TestCase
{
	public function testConnection()
	{
		$dao = new DAO();

		/* Standaard connectie openen en sluiten. */
		$this->assertTrue($dao->openConnection());
		$this->assertTrue($dao->closeConnection());

		/* Custom connectie openen en sluiten. */
		$this->assertTrue($dao->openConnection('mssql.iproject35.icasites.nl', 'iproject35', 'iproject35', 'M84qPwG5'));
		$this->assertTrue($dao->closeConnection());

		/* Custom connectie met foutieve parameters.
		   Een ongeldige host opgeven geeft een timeout (15 seconden) waardoor de test lang zal duren. */
		$this->assertFalse($dao->openConnection('mssql.iproject35.icasites.nl', 'error', 'fail', 'exception'));
		$this->assertFalse($dao->openConnection(null, 'error', 'fail', 'exception'));
	}

	/**
	 * @depends testConnection
	 */
	public function testQuery()
	{
		$dao = new DAO();
		$dao->openConnection();

		/* Maak een tabel, vul informatie in, violate de primary key. */
		$tableName = 'DAOTest_testQuery_' . hash('md5', time(), false);
		$this->assertTrue($dao->query("create table $tableName(testVar int not null primary key)"));
		$this->assertTrue($dao->query("insert into $tableName(testVar) values (50), (100), (150)"));
		$this->assertFalse($dao->query("insert into $tableName(testVar) values (100)"));

		/* Haal de data uit de tabel. */
		$stmt = $dao->queryOpen("select * from $tableName");
		$this->assertTrue($stmt !== false);

		$fetchResult = $dao->fetchNextRow($stmt);
		$this->assertTrue($fetchResult !== false && $fetchResult !== null);
		$this->assertEquals(50, $dao->getField($stmt, 0));
		unset($fetchResult);

		$row = $dao->fetchNextRowArrayAssoc($stmt);
		$this->assertTrue($row !== false && $row !== null);
		$this->assertEquals(100, $row['testVar']);
		unset($row);

		$row = $dao->fetchNextRowArrayIndex($stmt);
		$this->assertTrue($row !== false && $row !== null);
		$this->assertEquals(150, $row[0]);
		unset($row);

		$this->assertNull($dao->fetchNextRow($stmt));

		$this->assertTrue($dao->queryClose($stmt));

		/* Verwijder de tabel. */
		$this->assertTrue($dao->query("drop table $tableName"));
		unset($tableName);

		/* Geef een foute query mee aan custom functie. */
		$this->assertFalse($dao->queryOpen('error'));

		/* Fetchen met een ongeldige SQL resource kan niet, in de doc staat het datatype al op resource ingesteld. */
		// $this->assertFalse($dao->fetchNextRowArrayIndex(null));

		$dao->closeConnection();
	}
}
 