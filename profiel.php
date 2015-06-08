<?php
require_once('functions.php');
require_once('php/Voorwerp.php');
require_once('php/Gebruiker.php');
htmlHeader('Profiel', false);

if (array_key_exists('user', $_GET)) $user = htmlspecialchars_decode($_GET['user']);

if (isset($user))
{
	$userDetails = Gebruiker::getAccountDetails($user);

	$primaryPhoneNumber = Gebruiker::getPhoneNumbers($user, 1);
	if ($primaryPhoneNumber === null) $primaryPhoneNumber = '';
	else $primaryPhoneNumber = $primaryPhoneNumber[0]['Telefoonnummer'];

	if ($GLOBALS['userLoggedIn'] === true && $_SESSION['username'] === $user) $allowEdits = true;
	else $allowEdits = false;
}
?>
<div id="content">
<?php
include('searchBar.php');

if (!isset($userDetails) || $userDetails === null || !isset($user)) echo('<p>Deze gebruiker bestaat niet.</p>');
else if ($userDetails === false) echo('<p>Er is een databasefout opgetreden, probeer het later nog eens.</p>');
else
{
	/* Iedere gebruiker mag telefoonnummers inzien. */
	if (array_key_exists('userEditTelefoonnummer', $_POST)) $editAction = 'Telefoonnummer';

	if ($allowEdits === true && !isset($editAction))
	{
		if (array_key_exists('editAction', $_POST)) $editAction = $_POST['editAction'];
		else if (array_key_exists('userUpgradeAccount', $_POST)) $editAction = 'Upgrade';
		else if (array_key_exists('userEditPassword', $_POST)) $editAction = 'Password';
		else if (array_key_exists('userEditName', $_POST)) $editAction = 'Name';
		else if (array_key_exists('userEditAdresregel1', $_POST)) $editAction = 'Adresregel1';
		else if (array_key_exists('userEditAdresregel2', $_POST)) $editAction = 'Adresregel2';
		else if (array_key_exists('userEditPostcode', $_POST)) $editAction = 'Postcode';
		else if (array_key_exists('userEditWoonplaats', $_POST)) $editAction = 'Woonplaats';
		else if (array_key_exists('userEditLand', $_POST)) $editAction = 'Land';
	}

	if (isset($editAction))
	{
		echo('<p><a href="profiel.php?user=' . $_GET['user'] . '">Klik hier om terug naar te gaan naar de profielpagina.</a></p>');

		if ($allowEdits === true)
		{
			if (array_key_exists('voornaam', $_POST)) $voornaam = trim($_POST['voornaam']);
			if (array_key_exists('achternaam', $_POST)) $achternaam = trim($_POST['achternaam']);
			if (array_key_exists('postcode', $_POST)) $postcode = strtoupper(trim($_POST['postcode']));
			if (array_key_exists('adresregel1', $_POST)) $adresregel1 = trim($_POST['adresregel1']);
			if (array_key_exists('adresregel2', $_POST)) $adresregel2 = array_key_exists('adresregel2', $_POST) ? trim($_POST['adresregel2']) : '';
			if (array_key_exists('woonplaats', $_POST)) $woonplaats = trim($_POST['woonplaats']);
			if (array_key_exists('land', $_POST)) $land = $_POST['land'];
			else $land = 'Netherlands';
			if (array_key_exists('passwordCurrent', $_POST)) $passwordCurrent = strtolower(hash('sha512', $_POST['passwordCurrent'], false));
			if (array_key_exists('password', $_POST))
			{
				$password = strtolower(hash('sha512', $_POST['password'], false));
				if (array_key_exists('passwordConfirm', $_POST)) $passwordConfirm = strtolower(hash('sha512', $_POST['passwordConfirm'], false));
				else
				{
					/* Trigger password invalid checks. */
					$password = 'a';
					$_POST['password'] = 'a';
					$passwordConfirm = 'b';
					$_POST['passwordConfirm'] = 'b';
				}
			}
			if (array_key_exists('addTelefoonnummer', $_POST)) $addTelefoonnummer = trim($_POST['addTelefoonnummer']);
			if (array_key_exists('removeTelefoonnummer', $_POST)) $removeTelefoonnummer = trim($_POST['removeTelefoonnummer']);

			$message = '';

			if (isset($voornaam) && (strlen($voornaam) < 2 || strlen($voornaam) > 128))
				$message .= 'Voornaam moet minimaal 2 en maximaal 128 tekens lang zijn.<br/>';
			if (isset($achternaam) && (strlen($achternaam) < 2 || strlen($achternaam) > 128))
				$message .= 'Acthernaam moet minimaal 2 en maximal 128 tekens lang zijn.<br/>';
			if (isset($postcode) && (strlen($postcode) < 4 || strlen($postcode) > 10))
				$message .= 'Postcode moet minimaal 4 en maximaal 10 tekens lang zijn.<br/>';
			if (isset($adresregel1) && (strlen($adresregel1) < 2 || strlen($adresregel1) > 128))
				$message .= 'Adresregel 1 moet minimaal 2 en maximaal 128 tekens lang zijn.<br/>';
			if (isset($adresregel2) && (strlen($adresregel2) === 1 || strlen($adresregel2) > 128))
				$message .= 'Adresregel 2 moet of leeggelaten worden of minimaal 2 en maximaal 128 tekens lang zijn.<br/>';
			if (isset($woonplaats) && (strlen($woonplaats) < 2 || strlen($woonplaats) > 128))
				$message .= 'Woonplaats moet minimaal 2 en maximaal 128 tekens lang zijn.<br/>';
			if ((isset($password) || isset($passwordConfirm)) && !isset($passwordCurrent))
				$message .= 'U moet alle velden invullen';
			else if (isset($passwordCurrent) && $_SESSION['password'] !== $passwordCurrent)
				$message .= 'Uw gegeven huidige wachtwoord is niet juist, probeer het nog eens.<br/>';
			if (isset($password) && (strlen($_POST['password']) < 6 || strlen($_POST['passwordConfirm']) < 6))
				$message .= 'Uw nieuwe wachtwoord moet minimaal 6 tekens lang zijn.<br/>';
			if (isset($password) && isset($passwordConfirm) && ($password !== $passwordConfirm))
				$message .= 'Beide nieuwe wachtwoorden moeten hetzelfde zijn.<br/>';
			if (isset($addTelefoonnummer) && strlen($addTelefoonnummer) < 10 || strlen($addTelefoonnummer) > 13)
				$message .= 'Telefoonnummer moet minimaal 10 en maximaal 13 tekens lang zijn.<br/>';
			else if (isset($addTelefoonnummer) && preg_match("/^([\+0-9][0-9]*)$/", $addTelefoonnummer) === 0)
				$message .= 'Telefoonnummer moet een geldig telefoonnummer zijn. Bijvoorbeeld +31123456789 of 0612345678.<br/>';

			if ($message !== '')
			{
				/* Verwijder laatste <br/> uit $message. */
				$message = substr($message, 0, -5);
				$stage = 'details';
				echo("<p>$message</p>");
			}
			else
			{
				/* Alles OK, voer update uit. */
				if (array_key_exists('voornaam', $_POST))
				{
					if (Gebruiker::changeField($user, 'Voornaam', $voornaam) !== true) $editResult = false;
					else $editResult = true;
				}
				if (array_key_exists('achternaam', $_POST))
				{
					if (Gebruiker::changeField($user, 'Achternaam', $achternaam) !== true) $editResult = false;
					else $editResult = true;
				}
				if (array_key_exists('postcode', $_POST))
				{
					if (Gebruiker::changeField($user, 'Postcode', $postcode) !== true) $editResult = false;
					else $editResult = true;
				}
				if (array_key_exists('adresregel1', $_POST))
				{
					if (Gebruiker::changeField($user, 'Adresregel1', $adresregel1) !== true) $editResult = false;
					else $editResult = true;
				}
				if (array_key_exists('adresregel2', $_POST))
				{
					if (Gebruiker::changeField($user, 'Adresregel2', $adresregel2) !== true) $editResult = false;
					else $editResult = true;
				}
				if (array_key_exists('woonplaats', $_POST))
				{
					if (Gebruiker::changeField($user, 'Plaatsnaam', $woonplaats) !== true) $editResult = false;
					else $editResult = true;
				}
				if (array_key_exists('land', $_POST))
				{
					if (Gebruiker::changeField($user, 'Land', $land) !== true) $editResult = false;
					else $editResult = true;
				}
				if (array_key_exists('password', $_POST))
				{
					if (Gebruiker::changeField($user, 'Wachtwoord', $password) !== true) $editResult = false;
					else
					{
						Gebruiker::logout();
						$editResult = true;
					}
				}
				if (array_key_exists('addTelefoonnummer', $_POST))
				{
					if (Gebruiker::addPhoneNumber($user, $addTelefoonnummer) === false) $editResult = false;
					else $editResult = true;
				}
				if (array_key_exists('removeTelefoonnummer', $_POST))
				{
					if (Gebruiker::removePhoneNumber($user, $removeTelefoonnummer) === false) $editResult = false;
					else $editResult = true;
				}
			}

			if (isset($editResult))
			{
				if ($editResult !== true)
				{
					echo('<p>Er is een fout opgetreden tijdens het opslaan van de gegevens, probeer het later nog eens.</p>');
					if ($editAction = 'Telefoonnummer')
						echo('<p>U kunt niet hetzelfde telefoonnummer twee keer invoeren.</p>');
				}
				else
					echo('<p>De gegevens zijn succesvol opgeslagen.</p>');
			}
		}

		switch ($editAction)
		{
			case 'Upgrade':
				// TODO: Maak account upgrade.
				?>
				<p>Momenteel niet beschikbaar.</p>
				<?php
				break;

			case 'Password':
				if (isset($editResult) && $editResult === true) echo('<p>U kunt nadat de pagina herladen is inloggen met uw nieuwe wachtwoord.</p>');
				else
				{
					?>
					<form id="contact" method="post">
						<label>
							Huidig wachtwoord*
							<input type="password" name="passwordCurrent" required/>
						</label>
						<label>
							Nieuw wachtwoord*<br/>
							<input type="password" name="password" required/>
						</label>
						<br/>
						<label>
							Voer uw nieuwe wachtwoord nogmaals in*<br/>
							<input type="password" name="passwordConfirm" required/>
						</label>
						<br/>
						<?php echo('<input type="hidden" name="editAction" value="' . $editAction . '"/>'); ?>
						<input type="submit" value="Opslaan"/>
					</form>
				<?php
				}
				break;

			case 'Name':
				?>
				<form id="contact" method="post">
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
					<?php echo('<input type="hidden" name="editAction" value="' . $editAction . '"/>'); ?>
					<input type="submit" value="Opslaan"/>
				</form>
				<?php
				break;

			case 'Adresregel1':
				?>
				<form id="contact" method="post">
					<label>
						Adresregel 1*<br/>
						<input type="text" name="adresregel1"
								<?php if (array_key_exists('adresregel1', $_POST)) echo('value="' . $adresregel1 . '"'); ?>
                               required/>
					</label>
					<br/>
					<?php echo('<input type="hidden" name="editAction" value="' . $editAction . '"/>'); ?>
					<input type="submit" value="Opslaan"/>
				</form>
				<?php
				break;

			case 'Adresregel2':
				?>
				<form id="contact" method="post">
					<label>
						Adresregel 2<br/>
						<input type="text" name="adresregel2"
								<?php if (array_key_exists('adresregel2', $_POST)) echo('value="' . $adresregel2 . '"'); ?>/>
					</label>
					<br/>
					<?php echo('<input type="hidden" name="editAction" value="' . $editAction . '"/>'); ?>
					<input type="submit" value="Opslaan"/>
				</form>
				<?php
				break;

			case 'Postcode':
				?>
				<form id="contact" method="post">
					<label>
						Postcode*<br/>
						<input type="text" name="postcode"
								<?php if (array_key_exists('postcode', $_POST)) echo('value="' . $postcode . '"'); ?>
                               required/>
					</label>
					<br/>
					<?php echo('<input type="hidden" name="editAction" value="' . $editAction . '"/>'); ?>
					<input type="submit" value="Opslaan"/>
				</form>
				<?php
				break;

			case 'Woonplaats':
				?>
				<form id="contact" method="post">
					<label>
						Woonplaats*<br/>
						<input type="text" name="woonplaats"
								<?php if (array_key_exists('woonplaats', $_POST)) echo('value="' . $woonplaats . '"'); ?>
                               required/>
					</label>
					<br/>
					<?php echo('<input type="hidden" name="editAction" value="' . $editAction . '"/>'); ?>
					<input type="submit" value="Opslaan"/>
				</form>
				<?php
				break;

			case 'Land':
				?>
				<form id="contact" method="post">
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
				<option value="Antigua and Barbuda"<?php if ($land === 'Antigua and Barbuda') echo(' selected'); ?>>
					Antigua
					and
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
				<option value="Bosnia and Herzegowina"<?php if ($land === 'Bosnia and Herzegowina') echo(' selected'); ?>>
					Bosnia and
					Herzegowina
				</option>
				<option value="Botswana"<?php if ($land === 'Botswana') echo(' selected'); ?>>Botswana</option>
				<option value="Bouvet Island"<?php if ($land === 'Bouvet Island') echo(' selected'); ?>>Bouvet Island
				</option>
				<option value="Brazil"<?php if ($land === 'Brazil') echo(' selected'); ?>>Brazil</option>
				<option value="British Indian Ocean Territory"<?php if ($land === 'British Indian Ocean Territory') echo(' selected'); ?>>
					British Indian Ocean Territory
				</option>
				<option value="Brunei Darussalam"<?php if ($land === 'Brunei Darussalam') echo(' selected'); ?>>Brunei
				                                                                                                Darussalam
				</option>
				<option value="Bulgaria"<?php if ($land === 'Bulgaria') echo(' selected'); ?>>Bulgaria</option>
				<option value="Burkina Faso"<?php if ($land === 'Burkina Faso') echo(' selected'); ?>>Burkina Faso
				</option>
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
				<option value="Christmas Island"<?php if ($land === 'Christmas Island') echo(' selected'); ?>>Christmas
				                                                                                              Island
				</option>
				<option value="Cocos Islands"<?php if ($land === 'Cocos Islands') echo(' selected'); ?>>Cocos (Keeling)
				                                                                                        Islands
				</option>
				<option value="Colombia"<?php if ($land === 'Colombia') echo(' selected'); ?>>Colombia</option>
				<option value="Comoros"<?php if ($land === 'Comoros') echo(' selected'); ?>>Comoros</option>
				<option value="Congo"<?php if ($land === 'Congo') echo(' selected'); ?>>Congo</option>
				<option value="Cook Islands"<?php if ($land === 'Cook Islands') echo(' selected'); ?>>Cook Islands
				</option>
				<option value="Costa Rica"<?php if ($land === 'Costa Rica') echo(' selected'); ?>>Costa Rica</option>
				<option value="Cota D'Ivoire"<?php if ($land === 'Cota D\'Ivoire') echo(' selected'); ?>>Cote d'Ivoire
				</option>
				<option value="Croatia"<?php if ($land === 'Croatia') echo(' selected'); ?>>Croatia (Hrvatska)</option>
				<option value="Cuba"<?php if ($land === 'Cuba') echo(' selected'); ?>>Cuba</option>
				<option value="Cyprus"<?php if ($land === 'Cyprus') echo(' selected'); ?>>Cyprus</option>
				<option value="Czech Republic"<?php if ($land === 'Czech Republic') echo(' selected'); ?>>Czech Republic
				</option>
				<option value="Denmark"<?php if ($land === 'Denmark') echo(' selected'); ?>>Denmark</option>
				<option value="Djibouti"<?php if ($land === 'Djibouti') echo(' selected'); ?>>Djibouti</option>
				<option value="Dominica"<?php if ($land === 'Dominica') echo(' selected'); ?>>Dominica</option>
				<option value="Dominican Republic"<?php if ($land === 'Dominican Republic') echo(' selected'); ?>>
					Dominican
					Republic
				</option>
				<option value="East Timor"<?php if ($land === 'East Timor') echo(' selected'); ?>>East Timor</option>
				<option value="Ecuador"<?php if ($land === 'Ecuador') echo(' selected'); ?>>Ecuador</option>
				<option value="Egypt"<?php if ($land === 'Egypt') echo(' selected'); ?>>Egypt</option>
				<option value="El Salvador"<?php if ($land === 'El Salvador') echo(' selected'); ?>>El Salvador</option>
				<option value="Equatorial Guinea"<?php if ($land === 'Equatorial Guinea') echo(' selected'); ?>>
					Equatorial
					Guinea
				</option>
				<option value="Eritrea"<?php if ($land === 'Eritrea') echo(' selected'); ?>>Eritrea</option>
				<option value="Estonia"<?php if ($land === 'Estonia') echo(' selected'); ?>>Estonia</option>
				<option value="Ethiopia"<?php if ($land === 'Ethiopia') echo(' selected'); ?>>Ethiopia</option>
				<option value="Falkland Islands"<?php if ($land === 'Falkland Islands') echo(' selected'); ?>>Falkland
				                                                                                              Islands
				                                                                                              (Malvinas)
				</option>
				<option value="Faroe Islands"<?php if ($land === 'Faroe Islands') echo(' selected'); ?>>Faroe Islands
				</option>
				<option value="Fiji"<?php if ($land === 'Fiji') echo(' selected'); ?>>Fiji</option>
				<option value="Finland"<?php if ($land === 'Finland') echo(' selected'); ?>>Finland</option>
				<option value="France"<?php if ($land === 'France') echo(' selected'); ?>>France</option>
				<option value="France Metropolitan"<?php if ($land === 'France Metropolitan') echo(' selected'); ?>>
					France,
					Metropolitan
				</option>
				<option value="French Guiana"<?php if ($land === 'French Guiana') echo(' selected'); ?>>French Guiana
				</option>
				<option value="French Polynesia"<?php if ($land === 'French Polynesia') echo(' selected'); ?>>French
				                                                                                              Polynesia
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
				<option value="Guinea-Bissau"<?php if ($land === 'Guinea-Bissau') echo(' selected'); ?>>Guinea-Bissau
				</option>
				<option value="Guyana"<?php if ($land === 'Guyana') echo(' selected'); ?>>Guyana</option>
				<option value="Haiti"<?php if ($land === 'Haiti') echo(' selected'); ?>>Haiti</option>
				<option value="Heard and McDonald Islands"<?php if ($land === 'Heard and McDonald Islands') echo(' selected'); ?>>
					Heard and Mc Donald Islands
				</option>
				<option value="Holy See"<?php if ($land === 'Holy See') echo(' selected'); ?>>Holy See (Vatican City
				                                                                              State)
				</option>
				<option value="Honduras"<?php if ($land === 'Honduras') echo(' selected'); ?>>Honduras</option>
				<option value="Hong Kong"<?php if ($land === 'Hong Kong') echo(' selected'); ?>>Hong Kong</option>
				<option value="Hungary"<?php if ($land === 'Hungary') echo(' selected'); ?>>Hungary</option>
				<option value="Iceland"<?php if ($land === 'Iceland') echo(' selected'); ?>>Iceland</option>
				<option value="India"<?php if ($land === 'India') echo(' selected'); ?>>India</option>
				<option value="Indonesia"<?php if ($land === 'Indonesia') echo(' selected'); ?>>Indonesia</option>
				<option value="Iran"<?php if ($land === 'Iran') echo(' selected'); ?>>Iran (Islamic Republic of)
				</option>
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
				<option value="Lao"<?php if ($land === 'Lao') echo(' selected'); ?>>Lao People's Democratic Republic
				</option>
				<option value="Latvia"<?php if ($land === 'Latvia') echo(' selected'); ?>>Latvia</option>
				<option value="Lebanon"<?php if ($land === 'Lebanon') echo(' selected'); ?>>Lebanon</option>
				<option value="Lesotho"<?php if ($land === 'Lesotho') echo(' selected'); ?>>Lesotho</option>
				<option value="Liberia"<?php if ($land === 'Liberia') echo(' selected'); ?>>Liberia</option>
				<option value="Libyan Arab Jamahiriya"<?php if ($land === 'Libyan Arab Jamahiriya') echo(' selected'); ?>>
					Libyan
					Arab
					Jamahiriya
				</option>
				<option value="Liechtenstein"<?php if ($land === 'Liechtenstein') echo(' selected'); ?>>Liechtenstein
				</option>
				<option value="Lithuania"<?php if ($land === 'Lithuania') echo(' selected'); ?>>Lithuania</option>
				<option value="Luxembourg"<?php if ($land === 'Luxembourg') echo(' selected'); ?>>Luxembourg</option>
				<option value="Macau"<?php if ($land === 'Macau') echo(' selected'); ?>>Macau</option>
				<option value="Macedonia"<?php if ($land === 'Macedonia') echo(' selected'); ?>>Macedonia, The Former
				                                                                                Yugoslav
				                                                                                Republic of
				</option>
				<option value="Madagascar"<?php if ($land === 'Madagascar') echo(' selected'); ?>>Madagascar</option>
				<option value="Malawi"<?php if ($land === 'Malawi') echo(' selected'); ?>>Malawi</option>
				<option value="Malaysia"<?php if ($land === 'Malaysia') echo(' selected'); ?>>Malaysia</option>
				<option value="Maldives"<?php if ($land === 'Maldives') echo(' selected'); ?>>Maldives</option>
				<option value="Mali"<?php if ($land === 'Mali') echo(' selected'); ?>>Mali</option>
				<option value="Malta"<?php if ($land === 'Malta') echo(' selected'); ?>>Malta</option>
				<option value="Marshall Islands"<?php if ($land === 'Marshall Islands') echo(' selected'); ?>>Marshall
				                                                                                              Islands
				</option>
				<option value="Martinique"<?php if ($land === 'Martinique') echo(' selected'); ?>>Martinique</option>
				<option value="Mauritania"<?php if ($land === 'Mauritania') echo(' selected'); ?>>Mauritania</option>
				<option value="Mauritius"<?php if ($land === 'Mauritius') echo(' selected'); ?>>Mauritius</option>
				<option value="Mayotte"<?php if ($land === 'Mayotte') echo(' selected'); ?>>Mayotte</option>
				<option value="Mexico"<?php if ($land === 'Mexico') echo(' selected'); ?>>Mexico</option>
				<option value="Micronesia"<?php if ($land === 'Micronesia') echo(' selected'); ?>>Micronesia, Federated
				                                                                                  States of
				</option>
				<option value="Moldova"<?php if ($land === 'Moldova') echo(' selected'); ?>>Moldova, Republic of
				</option>
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
				<option value="New Caledonia"<?php if ($land === 'New Caledonia') echo(' selected'); ?>>New Caledonia
				</option>
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
				<option value="Papua New Guinea"<?php if ($land === 'Papua New Guinea') echo(' selected'); ?>>Papua New
				                                                                                              Guinea
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
				<option value="Saint Kitts and Nevis"<?php if ($land === 'Saint Kitts and Nevis') echo(' selected'); ?>>
					Saint Kitts
					and Nevis
				</option>
				<option value="Saint LUCIA"<?php if ($land === 'Saint LUCIA') echo(' selected'); ?>>Saint LUCIA</option>
				<option value="Saint Vincent"<?php if ($land === 'Saint Vincent') echo(' selected'); ?>>Saint Vincent
				                                                                                        and
				                                                                                        the
				                                                                                        Grenadines
				</option>
				<option value="Samoa"<?php if ($land === 'Samoa') echo(' selected'); ?>>Samoa</option>
				<option value="San Marino"<?php if ($land === 'San Marino') echo(' selected'); ?>>San Marino</option>
				<option value="Sao Tome and Principe"<?php if ($land === 'Sao Tome and Principe') echo(' selected'); ?>>
					Sao
					Tome
					and
					Principe
				</option>
				<option value="Saudi Arabia"<?php if ($land === 'Saudi Arabia') echo(' selected'); ?>>Saudi Arabia
				</option>
				<option value="Senegal"<?php if ($land === 'Senegal') echo(' selected'); ?>>Senegal</option>
				<option value="Seychelles"<?php if ($land === 'Seychelles') echo(' selected'); ?>>Seychelles</option>
				<option value="Sierra"<?php if ($land === 'Sierra') echo(' selected'); ?>>Sierra Leone</option>
				<option value="Singapore"<?php if ($land === 'Singapore') echo(' selected'); ?>>Singapore</option>
				<option value="Slovenia"<?php if ($land === 'Slovakia') echo(' selected'); ?>>Slovenia</option>
				<option value="Solomon Islands"<?php if ($land === 'Solomon Islands') echo(' selected'); ?>>Solomon
				                                                                                            Islands
				</option>
				<option value="Somalia"<?php if ($land === 'Somalia') echo(' selected'); ?>>Somalia</option>
				<option value="South Africa"<?php if ($land === 'South Africa') echo(' selected'); ?>>South Africa
				</option>
				<option value="South Georgia"<?php if ($land === 'South Georgia') echo(' selected'); ?>>South Georgia
				                                                                                        and
				                                                                                        the South
				                                                                                        Sandwich Islands
				</option>
				<option value="Spain"<?php if ($land === 'Spain') echo(' selected'); ?>>Spain</option>
				<option value="Sri Lanka"<?php if ($land === 'SriLanka') echo(' selected'); ?>>Sri Lanka</option>
				<option value="St. Helena"<?php if ($land === 'St. Helena') echo(' selected'); ?>>St. Helena</option>
				<option value="St. Pierre and Miguelon"<?php if ($land === 'St. Pierre and Miguelon') echo(' selected'); ?>>
					St.
					Pierre
					and
					Miquelon
				</option>
				<option value="Sudan"<?php if ($land === 'Sudan') echo(' selected'); ?>>Sudan</option>
				<option value="Suriname"<?php if ($land === 'Suriname') echo(' selected'); ?>>Suriname</option>
				<option value="Svalbard"<?php if ($land === 'Svalbard') echo(' selected'); ?>>Svalbard and Jan Mayen
				                                                                              Islands
				</option>
				<option value="Swaziland"<?php if ($land === 'Swaziland') echo(' selected'); ?>>Swaziland</option>
				<option value="Sweden"<?php if ($land === 'Sweden') echo(' selected'); ?>>Sweden</option>
				<option value="Switzerland"<?php if ($land === 'Switzerland') echo(' selected'); ?>>Switzerland</option>
				<option value="Syria"<?php if ($land === 'Syria') echo(' selected'); ?>>Syrian Arab Republic</option>
				<option value="Taiwan"<?php if ($land === 'Taiwan') echo(' selected'); ?>>Taiwan, Province of China
				</option>
				<option value="Tajikistan"<?php if ($land === 'Tajikistan') echo(' selected'); ?>>Tajikistan</option>
				<option value="Tanzania"<?php if ($land === 'Tanzania') echo(' selected'); ?>>Tanzania, United Republic
				                                                                              of
				</option>
				<option value="Thailand"<?php if ($land === 'Thailand') echo(' selected'); ?>>Thailand</option>
				<option value="Togo"<?php if ($land === 'Togo') echo(' selected'); ?>>Togo</option>
				<option value="Tokelau"<?php if ($land === 'Tokelau') echo(' selected'); ?>>Tokelau</option>
				<option value="Tonga"<?php if ($land === 'Tonga') echo(' selected'); ?>>Tonga</option>
				<option value="Trinidad and Tobago"<?php if ($land === 'Trinidad and Tobago') echo(' selected'); ?>>
					Trinidad
					and
					Tobago
				</option>
				<option value="Tunisia"<?php if ($land === 'Tunisia') echo(' selected'); ?>>Tunisia</option>
				<option value="Turkey"<?php if ($land === 'Turkey') echo(' selected'); ?>>Turkey</option>
				<option value="Turkmenistan"<?php if ($land === 'Turkmenistan') echo(' selected'); ?>>Turkmenistan
				</option>
				<option value="Turks and Caicos"<?php if ($land === 'Turks and Caicos') echo(' selected'); ?>>Turks and
				                                                                                              Caicos
				                                                                                              Islands
				</option>
				<option value="Tuvalu"<?php if ($land === 'Tuvalu') echo(' selected'); ?>>Tuvalu</option>
				<option value="Uganda"<?php if ($land === 'Uganda') echo(' selected'); ?>>Uganda</option>
				<option value="Ukraine"<?php if ($land === 'Ukraine') echo(' selected'); ?>>Ukraine</option>
				<option value="United Arab Emirates"<?php if ($land === 'United Arab Emirates') echo(' selected'); ?>>
					United
					Arab
					Emirates
				</option>
				<option value="United Kingdom"<?php if ($land === 'United Kingdom') echo(' selected'); ?>>United Kingdom
				</option>
				<option value="United States"<?php if ($land === 'United States') echo(' selected'); ?>>United States
				</option>
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
				<option value="Virgin Islands (U.S)"<?php if ($land === 'Virgin Islands (U.S.)') echo(' selected'); ?>>
					Virgin
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
				<?php echo('<input type="hidden" name="editAction" value="' . $editAction . '"/>'); ?>
				<input type="submit" value="Opslaan"/>
				</form>
				<?php
				break;

			case 'Telefoonnummer':
				echo('<table class="accountInformatie">');
				echo('<tr><th>Volgnummer</th><th>Telefoonnummer</th><th>');
				if ($allowEdits === true) echo('Verwijderen');
				echo('</th></tr>');
				$phoneNumbers = Gebruiker::getPhoneNumbers($user);
				if (sizeof($phoneNumbers) === 1) $disableRemoval = true;
				else $disableRemoval = false;
				foreach ($phoneNumbers as $number)
				{
					?>
					<tr>
						<td><?php echo($number['Volgnummer']); ?></td>
						<td><?php echo($number['Telefoonnummer']); ?></td>
						<td>
							<?php
							if ($allowEdits)
							{
								if ($disableRemoval === true) echo('U kunt het laatste nummer niet verwijderen.');
								else
								{
									?>
									<form method="post">
										<input type="hidden" name="removeTelefoonnummer"
										       value="<?php echo($number['Telefoonnummer']); ?>"/>
										<?php echo('<input type="hidden" name="editAction" value="' . $editAction . '"/>'); ?>
										<input type="submit" value="Verwijder"/>
									</form>
								<?php
								}
							}
							?>
						</td>
					</tr>
				<?php
				}
				unset($phoneNumbers);
				unset($disableRemoval);
				echo('</table>');

				if ($allowEdits === true)
				{
					?>
					<br/>
					<br/>
					<br/>
					<hr/>
					<form id="contact" method="post">
						<label>
							Telefoonnummer toevoegen<br/>
							<input type="text" name="addTelefoonnummer" required/><br/>
						</label>
						<?php echo('<input type="hidden" name="editAction" value="' . $editAction . '"/>'); ?>
						<input type="submit" value="Opslaan"/>
					</form>
				<?php
				}
				break;

			default:
				echo("<p>Fout: onbekende waarde van editAction: $editAction. Probeer het later nog eens.</p>");
				break;
		}
	}
}

if (isset($user) && !isset($editAction) && isset($userDetails) && $userDetails !== false && $userDetails !== null)
{
	?>
	<nav class="subNavigatie">
		<a href="profiel.php?user=<?php echo $user; ?>">Profiel</a>
		<a href="mijnbiedingen.php?user=<?php echo $user; ?>">Biedingen</a>
		<a href="mijnveilingen.php?user=<?php echo $user; ?>">Veilingen</a>
		<a href="mijnfeedback.php?user=<?php echo $user; ?>">Feedback</a>
		<?php
		if ($GLOBALS['userLoggedIn'] && $user === $_SESSION['username'])
		{
			$accDetails = Gebruiker::getAccountDetails($user);
			$isVerkoper = $accDetails['IsVerkoper'];
			if ($isVerkoper)
			{
				?>
				<a href="producttoevoegen.php">Product toevoegen</a>    <?php
			}
		} ?>
	</nav>

	<div class="RegistratieHeader">
		<h3>Accountgegevens</h3>
	</div>
	<br/>

	<table class="accountInformatie">
		<tr>
			<td>Account type</td>
			<td><?php echo(($userDetails['IsVerkoper'] === 1) ? 'Verkoper' : 'Koper'); ?></td>
			<td>
				<?php if ($allowEdits === true && $userDetails['IsVerkoper'] === 0)
				echo('<form action="accountupgrade.php"><input type="submit" value="Upgrade"></form>');?>
			</td>
		</tr>
		<tr>
			<td>Gebruikersnaam</td>
			<td><?php echo($userDetails['Gebruikersnaam']); ?></td>
			<td></td>
		</tr>
		<tr>
			<td>Wachtwoord</td>
			<td>Beveiligd opgeslagen</td>
			<td>
				<?php if ($allowEdits === true)
					echo('<form method="post"><input type="submit" name="userEditPassword" value="Pas aan"/></form>'); ?>
			</td>
		</tr>
	</table>

	<div class="RegistratieHeader">
		<h3>Contactgegevens</h3>
	</div>

	<br/>

	<table style="width: 100%" class="accountInformatie">
		<tr>
			<td>Primair telefoonnummer</td>
			<td><?php echo($primaryPhoneNumber); ?></td>
			<td><?php
				echo('<form method="post"><input type="submit" name="userEditTelefoonnummer" value="');
				if ($allowEdits === true) echo('Pas aan');
				else echo('Meer');
				echo('"');
				echo('/></form>');
				?></td>
		</tr>
		<tr>
			<td>Email adres</td>
			<td><?php echo($userDetails['Emailadres']); ?></td>
			<td></td>
		</tr>
		<tr>
			<td>Voor- en achternaam</td>
			<td><?php echo($userDetails['Voornaam'] . ' ' . $userDetails['Achternaam']); ?></td>
			<td>
				<?php if ($allowEdits === true)
					echo('<form method="post"><input type="submit" name="userEditName" value="Pas aan"/></form>'); ?>
			</td>
		</tr>
		<tr>
			<td>Eerste adresregel</td>
			<td><?php echo($userDetails['Adresregel1']); ?></td>
			<td>
				<?php if ($allowEdits === true)
					echo('<form method="post"><input type="submit" name="userEditAdresregel1" value="Pas aan"/></form>'); ?>
			</td>
		</tr>
		<tr>
			<td>Tweede adresregel</td>
			<td><?php echo($userDetails['Adresregel2']); ?></td>
			<td>
				<?php if ($allowEdits === true)
					echo('<form method="post"><input type="submit" name="userEditAdresregel2" value="Pas aan"/></form>'); ?>
			</td>
		</tr>
		<tr>
			<td>Postcode</td>
			<td><?php echo($userDetails['Postcode']); ?></td>
			<td>
				<?php if ($allowEdits === true)
					echo('<form method="post"><input type="submit" name="userEditPostcode" value="Pas aan"/></form>'); ?>
			</td>
		</tr>
		<tr>
			<td>Woonplaats</td>
			<td><?php echo($userDetails['Plaatsnaam']); ?></td>
			<td>
				<?php if ($allowEdits === true)
					echo('<form method="post"><input type="submit" name="userEditWoonplaats" value="Pas aan"/></form>'); ?>
			</td>
		</tr>
		<tr>
			<td>Land</td>
			<td><?php echo($userDetails['Land']); ?></td>
			<td>
				<?php if ($allowEdits === true)
					echo('<form method="post"><input type="submit" name="userEditLand" value="Pas aan"/></form>'); ?>
			</td>
		</tr>
	</table>
<?php } ?>
</div>
<?php include('footer.php'); ?>
