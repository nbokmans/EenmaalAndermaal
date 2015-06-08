<?php
include('functions.php');
require_once('php/Gebruiker.php');
require_once('php/Vraag.php');
htmlHeader('Wachtwoord vergeten', false);

if (array_key_exists('password', $_POST))
{
	if ($_POST['password'] !== $_POST['passwordConfirm'])
	{
		$message = "Beide wachtwoorden moeten hetzelfde zijn";
	}
	else if (strlen($_POST['password']) < 6 || strlen($_POST['passwordConfirm']) < 6)
	{
		$message = 'Het wachtwoord moet langer zijn dan 5 tekens.';
	}
	else
	{
		$stage = 'success';
	}
}
?>
<div id="content">
	<?php
	if ($GLOBALS['userLoggedIn'] === null)
	{
		if (is_null($stage))
		{
			?>
			<div class="RegistratieHeader">
				<h3>Wachtwoord vergeten - alle gegevens zijn vereist.</h3>
			</div>
			<form id="contact" action="wachtwoordvergeten.php" method="post">
				<label>
					Gebruikersnaam *<br/>
					<input type="text" name="gebruikersnaam"
							<?php
							if (array_key_exists('gebruikersnaam', $_POST))
							{
								if (Gebruiker::usernameInUse($_POST['gebruikersnaam']))
								{
									echo('value="' . $_POST['gebruikersnaam'] . '" readonly');
								}
								else
								{
									unset($_POST['gebruikersnaam']);
									$message = "Onbekende gebruikersnaam!";
								}
							} ?>
                           required/>
				</label>
				<?php
				if (array_key_exists('gebruikersnaam', $_POST))
				{
					?>
					<br/>
					<label>
						Geheime vraag*<br/>
						<!-- TODO: Krijg geheime vraag van gebruiken uit database. -->
						<input type="text" value="<?php echo Gebruiker::getQuestion($_POST['gebruikersnaam']) ?>"
						       required readonly/>
					</label>
					<br/>
					<label>
						Geheim antwoord*<br/>
						<!-- TODO: Krijg geheime vraag van gebruiken uit database. -->
						<input type="text" name="geheimAntwoord"
								<?php if (array_key_exists('geheimAntwoord', $_POST))
								{
									if ($_POST['geheimAntwoord'] === Gebruiker::getHiddenAnswer($_POST['gebruikersnaam']))
									{
										echo('value="' . $_POST['geheimAntwoord'] . '" readonly');
									}
									else
									{

										unset($_POST['geheimAntwoord']);
										$message = "Geheime vraag klopt niet met accountgegevens! Let op: het geheime
									 antwoord is hoofdlettergevoelig!";
									}
								} ?>
                               required/>
					</label>
				<?php
				}
				/* TODO: Kijk of het goede antwoord gegeven is. */
				if (array_key_exists('geheimAntwoord', $_POST))
				{
					?>
					<br/>
					<label>
						Nieuw wachtwoord*<br/>
						<input type="password" name="password" required/>
					</label>
					<br/>
					<label>
						Voer uw nieuwe wachtwoord nogmaals in*<br/>
						<input type="password" name="passwordConfirm" required/>
					</label>
				<?php } ?>
				<br/><br/>
				<input type="submit" value="Verzenden"/>
			</form>
			<?php
			if (isset($message)) echo("<p><i>$message</i></p>");
		}
		/* TODO: Reset het wachtwoord in de database. */
		else if ($stage === 'success')
		{
			if (Gebruiker::changePassword($_POST['gebruikersnaam'], $_POST['password']))
			{
				?>
				<p><i>U heeft uw wachtwoord succesvol ingesteld.<br/>U kunt nu inloggen met het nieuwe wachtwoord.</i>
				</p>
			<?php
			}
			else
			{
				?>
				<p><i>Er heeft zich een onbekende fout voorgedaan. Probeer het nogmaals.</i></p>
			<?php
			}
		}
	}
	else if ($GLOBALS['userLoggedIn'] === false)
	{
		echo('<p>De database in momenteel niet beschikbaar, probeer het later nog eens.</p>');
	}
	else
	{
		echo('<p>U kunt uw wachtwoord niet opvragen terwijl u ingelogd bent.</p>');
	}
	?>
</div>
<?php include('footer.php'); ?>
