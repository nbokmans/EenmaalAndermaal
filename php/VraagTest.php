<?php
require_once('Vraag.php');

class VraagTest extends PHPUnit_Framework_TestCase
{
	public function testGetSecretQuestions()
	{
		/* Ik controleer niet op database fouten want dat wordt in de DAO test gecontroleerd.*/
		$this->assertTrue(Vraag::getSecretQuestions() !== false);
	}
}
