<?php
include('functions.php');
htmlHeader('feedback', false);
require_once('php/Gebruiker.php');
require_once('php/Voorwerp.php');
require_once('php/Feedback.php');
?>
<div id="content">
	<?php
	if ($GLOBALS['userLoggedIn']) {
	if (isset($_GET['user']) && isset($_GET['productId']))
	{
	$user = $_GET['user'];
	$pid = (float)$_GET['productId'];
	if (Gebruiker::usernameInUse($user) && Voorwerp::isValidProductId($pid)) {
	if (Voorwerp::confirmAuctionOwner($user, $pid)) {
	if (Voorwerp::checkAuctionWinner($_SESSION['username'], $pid)) {
	?>
	<div class="RegistratieHeader">
		<h3>Feedback</h3>
	</div>
	<form method="post" action="feedbackopslaan.php" id="beoordeling">
		<input type="hidden" name="gebruikersnaam" value="<?php echo $user; ?>">
		<input type="hidden" name="pid" value="<?php echo $pid; ?>">
		<label>
			Gebruiker<br/>
			<input type="text" name="gebruikersnaam2" value="<?php echo $user; ?>" readonly>
		</label>
		<br/>
		<label>
			Feedback datum<br/>
			<input type="date" name="datum" value="<?php echo date('Y-m-d'); ?>">
		</label>
		<br/>
		Beoordeling (*)<br/>
		<label>Positief
			<input type="radio" name="beoordeling" value="+" checked>
		</label>
		<br/>
		<label>Neutraal
			<input type="radio" name="beoordeling" value="0">
		</label>
		<br/>
		<label>Negatief
			<input type="radio" name="beoordeling" value="-">
		</label>
		<br/>
		<label>
			Beschrijving<br/>
			<textarea name="commentaar">Hier komt de beschrijving</textarea>
		</label>
		<input type="submit" value="Verzenden"/>
	</form>
</div>
<?php
} else
{
	echo "U kunt geen feedback geven op een veiling die u niet gewonnen heeft of nog niet afgelopen is!";
}
} else
{
	echo "Het opgegeven account en veilingnummer komen niet overeen.";
}
} else
{
	echo "Het opgegeven account of product bestaat niet.";
}
} else
{
	echo "Er heeft zich een fout voorgedaan. Probeer het nog eens.";
}
} else
{
	echo "Gelieve in te loggen voordat u feedback geeft op een gebruiker.";
}
include('footer.php'); ?>
