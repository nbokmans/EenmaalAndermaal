<?php
include('functions.php');
require_once('php/Voorwerp.php');
require_once('php/Gebruiker.php');
htmlHeader('Mijn biedingen', false);
?>
<div id="content">
	<?php include('searchBar.php');
	?>
	<div class="RegistratieHeader">
		<h3>Mijn biedingen</h3>
	</div>
	<?php
	if (isset($_GET['user']) && Gebruiker::usernameInUse($_GET['user']))  {
	$user = $_GET['user'];
	if (Gebruiker::getBids($user) === null) {
		echo "Deze gebruiker heeft nog nergens op geboden.";
	}   else {
	?>
	<br/>
	<table id="geplaatsteProducten">
		<tr>
			<th>Product</th>
			<th>Afbeelding</th>
			<th>Geplaatste bod</th>
			<th>Huidige bod</th>
			<th>Tijd</th>
		</tr>
		<?php
		$biddings = Gebruiker::getBids($user);
		foreach ($biddings as $bid)
		{
			$voorwerpNummer = (float)$bid['voorwerpnummer'];
			$highestBid = Voorwerp::getHighestBid($voorwerpNummer);
			$afbeelding = Voorwerp::getImageLocation($voorwerpNummer);
			echo "<tr>";
			echo "<td><a href=\"productpagina.php?productID=" . $voorwerpNummer . "\">" . $bid['titel'] .
					"</a></td>";
			echo "<td><img src=\"upload/products/" . $afbeelding . "\" alt=\"" . $bid['titel'] . "\"></td>";
			echo "<td>" . $bid['Hoogste_bod'] . "</td>";
			echo "<td>" . Voorwerp::getHighestBid(($voorwerpNummer)) . "</td>";
			echo "<td>" . Voorwerp::getAuctionStatus($voorwerpNummer) . "</td>";
			echo "</tr>";
		}
		?>
	</table>
</div>
<?php
}
} else
{
	echo "Dit account kon niet gevonden worden in de database.";
}
include('footer.php');?>
