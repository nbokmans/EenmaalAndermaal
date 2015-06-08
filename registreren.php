<?php
include('functions.php');
require_once('php/dao.php');
require_once('php/Vraag.php');
require_once('php/Gebruiker.php');

$stage = 'email';
if (array_key_exists('emailConfirm', $_POST))
{
	$email = trim($_POST['email']);
	$emailConfirm = trim($_POST['emailConfirm']);

	if ($email !== $emailConfirm)
		$message = 'Beide e-mail adressen moeten hetzelfde zijn.';
	else if (!filter_var($email, FILTER_VALIDATE_EMAIL))
		$message = 'Er moet een geldig emailadres ingevuld worden.';
	else
	{
		$mailCheckResult = Gebruiker::emailInUse($email);
		if ($mailCheckResult === false)
			$message = 'Er is een databasefout opgetreden, probeer het later nog eens.';
		else if ($mailCheckResult === true)
			$message = 'Dit emailadres is al in gebruik.';
		else
		{
			$regCode = generateRegCode($email);
			if (setcookie('regCode', $regCode, strtotime('+4 hours')) === false) // Code verloopt in 4 uur.
				$message = 'Het genereren van de registratiecode is niet gelukt, probeer het later nog eens.';
			else
			{
				if (!mailRegCode($email, $regCode))
					$message = 'Het sturen van de registratie email is niet gelukt, probeer het later nog eens.';
				else $stage = 'code';
			}
		}
	}
}
if (array_key_exists('regCode', $_POST))
{
	if (!array_key_exists('regCode', $_COOKIE))
	{
		$message = 'De registratiecode is verlopen, probeer het nog eens.';
	}
	else if ($_POST['regCode'] !== $_COOKIE['regCode'])
	{
		$message = 'De door u ingevulde code is niet geldig, probeer het nogmaals.';
		$stage = 'code';
	}
	else
	{
		setcookie('regCode', null, 0);
		$stage = 'details';
	}
}
if (array_key_exists('voornaam', $_POST))
{
	$voornaam = trim($_POST['voornaam']);
	$achternaam = trim($_POST['achternaam']);
	$postcode = strtoupper(trim($_POST['postcode']));
	$adresregel1 = trim($_POST['adresregel1']);
	$adresregel2 = array_key_exists('adresregel2', $_POST) ? trim($_POST['adresregel2']) : '';
	$woonplaats = trim($_POST['woonplaats']);
	$telefoonnummer = trim($_POST['telefoonnummer']);
	$land = $_POST['land'];
	$gebruikersnaam = trim($_POST['gebruikersnaam']);
	$password = strtolower(hash('sha512', $_POST['password'], false));
	$passwordConfirm = strtolower(hash('sha512', $_POST['passwordConfirm'], false));
	$geheimeVraag = $_POST['geheimeVraag'];
	$geheimAntwoord = trim($_POST['geheimAntwoord']);
	$geboortedatum = trim($_POST['geboortedatum']);

	$message = '';

	if (strlen($voornaam) < 2 || strlen($voornaam) > 128)
		$message .= 'Voornaam moet minimaal 2 en maximaal 128 tekens lang zijn.<br/>';
	if (strlen($achternaam) < 2 || strlen($achternaam) > 128)
		$message .= 'Acthernaam moet minimaal 2 en maximal 128 tekens lang zijn.<br/>';
	if (strlen($postcode) < 4 || strlen($postcode) > 10)
		$message .= 'Postcode moet minimaal 4 en maximaal 10 tekens lang zijn.<br/>';
	if (strlen($adresregel1) < 2 || strlen($adresregel1) > 128)
		$message .= 'Adresregel 1 moet minimaal 2 en maximaal 128 tekens lang zijn.<br/>';
	if (strlen($adresregel2) === 1 || strlen($adresregel2) > 128)
		$message .= 'Adresregel 2 moet of leeggelaten worden of minimaal 2 en maximaal 128 tekens lang zijn.<br/>';
	if (strlen($woonplaats) < 2 || strlen($woonplaats) > 128)
		$message .= 'Woonplaats moet minimaal 2 en maximaal 128 tekens lang zijn.<br/>';
	if (strlen($telefoonnummer) < 10 || strlen($telefoonnummer) > 13)
		$message .= 'Telefoonnummer moet minimaal 10 en maximaal 13 tekens lang zijn.<br/>';
	else if (preg_match("/^([\+0-9][0-9]*)$/", $telefoonnummer) === 0)
		$message .= 'Telefoonnummer moet een geldig telefoonnummer zijn. Bijvoorbeeld +31123456789 of 0612345678.<br/>';
	if (strlen($gebruikersnaam) < 2 || strlen($gebruikersnaam) > 32)
		$message .= 'Gebruikersnaam moet minimaal 2 en maximaal 32 tekens lang zijn.<br/>';
	else
	{
		$result = Gebruiker::usernameInUse($gebruikersnaam);
		if ($result === false)
		{
			$message .= 'De database is tijdelijk buiten gebruik, probeer het later nog eens.<br/>';
			$databaseError = true;
		}
		else if ($result === true)
		{
			$message .= 'Deze gebruikersnaam is al in gebruik.<br/>';
		}
		unset($result);
	}
	if (strlen($_POST['password']) < 6 || strlen($_POST['passwordConfirm']) < 6)
		$message .= 'Wachtwoord moet minimaal 6 tekens lang zijn.<br/>';
	if ($password !== $passwordConfirm)
		$message .= 'Beide wachtwoorden moeten hetzelfde zijn.<br/>';
	if ($geheimeVraag === 'databaseError')
		$message .= 'Er is een ongeldige geheime vraag ingevoerd doordat de database niet beschikbaar was, probeer het later nog eens.<br/>';
	if (strlen($geheimAntwoord) < 1 || strlen($geheimAntwoord) > 32)
		$message .= 'Geheim antwoord moet minimaal 1 en maximaal 32 tekens lang zijn.<br/>';
	if (preg_match("#[0-9][0-9][0-9][0-9][-][0-9][0-9]-[0-9][0-9]#", $geboortedatum) === 0)
		$message .= 'Geboortedatum moet een format hebben van "yyyy-mm-dd".<br/>';
	else
	{
		$result = Gebruiker::checkBirthday($geboortedatum);
		if ($result === 'ongeldig')
		{
			$message .= 'De waarde van geboortedatum is geen geldige datum.<br/>';
			$message .= 'Geboortedatum moet een format hebben van "yyyy-mm-dd".<br/>';
		}
		else if ($result === 'minimaal')
		{
			$message .= 'U moet minimaal 14 jaar oud zijn om te registreren.<br/>';
		}
	}

	if ($message !== '')
	{
		/* Verwijder laatste <br/> uit $message. */
		$message = substr($message, 0, -5);
		$stage = 'details';
	}
	else
	{
		$registerFail = false;

		if (Gebruiker::register($gebruikersnaam, $password, $voornaam, $achternaam, $adresregel1, $adresregel2, $postcode, $woonplaats,
						$land, $geboortedatum, $_POST['email'], $geheimeVraag, $geheimAntwoord) !== true
		)
			$registerFail = true;

		if ($registerFail !== true)
		{
			if (Gebruiker::addPhoneNumber($gebruikersnaam, $telefoonnummer) === false)
				$registerFail = true;
		}

		if ($registerFail === true)
		{
			$message = 'Er is een databasefout opgetreden tijdens het registreren, probeer het later nog eens.';
			$stage = 'details';
		}
		else
		{
			$stage = 'success';
			unset($message);
		}
		unset($registerFail);
	}
}

htmlHeader('Registreren', false);
?>
<div id="content">
<?php
if (isset($message))
	echo("<p><i>$message</i></p>");
if ($stage === 'email')
{
	?>
	<div class="RegistratieHeader">
		<h3>Contactgegevens - alle gegevens zijn vereist.</h3>
	</div>
	<form id="contact" action="registreren.php" method="post">
		<label>
			E-mailadres*<br/>
			<input type="email" name="email"
					<?php if (array_key_exists('email', $_POST)) echo('value="' . $_POST['email'] . '"'); ?>
                   required/>
		</label>
		<br/>
		<label>
			Voer uw e-mailadres nogmaals in*<br/>
			<input type="email" name="emailConfirm" required/>
		</label>
		<br/><br/>
		<input type="submit" value="Verzenden"/>
	</form>
<?php
}
else if ($stage === 'code')
{
	?>
	<div class="RegistratieHeader">
		<h3>Contactgegevens - alle gegevens zijn vereist.</h3>
	</div>
	<p>
		<i>
			Er is een e-mail met een verificatiecode verzonden naar het door u opgegeven e-mailadres.<br/>
			De code is alleen geldig op de PC waarvan deze verzonden is en verloopt in 4 uur.
		</i>
	</p>
	<form id="contact" action="registreren.php" method="post">
		<label>
			E-mailadres*<br/>
			<input type="email" name="email"
					<?php if (array_key_exists('email', $_POST)) echo('value="' . $_POST['email'] . '"'); ?>
                   required readonly/>
		</label>
		<br/>
		<label>
			Registratiecode*<br/>
			<input type="text" name="regCode" required/>
		</label>
		<br/><br/>
		<input type="submit" value="Verzenden"/>
	</form>
<?php
}
else if ($stage === 'details')
{
	if (!isset($land)) $land = 'Netherlands';
	?>
	<div class="RegistratieHeader">
		<h3>Contactgegevens - alle gegevens zijn vereist.</h3>
	</div>
	<form id="contact" action="registreren.php" method="post">
	<label>
		Voornaam*<br/>
		<input type="text" name="voornaam"
				<?php if (array_key_exists('voornaam', $_POST)) echo('value="' . $voornaam . '"'); ?>
               required/>
	</label>
	<br/>
	<label>
		Achternaam*<br/>
		<input type="text" name="achternaam"
				<?php if (array_key_exists('achternaam', $_POST)) echo('value="' . $achternaam . '"'); ?>
               required/>
	</label>
	<br/>
	<label>
		Postcode*<br/>
		<input type="text" name="postcode"
				<?php if (array_key_exists('postcode', $_POST)) echo('value="' . $postcode . '"'); ?>
               required/>
	</label>
	<br/>
	<label>
		Adresregel 1*<br/>
		<input type="text" name="adresregel1"
				<?php if (array_key_exists('adresregel1', $_POST)) echo('value="' . $adresregel1 . '"'); ?>
               required/>
	</label>
	<br/>
	<label>
		Adresregel 2<br/>
		<input type="text" name="adresregel2"
				<?php if (array_key_exists('adresregel2', $_POST)) echo('value="' . $adresregel2 . '"'); ?>/>
	</label>
	<br/>
	<label>
		Woonplaats*<br/>
		<input type="text" name="woonplaats"
				<?php if (array_key_exists('woonplaats', $_POST)) echo('value="' . $woonplaats . '"'); ?>
               required/>
	</label>
	<br/>
	<label>
	Land*<br/>
	<select name="land">
	<option value="Afghanistan"<?php if ($land === 'Afghanistan') echo(' selected'); ?>>Afghanistan</option>
	<option value="Albania"<?php if ($land === 'Albania') echo(' selected'); ?>>Albania</option>
	<option value="Algeria"<?php if ($land === 'Algeria') echo(' selected'); ?>>Algeria</option>
	<option value="American Samoa"<?php if ($land === 'American Samoa') echo(' selected'); ?>>American Samoa
	</option>
	<option value="Andorra"<?php if ($land === 'Andorra') echo(' selected'); ?>>Andorra</option>
	<option value="Angola"<?php if ($land === 'Angola') echo(' selected'); ?>>Angola</option>
	<option value="Anguilla"<?php if ($land === 'Anguilla') echo(' selected'); ?>>Anguilla</option>
	<option value="Antartica"<?php if ($land === 'Antartica') echo(' selected'); ?>>Antarctica</option>
	<option value="Antigua and Barbuda"<?php if ($land === 'Antigua and Barbuda') echo(' selected'); ?>>Antigua and
	                                                                                                    Barbuda
	</option>
	<option value="Argentina"<?php if ($land === 'Argentina') echo(' selected'); ?>>Argentina</option>
	<option value="Armenia"<?php if ($land === 'Armenia') echo(' selected'); ?>>Armenia</option>
	<option value="Aruba"<?php if ($land === 'Aruba') echo(' selected'); ?>>Aruba</option>
	<option value="Australia"<?php if ($land === 'Australia') echo(' selected'); ?>>Australia</option>
	<option value="Austria"<?php if ($land === 'Austria') echo(' selected'); ?>>Austria</option>
	<option value="Azerbaijan"<?php if ($land === 'Azerbaijan') echo(' selected'); ?>>Azerbaijan</option>
	<option value="Bahamas"<?php if ($land === 'Bahamas') echo(' selected'); ?>>Bahamas</option>
	<option value="Bahrain"<?php if ($land === 'Bahrain') echo(' selected'); ?>>Bahrain</option>
	<option value="Bangladesh"<?php if ($land === 'Bangladesh') echo(' selected'); ?>>Bangladesh</option>
	<option value="Barbados"<?php if ($land === 'Barbados') echo(' selected'); ?>>Barbados</option>
	<option value="Belarus"<?php if ($land === 'Belarus') echo(' selected'); ?>>Belarus</option>
	<option value="Belgium"<?php if ($land === 'Belgium') echo(' selected'); ?>>Belgium</option>
	<option value="Belize"<?php if ($land === 'Belize') echo(' selected'); ?>>Belize</option>
	<option value="Benin"<?php if ($land === 'Benin') echo(' selected'); ?>>Benin</option>
	<option value="Bermuda"<?php if ($land === 'Bermuda') echo(' selected'); ?>>Bermuda</option>
	<option value="Bhutan"<?php if ($land === 'Bhutan') echo(' selected'); ?>>Bhutan</option>
	<option value="Bolivia"<?php if ($land === 'Bolivia') echo(' selected'); ?>>Bolivia</option>
	<option value="Bosnia and Herzegowina"<?php if ($land === 'Bosnia and Herzegowina') echo(' selected'); ?>>Bosnia
	                                                                                                          and
	                                                                                                          Herzegowina
	</option>
	<option value="Botswana"<?php if ($land === 'Botswana') echo(' selected'); ?>>Botswana</option>
	<option value="Bouvet Island"<?php if ($land === 'Bouvet Island') echo(' selected'); ?>>Bouvet Island</option>
	<option value="Brazil"<?php if ($land === 'Brazil') echo(' selected'); ?>>Brazil</option>
	<option value="British Indian Ocean Territory"<?php if ($land === 'British Indian Ocean Territory') echo(' selected'); ?>>
		British Indian Ocean Territory
	</option>
	<option value="Brunei Darussalam"<?php if ($land === 'Brunei Darussalam') echo(' selected'); ?>>Brunei
	                                                                                                Darussalam
	</option>
	<option value="Bulgaria"<?php if ($land === 'Bulgaria') echo(' selected'); ?>>Bulgaria</option>
	<option value="Burkina Faso"<?php if ($land === 'Burkina Faso') echo(' selected'); ?>>Burkina Faso</option>
	<option value="Burundi"<?php if ($land === 'Burundi') echo(' selected'); ?>>Burundi</option>
	<option value="Cambodia"<?php if ($land === 'Cambodia') echo(' selected'); ?>>Cambodia</option>
	<option value="Cameroon"<?php if ($land === 'Cameroon') echo(' selected'); ?>>Cameroon</option>
	<option value="Canada"<?php if ($land === 'Canada') echo(' selected'); ?>>Canada</option>
	<option value="Cape Verde"<?php if ($land === 'Cape Verde') echo(' selected'); ?>>Cape Verde</option>
	<option value="Cayman Islands"<?php if ($land === 'Cayman Islands') echo(' selected'); ?>>Cayman Islands
	</option>
	<option value="Central African Republic"<?php if ($land === 'Central African Republic') echo(' selected'); ?>>
		Central African Republic
	</option>
	<option value="Chad"<?php if ($land === 'Chad') echo(' selected'); ?>>Chad</option>
	<option value="Chile"<?php if ($land === 'Chile') echo(' selected'); ?>>Chile</option>
	<option value="China"<?php if ($land === 'China') echo(' selected'); ?>>China</option>
	<option value="Christmas Island"<?php if ($land === 'Christmas Island') echo(' selected'); ?>>Christmas Island
	</option>
	<option value="Cocos Islands"<?php if ($land === 'Cocos Islands') echo(' selected'); ?>>Cocos (Keeling) Islands
	</option>
	<option value="Colombia"<?php if ($land === 'Colombia') echo(' selected'); ?>>Colombia</option>
	<option value="Comoros"<?php if ($land === 'Comoros') echo(' selected'); ?>>Comoros</option>
	<option value="Congo"<?php if ($land === 'Congo') echo(' selected'); ?>>Congo</option>
	<option value="Cook Islands"<?php if ($land === 'Cook Islands') echo(' selected'); ?>>Cook Islands</option>
	<option value="Costa Rica"<?php if ($land === 'Costa Rica') echo(' selected'); ?>>Costa Rica</option>
	<option value="Cota D'Ivoire"<?php if ($land === 'Cota D\'Ivoire') echo(' selected'); ?>>Cote d'Ivoire</option>
	<option value="Croatia"<?php if ($land === 'Croatia') echo(' selected'); ?>>Croatia (Hrvatska)</option>
	<option value="Cuba"<?php if ($land === 'Cuba') echo(' selected'); ?>>Cuba</option>
	<option value="Cyprus"<?php if ($land === 'Cyprus') echo(' selected'); ?>>Cyprus</option>
	<option value="Czech Republic"<?php if ($land === 'Czech Republic') echo(' selected'); ?>>Czech Republic
	</option>
	<option value="Denmark"<?php if ($land === 'Denmark') echo(' selected'); ?>>Denmark</option>
	<option value="Djibouti"<?php if ($land === 'Djibouti') echo(' selected'); ?>>Djibouti</option>
	<option value="Dominica"<?php if ($land === 'Dominica') echo(' selected'); ?>>Dominica</option>
	<option value="Dominican Republic"<?php if ($land === 'Dominican Republic') echo(' selected'); ?>>Dominican
	                                                                                                  Republic
	</option>
	<option value="East Timor"<?php if ($land === 'East Timor') echo(' selected'); ?>>East Timor</option>
	<option value="Ecuador"<?php if ($land === 'Ecuador') echo(' selected'); ?>>Ecuador</option>
	<option value="Egypt"<?php if ($land === 'Egypt') echo(' selected'); ?>>Egypt</option>
	<option value="El Salvador"<?php if ($land === 'El Salvador') echo(' selected'); ?>>El Salvador</option>
	<option value="Equatorial Guinea"<?php if ($land === 'Equatorial Guinea') echo(' selected'); ?>>Equatorial
	                                                                                                Guinea
	</option>
	<option value="Eritrea"<?php if ($land === 'Eritrea') echo(' selected'); ?>>Eritrea</option>
	<option value="Estonia"<?php if ($land === 'Estonia') echo(' selected'); ?>>Estonia</option>
	<option value="Ethiopia"<?php if ($land === 'Ethiopia') echo(' selected'); ?>>Ethiopia</option>
	<option value="Falkland Islands"<?php if ($land === 'Falkland Islands') echo(' selected'); ?>>Falkland Islands
	                                                                                              (Malvinas)
	</option>
	<option value="Faroe Islands"<?php if ($land === 'Faroe Islands') echo(' selected'); ?>>Faroe Islands</option>
	<option value="Fiji"<?php if ($land === 'Fiji') echo(' selected'); ?>>Fiji</option>
	<option value="Finland"<?php if ($land === 'Finland') echo(' selected'); ?>>Finland</option>
	<option value="France"<?php if ($land === 'France') echo(' selected'); ?>>France</option>
	<option value="France Metropolitan"<?php if ($land === 'France Metropolitan') echo(' selected'); ?>>France,
	                                                                                                    Metropolitan
	</option>
	<option value="French Guiana"<?php if ($land === 'French Guiana') echo(' selected'); ?>>French Guiana</option>
	<option value="French Polynesia"<?php if ($land === 'French Polynesia') echo(' selected'); ?>>French Polynesia
	</option>
	<option value="French Southern Territories"<?php if ($land === 'French Southern Territories') echo(' selected'); ?>>
		French Southern Territories
	</option>
	<option value="Gabon"<?php if ($land === 'Gabon') echo(' selected'); ?>>Gabon</option>
	<option value="Gambia"<?php if ($land === 'Gambia') echo(' selected'); ?>>Gambia</option>
	<option value="Georgia"<?php if ($land === 'Georgia') echo(' selected'); ?>>Georgia</option>
	<option value="Germany"<?php if ($land === 'Germany') echo(' selected'); ?>>Germany</option>
	<option value="Ghana"<?php if ($land === 'Ghana') echo(' selected'); ?>>Ghana</option>
	<option value="Gibraltar"<?php if ($land === 'Gibraltar') echo(' selected'); ?>>Gibraltar</option>
	<option value="Greece"<?php if ($land === 'Greece') echo(' selected'); ?>>Greece</option>
	<option value="Greenland"<?php if ($land === 'Greenland') echo(' selected'); ?>>Greenland</option>
	<option value="Grenada"<?php if ($land === 'Grenada') echo(' selected'); ?>>Grenada</option>
	<option value="Guadeloupe"<?php if ($land === 'Guadeloupe') echo(' selected'); ?>>Guadeloupe</option>
	<option value="Guam"<?php if ($land === 'Guam') echo(' selected'); ?>>Guam</option>
	<option value="Guatemala"<?php if ($land === 'Guatemala') echo(' selected'); ?>>Guatemala</option>
	<option value="Guinea"<?php if ($land === 'Guinea') echo(' selected'); ?>>Guinea</option>
	<option value="Guinea-Bissau"<?php if ($land === 'Guinea-Bissau') echo(' selected'); ?>>Guinea-Bissau</option>
	<option value="Guyana"<?php if ($land === 'Guyana') echo(' selected'); ?>>Guyana</option>
	<option value="Haiti"<?php if ($land === 'Haiti') echo(' selected'); ?>>Haiti</option>
	<option value="Heard and McDonald Islands"<?php if ($land === 'Heard and McDonald Islands') echo(' selected'); ?>>
		Heard and Mc Donald Islands
	</option>
	<option value="Holy See"<?php if ($land === 'Holy See') echo(' selected'); ?>>Holy See (Vatican City State)
	</option>
	<option value="Honduras"<?php if ($land === 'Honduras') echo(' selected'); ?>>Honduras</option>
	<option value="Hong Kong"<?php if ($land === 'Hong Kong') echo(' selected'); ?>>Hong Kong</option>
	<option value="Hungary"<?php if ($land === 'Hungary') echo(' selected'); ?>>Hungary</option>
	<option value="Iceland"<?php if ($land === 'Iceland') echo(' selected'); ?>>Iceland</option>
	<option value="India"<?php if ($land === 'India') echo(' selected'); ?>>India</option>
	<option value="Indonesia"<?php if ($land === 'Indonesia') echo(' selected'); ?>>Indonesia</option>
	<option value="Iran"<?php if ($land === 'Iran') echo(' selected'); ?>>Iran (Islamic Republic of)</option>
	<option value="Iraq"<?php if ($land === 'Iraq') echo(' selected'); ?>>Iraq</option>
	<option value="Ireland"<?php if ($land === 'Ireland') echo(' selected'); ?>>Ireland</option>
	<option value="Israel"<?php if ($land === 'Israel') echo(' selected'); ?>>Israel</option>
	<option value="Italy"<?php if ($land === 'Italy') echo(' selected'); ?>>Italy</option>
	<option value="Jamaica"<?php if ($land === 'Jamaica') echo(' selected'); ?>>Jamaica</option>
	<option value="Japan"<?php if ($land === 'Japan') echo(' selected'); ?>>Japan</option>
	<option value="Jordan"<?php if ($land === 'Jordan') echo(' selected'); ?>>Jordan</option>
	<option value="Kazakhstan"<?php if ($land === 'Kazakhstan') echo(' selected'); ?>>Kazakhstan</option>
	<option value="Kenya"<?php if ($land === 'Kenya') echo(' selected'); ?>>Kenya</option>
	<option value="Kiribati"<?php if ($land === 'Kiribati') echo(' selected'); ?>>Kiribati</option>
	<option value="Democratic People's Republic of Korea"<?php if ($land === 'Democratic People\'s Republic of Korea') echo(' selected'); ?>>
		Korea, Democratic People's Republic of
	</option>
	<option value="Korea"<?php if ($land === 'Korea') echo(' selected'); ?>>Korea, Republic of</option>
	<option value="Kuwait"<?php if ($land === 'Kuwait') echo(' selected'); ?>>Kuwait</option>
	<option value="Kyrgyzstan"<?php if ($land === 'Kyrgyzstan') echo(' selected'); ?>>Kyrgyzstan</option>
	<option value="Lao"<?php if ($land === 'Lao') echo(' selected'); ?>>Lao People's Democratic Republic</option>
	<option value="Latvia"<?php if ($land === 'Latvia') echo(' selected'); ?>>Latvia</option>
	<option value="Lebanon"<?php if ($land === 'Lebanon') echo(' selected'); ?>>Lebanon</option>
	<option value="Lesotho"<?php if ($land === 'Lesotho') echo(' selected'); ?>>Lesotho</option>
	<option value="Liberia"<?php if ($land === 'Liberia') echo(' selected'); ?>>Liberia</option>
	<option value="Libyan Arab Jamahiriya"<?php if ($land === 'Libyan Arab Jamahiriya') echo(' selected'); ?>>Libyan
	                                                                                                          Arab
	                                                                                                          Jamahiriya
	</option>
	<option value="Liechtenstein"<?php if ($land === 'Liechtenstein') echo(' selected'); ?>>Liechtenstein</option>
	<option value="Lithuania"<?php if ($land === 'Lithuania') echo(' selected'); ?>>Lithuania</option>
	<option value="Luxembourg"<?php if ($land === 'Luxembourg') echo(' selected'); ?>>Luxembourg</option>
	<option value="Macau"<?php if ($land === 'Macau') echo(' selected'); ?>>Macau</option>
	<option value="Macedonia"<?php if ($land === 'Macedonia') echo(' selected'); ?>>Macedonia, The Former Yugoslav
	                                                                                Republic of
	</option>
	<option value="Madagascar"<?php if ($land === 'Madagascar') echo(' selected'); ?>>Madagascar</option>
	<option value="Malawi"<?php if ($land === 'Malawi') echo(' selected'); ?>>Malawi</option>
	<option value="Malaysia"<?php if ($land === 'Malaysia') echo(' selected'); ?>>Malaysia</option>
	<option value="Maldives"<?php if ($land === 'Maldives') echo(' selected'); ?>>Maldives</option>
	<option value="Mali"<?php if ($land === 'Mali') echo(' selected'); ?>>Mali</option>
	<option value="Malta"<?php if ($land === 'Malta') echo(' selected'); ?>>Malta</option>
	<option value="Marshall Islands"<?php if ($land === 'Marshall Islands') echo(' selected'); ?>>Marshall Islands
	</option>
	<option value="Martinique"<?php if ($land === 'Martinique') echo(' selected'); ?>>Martinique</option>
	<option value="Mauritania"<?php if ($land === 'Mauritania') echo(' selected'); ?>>Mauritania</option>
	<option value="Mauritius"<?php if ($land === 'Mauritius') echo(' selected'); ?>>Mauritius</option>
	<option value="Mayotte"<?php if ($land === 'Mayotte') echo(' selected'); ?>>Mayotte</option>
	<option value="Mexico"<?php if ($land === 'Mexico') echo(' selected'); ?>>Mexico</option>
	<option value="Micronesia"<?php if ($land === 'Micronesia') echo(' selected'); ?>>Micronesia, Federated States
	                                                                                  of
	</option>
	<option value="Moldova"<?php if ($land === 'Moldova') echo(' selected'); ?>>Moldova, Republic of</option>
	<option value="Monaco"<?php if ($land === 'Monaco') echo(' selected'); ?>>Monaco</option>
	<option value="Mongolia"<?php if ($land === 'Mongolia') echo(' selected'); ?>>Mongolia</option>
	<option value="Montserrat"<?php if ($land === 'Montserrat') echo(' selected'); ?>>Montserrat</option>
	<option value="Morocco"<?php if ($land === 'Morocco') echo(' selected'); ?>>Morocco</option>
	<option value="Mozambique"<?php if ($land === 'Mozambique') echo(' selected'); ?>>Mozambique</option>
	<option value="Myanmar"<?php if ($land === 'Myanmar') echo(' selected'); ?>>Myanmar</option>
	<option value="Namibia"<?php if ($land === 'Namibia') echo(' selected'); ?>>Namibia</option>
	<option value="Nauru"<?php if ($land === 'Nauru') echo(' selected'); ?>>Nauru</option>
	<option value="Nepal"<?php if ($land === 'Nepal') echo(' selected'); ?>>Nepal</option>
	<option value="Netherlands"<?php if ($land === 'Netherlands') echo(' selected'); ?>>Netherlands</option>
	<option value="Netherlands Antilles"<?php if ($land === 'Netherlands Antilles') echo(' selected'); ?>>
		Netherlands
		Antilles
	</option>
	<option value="New Caledonia"<?php if ($land === 'New Caledonia') echo(' selected'); ?>>New Caledonia</option>
	<option value="New Zealand"<?php if ($land === 'New Zealand') echo(' selected'); ?>>New Zealand</option>
	<option value="Nicaragua"<?php if ($land === 'Nicaragua') echo(' selected'); ?>>Nicaragua</option>
	<option value="Niger"<?php if ($land === 'Niger') echo(' selected'); ?>>Niger</option>
	<option value="Nigeria"<?php if ($land === 'Nigeria') echo(' selected'); ?>>Nigeria</option>
	<option value="Niue"<?php if ($land === 'Niue') echo(' selected'); ?>>Niue</option>
	<option value="Norfolk Island"<?php if ($land === 'Norfolk Island') echo(' selected'); ?>>Norfolk Island
	</option>
	<option value="Northern Mariana Islands"<?php if ($land === 'Northern Mariana Islands') echo(' selected'); ?>>
		Northern Mariana Islands
	</option>
	<option value="Norway"<?php if ($land === 'Norway') echo(' selected'); ?>>Norway</option>
	<option value="Oman"<?php if ($land === 'Oman') echo(' selected'); ?>>Oman</option>
	<option value="Pakistan"<?php if ($land === 'Pakistan') echo(' selected'); ?>>Pakistan</option>
	<option value="Palau"<?php if ($land === 'Palau') echo(' selected'); ?>>Palau</option>
	<option value="Panama"<?php if ($land === 'Panama') echo(' selected'); ?>>Panama</option>
	<option value="Papua New Guinea"<?php if ($land === 'Papua New Guinea') echo(' selected'); ?>>Papua New Guinea
	</option>
	<option value="Paraguay"<?php if ($land === 'Paraguay') echo(' selected'); ?>>Paraguay</option>
	<option value="Peru"<?php if ($land === 'Peru') echo(' selected'); ?>>Peru</option>
	<option value="Philippines"<?php if ($land === 'Philippines') echo(' selected'); ?>>Philippines</option>
	<option value="Pitcairn"<?php if ($land === 'Pitcairn') echo(' selected'); ?>>Pitcairn</option>
	<option value="Poland"<?php if ($land === 'Poland') echo(' selected'); ?>>Poland</option>
	<option value="Portugal"<?php if ($land === 'Portugal') echo(' selected'); ?>>Portugal</option>
	<option value="Puerto Rico"<?php if ($land === 'Puerto Rico') echo(' selected'); ?>>Puerto Rico</option>
	<option value="Qatar"<?php if ($land === 'Qatar') echo(' selected'); ?>>Qatar</option>
	<option value="Reunion"<?php if ($land === 'Reunion') echo(' selected'); ?>>Reunion</option>
	<option value="Romania"<?php if ($land === 'Romania') echo(' selected'); ?>>Romania</option>
	<option value="Russia"<?php if ($land === 'Russia') echo(' selected'); ?>>Russian Federation</option>
	<option value="Rwanda"<?php if ($land === 'Rwanda') echo(' selected'); ?>>Rwanda</option>
	<option value="Saint Kitts and Nevis"<?php if ($land === 'Saint Kitts and Nevis') echo(' selected'); ?>>Saint
	                                                                                                        Kitts
	                                                                                                        and
	                                                                                                        Nevis
	</option>
	<option value="Saint LUCIA"<?php if ($land === 'Saint LUCIA') echo(' selected'); ?>>Saint LUCIA</option>
	<option value="Saint Vincent"<?php if ($land === 'Saint Vincent') echo(' selected'); ?>>Saint Vincent and the
	                                                                                        Grenadines
	</option>
	<option value="Samoa"<?php if ($land === 'Samoa') echo(' selected'); ?>>Samoa</option>
	<option value="San Marino"<?php if ($land === 'San Marino') echo(' selected'); ?>>San Marino</option>
	<option value="Sao Tome and Principe"<?php if ($land === 'Sao Tome and Principe') echo(' selected'); ?>>Sao Tome
	                                                                                                        and
	                                                                                                        Principe
	</option>
	<option value="Saudi Arabia"<?php if ($land === 'Saudi Arabia') echo(' selected'); ?>>Saudi Arabia</option>
	<option value="Senegal"<?php if ($land === 'Senegal') echo(' selected'); ?>>Senegal</option>
	<option value="Seychelles"<?php if ($land === 'Seychelles') echo(' selected'); ?>>Seychelles</option>
	<option value="Sierra"<?php if ($land === 'Sierra') echo(' selected'); ?>>Sierra Leone</option>
	<option value="Singapore"<?php if ($land === 'Singapore') echo(' selected'); ?>>Singapore</option>
	<option value="Slovenia"<?php if ($land === 'Slovakia') echo(' selected'); ?>>Slovenia</option>
	<option value="Solomon Islands"<?php if ($land === 'Solomon Islands') echo(' selected'); ?>>Solomon Islands
	</option>
	<option value="Somalia"<?php if ($land === 'Somalia') echo(' selected'); ?>>Somalia</option>
	<option value="South Africa"<?php if ($land === 'South Africa') echo(' selected'); ?>>South Africa</option>
	<option value="South Georgia"<?php if ($land === 'South Georgia') echo(' selected'); ?>>South Georgia and the
	                                                                                        South
	                                                                                        Sandwich Islands
	</option>
	<option value="Spain"<?php if ($land === 'Spain') echo(' selected'); ?>>Spain</option>
	<option value="Sri Lanka"<?php if ($land === 'SriLanka') echo(' selected'); ?>>Sri Lanka</option>
	<option value="St. Helena"<?php if ($land === 'St. Helena') echo(' selected'); ?>>St. Helena</option>
	<option value="St. Pierre and Miguelon"<?php if ($land === 'St. Pierre and Miguelon') echo(' selected'); ?>>St.
	                                                                                                            Pierre
	                                                                                                            and
	                                                                                                            Miquelon
	</option>
	<option value="Sudan"<?php if ($land === 'Sudan') echo(' selected'); ?>>Sudan</option>
	<option value="Suriname"<?php if ($land === 'Suriname') echo(' selected'); ?>>Suriname</option>
	<option value="Svalbard"<?php if ($land === 'Svalbard') echo(' selected'); ?>>Svalbard and Jan Mayen Islands
	</option>
	<option value="Swaziland"<?php if ($land === 'Swaziland') echo(' selected'); ?>>Swaziland</option>
	<option value="Sweden"<?php if ($land === 'Sweden') echo(' selected'); ?>>Sweden</option>
	<option value="Switzerland"<?php if ($land === 'Switzerland') echo(' selected'); ?>>Switzerland</option>
	<option value="Syria"<?php if ($land === 'Syria') echo(' selected'); ?>>Syrian Arab Republic</option>
	<option value="Taiwan"<?php if ($land === 'Taiwan') echo(' selected'); ?>>Taiwan, Province of China</option>
	<option value="Tajikistan"<?php if ($land === 'Tajikistan') echo(' selected'); ?>>Tajikistan</option>
	<option value="Tanzania"<?php if ($land === 'Tanzania') echo(' selected'); ?>>Tanzania, United Republic of
	</option>
	<option value="Thailand"<?php if ($land === 'Thailand') echo(' selected'); ?>>Thailand</option>
	<option value="Togo"<?php if ($land === 'Togo') echo(' selected'); ?>>Togo</option>
	<option value="Tokelau"<?php if ($land === 'Tokelau') echo(' selected'); ?>>Tokelau</option>
	<option value="Tonga"<?php if ($land === 'Tonga') echo(' selected'); ?>>Tonga</option>
	<option value="Trinidad and Tobago"<?php if ($land === 'Trinidad and Tobago') echo(' selected'); ?>>Trinidad and
	                                                                                                    Tobago
	</option>
	<option value="Tunisia"<?php if ($land === 'Tunisia') echo(' selected'); ?>>Tunisia</option>
	<option value="Turkey"<?php if ($land === 'Turkey') echo(' selected'); ?>>Turkey</option>
	<option value="Turkmenistan"<?php if ($land === 'Turkmenistan') echo(' selected'); ?>>Turkmenistan</option>
	<option value="Turks and Caicos"<?php if ($land === 'Turks and Caicos') echo(' selected'); ?>>Turks and Caicos
	                                                                                              Islands
	</option>
	<option value="Tuvalu"<?php if ($land === 'Tuvalu') echo(' selected'); ?>>Tuvalu</option>
	<option value="Uganda"<?php if ($land === 'Uganda') echo(' selected'); ?>>Uganda</option>
	<option value="Ukraine"<?php if ($land === 'Ukraine') echo(' selected'); ?>>Ukraine</option>
	<option value="United Arab Emirates"<?php if ($land === 'United Arab Emirates') echo(' selected'); ?>>United
	                                                                                                      Arab
	                                                                                                      Emirates
	</option>
	<option value="United Kingdom"<?php if ($land === 'United Kingdom') echo(' selected'); ?>>United Kingdom
	</option>
	<option value="United States"<?php if ($land === 'United States') echo(' selected'); ?>>United States</option>
	<option value="United States Minor Outlying Islands"<?php if ($land === 'United States Minor Outlying Islands') echo(' selected'); ?>>
		United States Minor Outlying Islands
	</option>
	<option value="Uruguay"<?php if ($land === 'Uruguay') echo(' selected'); ?>>Uruguay</option>
	<option value="Uzbekistan"<?php if ($land === 'Uzbekistan') echo(' selected'); ?>>Uzbekistan</option>
	<option value="Vanuatu"<?php if ($land === 'Vanuatu') echo(' selected'); ?>>Vanuatu</option>
	<option value="Venezuela"<?php if ($land === 'Venezuela') echo(' selected'); ?>>Venezuela</option>
	<option value="Vietnam"<?php if ($land === 'Vietnam') echo(' selected'); ?>>Viet Nam</option>
	<option value="Virgin Islands (British)"<?php if ($land === 'Virgin Islands (British)') echo(' selected'); ?>>
		Virgin
		Islands
		(British)
	</option>
	<option value="Virgin Islands (U.S)"<?php if ($land === 'Virgin Islands (U.S.)') echo(' selected'); ?>>Virgin
	                                                                                                       Islands
	                                                                                                       (U.S.)
	</option>
	<option value="Wallis and Futana Islands"<?php if ($land === 'Wallis and Futana Islands') echo(' selected'); ?>>
		Wallis and Futuna Islands
	</option>
	<option value="Western Sahara"<?php if ($land === 'Western Sahara') echo(' selected'); ?>>Western Sahara
	</option>
	<option value="Yemen"<?php if ($land === 'Yemen') echo(' selected'); ?>>Yemen</option>
	<option value="Yugoslavia"<?php if ($land === 'Yugoslavia') echo(' selected'); ?>>Yugoslavia</option>
	<option value="Zambia"<?php if ($land === 'Zambia') echo(' selected'); ?>>Zambia</option>
	<option value="Zimbabwe"<?php if ($land === 'Zimbabwe') echo(' selected'); ?>>Zimbabwe</option>
	</select>
	</label>
	<br/>
	<label>
		Telefoonnummer*<br/>
		<input type="text" name="telefoonnummer"
				<?php if (array_key_exists('telefoonnummer', $_POST)) echo('value="' . $_POST['telefoonnummer'] . '"'); ?>
               required/>
	</label>
	<br/>
	<label>
		E-mailadres*<br/>
		<input type="email" name="email"
				<?php if (array_key_exists('email', $_POST)) echo('value="' . $_POST['email'] . '"'); ?>
               required readonly/>
	</label>

	<div class="RegistratieHeader">
		<h3>Kies je gebruikersnaam en wachtwoord - alle gegevens zijn vereist.</h3>
	</div>

	<label>
		Gebruikersnaam*<br/>
		<input type="text" name="gebruikersnaam"
				<?php if (array_key_exists('gebruikersnaam', $_POST)) echo('value="' . $gebruikersnaam . '"'); ?>
               required/>
	</label>
	<br/>
	<label>
		Wachtwoord*<br/>
		<input type="password" name="password" required/>
	</label>
	<br/>
	<label>
		Voer uw wachtwoord nogmaals in*<br/>
		<input type="password" name="passwordConfirm" required/>
	</label>
	<br/>
	<label>
		Kies een geheime vraag*<br/>
		<select name="geheimeVraag">
			<?php
			/* Als er al eerder op de pagina een databasefout was, probeer dat niet eens te connecten om tijd te besparen. */
			if (!isset($databaseError))
			{
				$questions = Vraag::getSecretQuestions();
				if ($questions === false || $questions === null) $databaseError = true;
				else
				{
					foreach ($questions as $element)
					{
						/* <option value="Vraagnummer">Vraag</option> */
						echo('<option value="');
						echo($element['Vraagnummer']);
						echo('"');
						if (array_key_exists('geheimeVraag', $_POST) &&
								$_POST['geheimeVraag'] == $element['Vraagnummer']
						) echo(' selected');
						echo('>');
						echo($element['Vraag']);
						echo('</option>');
						echo("\n");
					}
				}
			}
			unset($questions);
			if (isset($databaseError)) echo('<option value="databaseError" selected>Er is een fout opgetreden, zie toelichting hieronder.</option>');
			?>
		</select>
	</label>
	<?php
	if (isset($databaseError))
		echo('<p><i>Er is een databasefout opgetreden, u kunt de rest van de
		      pagina invullen en op verzenden klikken om het opnieuw te proberen.</i></p>');
	?>
	<br/>
	<label>
		Geheim antwoord*<br/>
		<input type="text" name="geheimAntwoord"
				<?php
				if (array_key_exists('geheimAntwoord', $_POST)) echo('value="' . $geheimAntwoord . '"');
				else if (isset($databaseError))
				{
					echo('value="Er is een fout opgetreden." readonly');
					unset($databaseError);
				}
				?>
               required/>
	</label>
	<br/>
	<label>
		Geboortedatum* (jjjj-mm-dd)<br/>
		<input type="date" name="geboortedatum"
				<?php if (array_key_exists('geboortedatum', $_POST)) echo('value="' . $geboortedatum . '"'); ?>
               required/>
	</label>
	<br/><br/>
	<input type="submit" value="Verzenden"/>
	</form>
<?php
}
else if ($stage === 'success')
{
	?>
	<p><i>U heeft zich succesvol geregistreerd.</i></p>
	<?php header('Location: index.php'); ?>
<?php
}?>
</div>
<?php
include('footer.php');?>
