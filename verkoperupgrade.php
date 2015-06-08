<?php
include('functions.php');
htmlHeader('Persoonlijk account upgraden', false);
require_once('php/Gebruiker.php');
?>
<div id="content">
	<?php include('searchBar.php');
	if ($GLOBALS['userLoggedIn'])
	{
		$user = $_SESSION['username'];
		$accDetails = Gebruiker::getAccountDetails($user);
		if ($accDetails['IsVerkoper'] === 0)
		{
			?>
			<div class="RegistratieHeader">
				<h3>Accountupgrade - Verifiëren.</h3>
			</div>
			<?php
			if (isset($_POST['betaalmethode']) && isset($_POST['verificatiemethode']))
			{
				$betaalmethode = $_POST['betaalmethode'];
				$verificatiemethode = $_POST['verificatiemethode'];
				if ($betaalmethode === "bank")
				{
					if (isset($_POST['banknaam']) && isset($_POST['bankrekening']))
					{
						$banknaam = $_POST['banknaam'];
						$bankrekening = $_POST['bankrekening'];
						echo Gebruiker::setSellerStatus($user, false, $verificatiemethode, "", $banknaam, $bankrekening)
						=== true ? "U bent nu geregistreerd als verkoper." : "Er is iets foutgegaan, probeer het nog eens.";
					}
					else
					{
						echo "Ongeldige invoer, probeer het later nog eens.";
					}
				}
				else if ($betaalmethode === "creditcard")
				{
					if (isset($_POST['ccnummer']))
					{
						$ccnummer = $_POST['ccnummer'];
						echo Gebruiker::setSellerStatus($user, true, $verificatiemethode, $ccnummer)
						=== true ? "U bent nu geregistreerd als verkoper." : "Er is iets foutgegaan, probeer het nog eens.";
					}
					else
					{
						echo "Ongeldige invoer, probeer het later nog eens.";
					}
				}
				else
				{
					echo "Ongeldige betaalmethode opgegeven, neem contact op met de beheerder.";
				}
			}
		}
		else
		{
			echo "U bent al een verkoper!";
		}
	}
	else
	{
		echo "Gelieve in te loggen voordat u uw account upgradet naar een verkopersaccount.";
	}?>
</div>