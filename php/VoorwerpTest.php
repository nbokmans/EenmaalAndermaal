<?php
require_once('Voorwerp.php');

class VoorwerpTest extends PHPUnit_Framework_TestCase
{
	public function testRubrieken()
	{
		/* Zoeken wordt alleen op foute parameter getest omdat het heel erg databaseafhankelijk is. */

		/* Test echte rubriek met voorwerpen. */
		$result = Voorwerp::getVoorwerpInRubriek(8780, 10);
		$this->assertTrue($result !== false && $result !== null);
		unset($result);

		/* Alle rubrieken. */
		$result = Voorwerp::getVoorwerpInRubriek(null, 10);
		$this->assertTrue($result !== false && $result !== null);
		unset($result);

		/* Foutive parameters. */
		$this->assertFalse(Voorwerp::getVoorwerpInRubriek(8780, true));
		$this->assertFalse(Voorwerp::getVoorwerpInRubriek(8780, -12));
		$this->assertFalse(Voorwerp::getVoorwerpInRubriek(8780, 0));
		$this->assertFalse(Voorwerp::getVoorwerpInRubriek(8780, null));
		$this->assertFalse(Voorwerp::getVoorwerpInRubriek(false, 10));
		$this->assertFalse(Voorwerp::getVoorwerpInRubriek(-126, 10));
		$this->assertFalse(Voorwerp::getVoorwerpInRubriek(8780, 10, false));
		$this->assertFalse(Voorwerp::getVoorwerpInRubriek(8780, 10, -1257));
		$this->assertFalse(Voorwerp::getVoorwerpInRubriek(8780, 10, 0));
		$this->assertFalse(Voorwerp::getVoorwerpInRubriek(8780, 10, 8126));
	}

	public function testGetHighestBid()
	{
		/* Controleert parameterinvoer. */
		$this->assertFalse(Voorwerp::getHighestBid(-1));
		$this->assertFalse(Voorwerp::getHighestBid(null));
		$this->assertFalse(Voorwerp::getHighestBid(true));
		$this->assertFalse(Voorwerp::getHighestBid("a"));
	}

	public function testGetImageLocation()
	{
		/* Controleert parameterinvoer. */
		$this->assertFalse(Voorwerp::getImageLocation(-1));
		$this->assertFalse(Voorwerp::getImageLocation(null));
		$this->assertFalse(Voorwerp::getImageLocation(true));
		$this->assertFalse(Voorwerp::getImageLocation("a"));

		/* Controleert of voorbeeld productId juiste locatie retourneert. */
		$this->assertEquals("dt_1_110712611137.jpg", Voorwerp::getImageLocation(110712611137));
	}

	public function testGetField()
	{
		/* Controleert parameterinvoer. */
		$this->assertFalse(Voorwerp::getField("ditveldbestaathelemaalniet", 0));
		$this->assertFalse(Voorwerp::getField(null, 0));
		$this->assertFalse(Voorwerp::getField(true, 0));
		$this->assertFalse(Voorwerp::getField(1, 0));
		$this->assertFalse(Voorwerp::getField(3.141592654, 0));

		$this->assertFalse(Voorwerp::getField("titel", -1));
		$this->assertFalse(Voorwerp::getField("titel", null));
		$this->assertFalse(Voorwerp::getField("titel", 3.141592654));
		$this->assertFalse(Voorwerp::getField("titel", true));
		$this->assertFalse(Voorwerp::getField("titel", "test"));

		/* Controleert of voorbeeld productId en veldnaam juiste veldwaarde retourneert. */
		$this->assertEquals("DISNEY TOPOLINO SET DONALD PHANTOMIAS M. BOOT  ! TOP !", Voorwerp::getField("titel", 110712611137));
	}

	public function testIsValidProductId()
	{
		/* Controleert parameterinvoer */
		$this->assertFalse(Voorwerp::isValidProductId(true));
		$this->assertFalse(Voorwerp::isValidProductId(null));
		$this->assertFalse(Voorwerp::isValidProductId("ditisgeenproductid"));

		/* Controleert uitvoerwaarden. */
		$this->assertFalse(Voorwerp::isValidProductId(0));
		$this->assertTrue(Voorwerp::isValidProductId(110712611137));
	}

	public function testGetTopBids()
	{
		/* Controleert parameterinvoer */
		$this->assertFalse(Voorwerp::getTopBids(-1, 1));
		$this->assertFalse(Voorwerp::getTopBids(null, 1));
		$this->assertFalse(Voorwerp::getTopBids(false, 1));
		$this->assertFalse(Voorwerp::getTopBids("ey", 1));
		$this->assertFalse(Voorwerp::getTopBids(1, null));
		$this->assertFalse(Voorwerp::getTopBids(1, true));
		$this->assertFalse(Voorwerp::getTopBids(1, "y"));
		$this->assertFalse(Voorwerp::getTopBids(1, 3.141592654));

		/* Controleert of uitvoer null is als er geen aantal wordt opgegeven. */
		$this->assertNull(Voorwerp::getTopBids(110712611137, 0));
	}

	public function testIsAuctionClosed()
	{
		/* Controleert parameterinvoer. */
		$this->assertFalse(Voorwerp::getAuctionStatus(-1));
		$this->assertFalse(Voorwerp::getAuctionStatus(null));
		$this->assertFalse(Voorwerp::getAuctionStatus(true));
		$this->assertFalse(Voorwerp::getAuctionStatus("x"));

		/* Controleert uitvoerwaarde. */
		$this->assertEquals("Deze aanbieding is al verlopen!", Voorwerp::getAuctionStatus(400470453451));
	}
}
 