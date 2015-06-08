<!DOCTYPE html>
<head>
<title>Mail Script</title>
<style>
	body {background-color:#BDC3C7;}
</style>
</head>
<body>

<?php
	include('functions.php');
/*
 * Dit script zorgt voor de afhandeling van de mail vanuit de beheerapplicatie.
*/
if (!isset($_GET['start']))
{
	$error = "Er is een onbekende fout opgetreden.";
		if (isset($_GET['email']) && isset($_GET['naam']) && isset($_GET['voorwerpnummer']) && isset($_GET['titel']))
		{
			$email = $_GET['email'];
			$naam = $_GET['naam'];
			$voorwerpnummer = $_GET['voorwerpnummer'];
			$titel = $_GET['titel'];

			if (mailNewHighestBid($email, $naam, $voorwerpnummer, $titel))
			{
				$error = "Er is een mail gestuurd naar de nieuwste hoogste bieder!";
			}
			else 
			{
				$error = "Er is een fout opgetreden tijdens het versturen van de mail.";
			}
		}
		else
		{
			$error = "Er is een fout opgetreden tijdens het ophalen van de benodigde data.";
		}
	echo $error;
}
?>

</body>