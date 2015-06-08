<?php
include('functions.php');
require_once('php/Voorwerp.php');
require_once('php/Gebruiker.php');
htmlHeader('Mijn veilingen', false);
?>
<div id="content">
	<?php
	include('searchBar.php');
	?>	<div class="RegistratieHeader">
		<h3>Mijn veilingen</h3>
	</div><?php
	if (isset($_GET['user']) && Gebruiker::usernameInUse($_GET['user']))
	{
	$user = $_GET['user'];
	$auctions = Gebruiker::getAuctions($user);
	if (Gebruiker::getAuctions($user) === null)
	{
		echo "Deze gebruiker heeft geen veilingen!";
	}
	else
	{
	?>
	<br/>
	<table id="geplaatsteProducten">
		<tr>
			<th>Product</th>
			<th>Afbeelding</th>
			<th>Hoogste bod</th>
			<th>Tijd</th>
		</tr>
		<?php
		foreach ($auctions as $auction)
		{
			$voorwerpNummer = (float)$auction['voorwerpnummer'];
			$highestBid = Voorwerp::getHighestBid($voorwerpNummer);
			$afbeelding = Voorwerp::getImageLocation($voorwerpNummer);
			echo "<tr>";
			echo "<td><a href=\"productpagina.php?productID=" . $voorwerpNummer . "\">" . $auction['titel'] .
					"</a></td>";
			echo "<td><img src=\"upload/products/" . $afbeelding . "\" alt=\"" . $auction['titel'] . "\"></td>";
			echo "<td>" . Voorwerp::getHighestBid(($voorwerpNummer)) . "</td>";
			echo "<td>" . Voorwerp::getAuctionStatus($voorwerpNummer) . "</td>";
			echo "</tr>";
		}
		?>
	</table>
</div>
<?php

}
}
else
{
	echo "Dit account bestaat niet!";
}
?>
</div>