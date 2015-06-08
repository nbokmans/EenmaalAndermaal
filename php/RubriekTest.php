<?php
require_once('Rubriek.php');

class RubriekTest extends PHPUnit_Framework_TestCase
{
	public function testGetRubriekName()
	{
		/* Test of er iets terug gegeven wordt */
		$this->assertNotNull(Rubriek::getRubriekname());
	}

	public function testgetSubRubriek()
	{
		/* Vraagt subrubrieken van een bestaande runbriek op.  */
		$this->assertNotNull(Rubriek::getSubRubriekName('1'));

		/* Vraagt subrubrieken van een niet-bestaande gebruiker op.  */
		$this->assertNull(Rubriek::getSubRubriekName(''));
	}
}
 