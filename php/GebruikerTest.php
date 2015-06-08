<?php
require_once('Gebruiker.php');

class GebruikerTest extends PHPUnit_Framework_TestCase
{
	/* Registreren wordt niet getest omdat het enigste wat deze functie doet het aanroepen van een insert query is.
	   Zia DAOTest voor database tests. */

	public function testEmailInUse()
	{
		/* Gebruikte email adressen. */
		$this->assertTrue(Gebruiker::emailInUse('mingels@gmail.com'));

		/* Ongebruikte geldige email adressen. */
		$this->assertNull(Gebruiker::emailInUse('gebruikertest_testemailinuse_validemail@eenmaalandermaal.nl'));

		/* Foutive email adressen. */
		$this->assertFalse(Gebruiker::emailInUse('error'));
		$this->assertFalse(Gebruiker::emailInUse(true));
		$this->assertFalse(Gebruiker::emailInUse(50));
		$this->assertFalse(Gebruiker::emailInUse(null));

		/* Foutive parameters. */
		$this->assertFalse(Gebruiker::emailInUse(null));
		$this->assertFalse(Gebruiker::emailInUse(true));
		$this->assertFalse(Gebruiker::emailInUse(50));
	}

	public function testPhoneNumber()
	{
		/* Haal telefoonnummer op. */
		$numberArray = Gebruiker::getPhoneNumbers('annie', 1);
		$this->assertEquals('0614254174', $numberArray[0]['Telefoonnummer']);
		unset($numberArray);

		/* Voeg geldige telefoonnummers toe. */
		$this->assertTrue(Gebruiker::addPhoneNumber('annie', '+311230166361'));
		$this->assertTrue(Gebruiker::addPhoneNumber('annie', '0620502011'));
		$this->assertTrue(Gebruiker::addPhoneNumber('annie', '716693760272', 50000));

		/* Voeg duplicate telefoonnummer toe en duplicate volgnummer toe. */
		$this->assertFalse(Gebruiker::addPhoneNumber('annie', '+311230166361'));
		$this->assertFalse(Gebruiker::addPhoneNumber('annie', '9276944742927', 50000));

		/* Verwijder telefoonnummers weer. */
		$this->assertTrue(Gebruiker::removePhoneNumber('annie', '+311230166361'));
		$this->assertTrue(Gebruiker::removePhoneNumber('annie', '0620502011'));
		$this->assertTrue(Gebruiker::removePhoneNumber('annie', '716693760272'));

		/* Gebruikers die niet bestaan. */
		$this->assertNull(Gebruiker::getPhoneNumbers('a'));
		$this->assertNull(Gebruiker::getPhoneNumbers('a', 10));
		$this->assertFalse(Gebruiker::addPhoneNumber('a', '+311230166361'));
		$this->assertFalse(Gebruiker::RemovePhoneNumber('a', '9276944742927'));

		/* Foutive parameters. */
		$this->assertFalse(Gebruiker::getPhoneNumbers(true));
		$this->assertFalse(Gebruiker::getPhoneNumbers(null));
		$this->assertFalse(Gebruiker::getPhoneNumbers(250));
		$this->assertFalse(Gebruiker::getPhoneNumbers('annie', false));
		$this->assertFalse(Gebruiker::getPhoneNumbers('annie', 'error'));
		$this->assertFalse(Gebruiker::addPhoneNumber(null, '1234567891'));
		$this->assertFalse(Gebruiker::addPhoneNumber(true, '1234567891'));
		$this->assertFalse(Gebruiker::addPhoneNumber('a', '585'));
		$this->assertFalse(Gebruiker::addPhoneNumber('a', 'lkhsadhkl'));
		$this->assertFalse(Gebruiker::addPhoneNumber('a', '+31jasghj'));
		$this->assertFalse(Gebruiker::addPhoneNumber('a', false));
		$this->assertFalse(Gebruiker::addPhoneNumber('a', null));
		$this->assertFalse(Gebruiker::removePhoneNumber(null, '1234567891'));
		$this->assertFalse(Gebruiker::removePhoneNumber(true, '1234567891'));
		$this->assertFalse(Gebruiker::removePhoneNumber('a', '858'));
		$this->assertFalse(Gebruiker::removePhoneNumber('a', '+31lkakgj'));
		$this->assertFalse(Gebruiker::removePhoneNumber('a', null));
		$this->assertFalse(Gebruiker::removePhoneNumber('a', true));
	}

	public function testGetAccountDetails()
	{
		/* Vraagt gegevens op van een bestaande gebruiker.*/
		$this->assertTrue(Gebruiker::getAccountDetails('annie') !== false);

		/* Vraagt gegevens op van een niet-bestaande gebruiker.*/
		$this->assertNull(Gebruiker::getAccountDetails('a'));

		/* Foutieve parameters. */
		$this->assertFalse(Gebruiker::getAccountDetails(50));
		$this->assertFalse(Gebruiker::getAccountDetails(false));
		$this->assertFalse(Gebruiker::getAccountDetails(null));
	}

	public function testCheckUsername()
	{
		$gebruiker = new Gebruiker();

		/* Bestaande gebruikersnaam ingevoerd */
		$this->assertTrue(Gebruiker::usernameInUse('annie'));

		/* Niet bestaande gebruikersnaam ingevoerd */
		$this->assertNull(Gebruiker::usernameInUse('a'));

		/* Foutive parameters. */
		$this->assertFalse(Gebruiker::usernameInUse(50));
		$this->assertFalse(Gebruiker::usernameInUse(false));
		$this->assertFalse(Gebruiker::usernameInUse(null));
	}

	public function testCheckGeboortedatum()
	{
		$gebruiker = new Gebruiker();

		/* Ongeldige invoer */
		$this->assertEquals('ongeldig', Gebruiker::checkBirthday('2000-50-01'));
		$this->assertEquals('ongeldig', Gebruiker::checkBirthday('2000-20-13'));
		$this->assertEquals('ongeldig', Gebruiker::checkBirthday('1970-05-40'));
		$this->assertEquals('ongeldig', Gebruiker::checkBirthday('error'));
		$this->assertEquals('ongeldig', Gebruiker::checkBirthday(null));
		$this->assertEquals('ongeldig', Gebruiker::checkBirthday('errorerror'));

		/* Jonger dan 14 jaar */
		$this->assertEquals('minimaal', Gebruiker::checkBirthday('2009-01-01'));
		$this->assertEquals('minimaal', Gebruiker::checkBirthday('2008-12-28'));

		/* Ouder dan 14 jaar */
		$this->assertTrue(Gebruiker::checkBirthday('1993-01-12'));
		$this->assertTrue(Gebruiker::checkBirthday('1990-03-25'));
		$this->assertTrue(Gebruiker::checkBirthday('1990-12-15'));
	}

	public function testGetBids()
	{
		/* Vraagt gegevens op van een bestaande gebruiker. */
		$this->assertTrue(Gebruiker::getBids('annie') !== false);

		/* Vraagt gegevens op van een niet-bestaande gebruiker. */
		$this->assertNull(Gebruiker::getBids('a'));

		/* Vraagt gegevens op van een foutive gebruiker. */
		$this->assertFalse(Gebruiker::getBids(null));
		$this->assertFalse(Gebruiker::getBids(50));
		$this->assertFalse(Gebruiker::getBids(true));
	}

	public function testGetBidsImage()
	{
		/* Vraagt gegevens op van een bestaande gebruiker. */

		$this->assertTrue(Gebruiker::getBidsImage(110712611137, 1) !== false);

		/* Vraagt gegevens op van een niet-bestaande gebruiker. */
		$this->assertFalse(Gebruiker::getBidsImage('a', 10));

		/* Foutive parameters. */
		$this->assertFalse(Gebruiker::getBidsImage(null, 10));
		$this->assertFalse(Gebruiker::getBidsImage(true, 10));
		$this->assertFalse(Gebruiker::getBidsImage('a', true));
		$this->assertFalse(Gebruiker::getBidsImage('a', null));
		$this->assertFalse(Gebruiker::getBidsImage('a', 'hallo'));
	}

	public function testGetSellersObject()
	{
		/* Vraagt de geplaatste objecten van een bestaande gebruiker op */
		$this->assertTrue(Gebruiker::getSellersObjects('annie') !== false);

		/* Vraagt de geplaatste objecten van een niet-bestaande en ongeldige gebruiker op */
		$this->assertNull(Gebruiker::getSellersObjects('a'));
		$this->assertFalse(Gebruiker::getSellersObjects(50));
	}

	public function testIsLoginValid()
	{
		/* Controleer goede gegevens. */
		$this->assertTrue(Gebruiker::isLoginValid('annie', 'drowssapgnikrow', false));
		$this->assertTrue(Gebruiker::isLoginValid('annie',
				'0a4a8c3a1ff742e8a1742b6c4492f9e6d9663444c42ad53c629837ae52e8b136bf1a4' .
				'005c29b3a943bc69ea49e482ef893b2c002e8aa9d11796dc2a706273eaf'));

		/* Controleer met ongeldige gegevens. */
		$this->assertNull(Gebruiker::isLoginValid('a', 'invalid', false));
		$this->assertNull(Gebruiker::isLoginValid('a',
				'6a162d143889f5200e64400bc53e6b998bdfcf5d7600b633ede12a67ad24efccecff5' .
				'29ebe472963ad738bb7c463a158938d2f681f238e21c0d6f795f4fd1d87'));
		$this->assertNull(Gebruiker::isLoginValid(null, 'fail', false));
		$this->assertNull(Gebruiker::isLoginValid(true, 'fail', false));
		$this->assertNull(Gebruiker::isLoginValid(50, 'fail', false));
		$this->assertNull(Gebruiker::isLoginValid('a', false, false));
		$this->assertNull(Gebruiker::isLoginValid('a', 300, false));
		$this->assertNull(Gebruiker::isLoginValid('a', null, false));
		$this->assertNull(Gebruiker::isLoginValid('a', 'fail'));
		$this->assertNull(Gebruiker::isLoginValid('a', 250));

		/* Foutive parameters. */
		$this->assertFalse(Gebruiker::isLoginValid('a', 'goodpass', 500));
		$this->assertFalse(Gebruiker::isLoginValid('a', 'goodpass', null));
	}

	public function testLogin()
	{
		/* Cookies werken niet met PHPUnit dus die worden ook niet getest.
		   logout() en loggedIn() worden niet getest omdat dat niet mogelijk is zonder echte sessie. */

		/* Controleer goede gegevens. */
		$this->assertTrue(Gebruiker::login('annie', 'drowssapgnikrow', false, false));
		$this->assertTrue(Gebruiker::login('annie',
				'0a4a8c3a1ff742e8a1742b6c4492f9e6d9663444c42ad53c629837ae52e8b136bf1a4' .
				'005c29b3a943bc69ea49e482ef893b2c002e8aa9d11796dc2a706273eaf'));

		/* Controleer met ongeldige gegevens. */
		$this->assertNull(Gebruiker::login('a', 'invalid', false, false));
		$this->assertNull(Gebruiker::login('a',
				'6a162d143889f5200e64400bc53e6b998bdfcf5d7600b633ede12a67ad24efccecff5' .
				'29ebe472963ad738bb7c463a158938d2f681f238e21c0d6f795f4fd1d87'));
		$this->assertNull(Gebruiker::login(null, 'fail', false, false));
		$this->assertNull(Gebruiker::login(true, 'fail', false, false));
		$this->assertNull(Gebruiker::login(50, 'fail', false, false));
		$this->assertNull(Gebruiker::login('a', false, false, false));
		$this->assertNull(Gebruiker::login('a', 300, false, false));
		$this->assertNull(Gebruiker::login('a', null, false, false));
		$this->assertNull(Gebruiker::login('a', 'fail'));
		$this->assertNull(Gebruiker::login('a', 250));

		/* Foutive parameters. */
		$this->assertFalse(Gebruiker::login('a', 'goodpass', 500, false));
		$this->assertFalse(Gebruiker::login('a', 'goodpass', null, false));
		$this->assertFalse(Gebruiker::login('a', 'goodpass', false, 500));
		$this->assertFalse(Gebruiker::login('a', 'goodpass', false, null));
	}

	public function testGetQuestion()
	{
		/* Geldige gebruiker. */
		$this->assertTrue(is_string(Gebruiker::getQuestion('annie')));

		/* Niet bestaande gebruiker. */
		$this->assertNull(Gebruiker::getQuestion('a'));

		/* Fouetieve parameter. */
		$this->assertFalse(Gebruiker::getQuestion(-1));
		$this->assertFalse(Gebruiker::getQuestion(278));
		$this->assertFalse(Gebruiker::getQuestion(true));
		$this->assertFalse(Gebruiker::getQuestion(null));
	}

	/**
	 * @depends testGetAccountDetails
	 */
	public function testChangeField()
	{
		/* Verifiëer oude gegevens. */
		$detailsOld = Gebruiker::getAccountDetails('annie');
		$this->assertTrue($detailsOld !== null && $detailsOld !== false);

		/* Controleer dat nieuwe postcode niet zelfde is als de oude. */
		$this->assertTrue($detailsOld['Postcode'] !== '1010XZ');

		/* Verander gegevens en controleer of het veranderd is. */
		$this->assertTrue(Gebruiker::changeField('annie', 'Postcode', '1010XZ'));
		$detailsNew = Gebruiker::getAccountDetails('annie');
		$this->assertTrue($detailsNew['Postcode'] === '1010XZ');

		/* Verander het weer terug. */
		$this->assertTrue(Gebruiker::changeField('annie', 'Postcode', $detailsOld['Postcode']));
		$detailsReverted = Gebruiker::getAccountDetails('annie');
		$this->assertTrue($detailsOld['Postcode'] === $detailsReverted['Postcode']);

		unset($detailsOld);
		unset($detailsNew);
		unset($detailsReverted);

		/* Niet bestaande gebruiker en foutieve velden en waardes. */
		$this->assertNull(Gebruiker::changeField('a', 'Postcode', '1234AB'));
		$this->assertFalse(Gebruiker::changeField('annie', 'NepVeldWatTotaalNietBestaatOfzo', '1234AB'));
		$this->assertFalse(Gebruiker::changeField('annie', 'Postcode', '1234ABDEZEPOSTCODEISVEELSTELANGGODNONDEJU'));

		/* Foutieve parameters. */
		$this->assertFalse(Gebruiker::changeField(null, 'Postcode', '1234AB'));
		$this->assertFalse(Gebruiker::changeField(true, 'Postcode', '1234AB'));
		$this->assertFalse(Gebruiker::changeField(50, 'Postcode', '1234AB'));
		$this->assertFalse(Gebruiker::changeField('annie', null, '1234AB'));
		$this->assertFalse(Gebruiker::changeField('annie', false, '1234AB'));
		$this->assertFalse(Gebruiker::changeField('annie', 250, '1234AB'));
		$this->assertFalse(Gebruiker::changeField('annie', 'Postcode', null));
		$this->assertFalse(Gebruiker::changeField('annie', 'Postcode', false));
	}
}
