<?php
include('functions.php');
require_once('php/Feedback.php');
htmlHeader('Feedback opslaan', false);
?>
<div id="content">
	<?php
	include('searchBar.php');
	if (isset($_POST['gebruikersnaam']) && isset($_POST['datum']) && isset($_POST['beoordeling']) &&
			isset($_POST['commentaar']) && isset($_POST['pid'])
	)
	{
		$pid = (float)$_POST['pid'];
		$user = (String)$_POST['gebruikersnaam'];
		$date = $_POST['datum'];
		$rating = $_POST['beoordeling'];
		$comment = $_POST['commentaar'];
		echo Feedback::addFeedback($pid, $user, $rating, $date, $comment) === true ? "Succesvol feedback opgeslagen!" :
				"Er is een fout opgetreden, probeer het later nog eens.";
	}
	else
	{
		echo "Fout: niet alle variabelen aanwezig.";
	}
	?>
	<br/>
	<a href="index.php">Keer terug naar hoofdpagina.</a>
</div>