<?php
include('functions.php');
htmlHeader('Persoonlijk account upgraden', false);
require_once('php/Gebruiker.php');
?>
<div id="content">
	<?php
	include('searchBar.php');
	if ($GLOBALS['userLoggedIn'])
	{
		$user = $_SESSION['username'];
		$accDetails = Gebruiker::getAccountDetails($user);
		if ((int) $accDetails['IsVerkoper'] === 0)
		{
			if (array_key_exists('betaalmethode', $_POST) && array_key_exists('verificatiemethode', $_POST))
			{
				$betaalmethode = $_POST['betaalmethode'];
				$verificatiemethode = $_POST['verificatiemethode'];
				?>
				<form action="verkoperupgrade.php" method="post">
				<input type="hidden" name="betaalmethode" value="<?php echo $betaalmethode; ?>">
				<input type="hidden" name="verificatiemethode" value="<?php echo $verificatiemethode; ?>">
				<?php
				if ($betaalmethode === "bank")
				{
					?>
					<label>
						Selecteer uw bank:
						<br/>
						<select name="banknaam">
							<option value="Rabobank">Rabobank</option>
							<option value="ING Bank">ING</option>
							<option value="ABN Amro">ANB Amro</option>
							<option value="Regiobank">Regiobank</option>
							<option value="SNS Bank">SNS Bank</option>
						</select>
					</label>
					<br/>
					<label>
						Voer uw bankrekeningnummer of IBAN in:
						<br/>
						<input type="text" size="34" maxlength="34" name="bankrekening">
					</label>
				<?php
				}
				else
				{
					?>
					<label>
						Voer uw creditcardnummer in:
						<br/>
						<input type="number" min="0" max="9999999999999999999" name="ccnummer">
					</label>
				<?php
				}
				?> <input type="submit" value="Submit"></form><?php
			}
			else
			{
				?>
				<form method="post">
					<label>
						Selecteer uw betaalmethode
						<br/>
						<select name="betaalmethode">
							<option value="bank">
								Bankrekening
							</option>
							<option value="creditcard">
								Credit card
							</option>
						</select>
					</label>
					<br/>
					<label>
						Selecteer een controleoptie
						<br/>
						<select name="verificatiemethode">
							<option value="post" selected>
								Per post
							</option>
							<option value="e-mail" disabled>
								Per e-mail
							</option>
							<option value="telefoon" disabled>
								Per telefoon
							</option>
						</select>
					</label>
					<br/>
					<input type="submit" value="Submit">
				</form>
			<?php
			}
		}
		else
		{
			echo "U bent al geregistreerd als verkoper!";
		}
	}
	else
	{
		echo "Gelieve eerst in te loggen voordat u uw account upgradet naar verkopersstatus.";
	}
	?>
</div>