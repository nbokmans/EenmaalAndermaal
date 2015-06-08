<?php
include('functions.php');
htmlHeader('ProductPagina', false);
require_once('php/Voorwerp.php');
require_once('php/Feedback.php');
?>
<div id="content">
	<?php include('searchBar.php');
	if (isset($_GET['productID'])) {
	$productId = (float)$_GET['productID'];
	if (Voorwerp::isValidProductId($productId)) {
	?>
	<div class="biedingen">
		<div>
			<h4>Biedingen</h4>
			<?php
			$topBids = Voorwerp::getTopBids($productId, 10);
			if ($topBids === null)
			{
				echo "Geen gevonden biedingen!";
			}
			else
			{
				foreach ($topBids as $bid)
				{
					echo "&euro;" . $bid['Bodbedrag'] . " ";
					echo "(<a href=\"profiel.php?user=" . $bid['Gebruiker'] . "\">" . $bid['Gebruiker'] . "</a>)";
					echo "<br>";
				}
			}
			?>
		</div>
		<hr/>
		<?php if ((Voorwerp::getAuctionStatus($productId) === "Deze aanbieding is al verlopen!") === false)
		{
			?>
			<div>
				<form action="plaatsbod.php" method="post">
					<input type="hidden" name="pid" value="<?php echo $productId; ?>">
					<label>
						Bod toevoegen<br/>
						<input type="text" name="bod" placeholder="Bedrag in Euro's"/>
					</label>
					<br/><br/>
					<input type="submit" value="Bod plaatsen" onclick="return confirm('Wilt u doorgaan met uw bod?');">
				</form>
			</div>
		<?php } ?>
	</div>
	<div class="productInformatie">
		<img src="upload/products/<?php
		$location = Voorwerp::getImageLocation($productId);
		echo(file_exists('upload/products/' . $location) ?
				$location : 'unavailable.jpg');
		unset($location);
		?>" alt="Product Afbeelding">

		<div>
			<p>
				<b>Verkoper:</b> <?php $seller = Voorwerp::getField('Verkoper', $productId);
				echo "<a href=\"profiel.php?user=" . $seller . "\">";
				echo $seller;
				echo "</a>";?><br/><br/>
				<?php
				$totalFeedbackCount = Feedback::getFeedbackCount($seller, "all");
				$positiveFeedbackCount = Feedback::getFeedbackCount($seller, "+");
				$negativeFeedbackCount = Feedback::getFeedbackCount($seller, "-");
				$neutralFeedbackCount = Feedback::getFeedbackCount($seller, "0");
				$positivePercentage = ($positiveFeedbackCount / $totalFeedbackCount) * 100;
				$neutralPercentage = ($neutralFeedbackCount / $totalFeedbackCount) * 100;
				$negativePercentage = ($negativeFeedbackCount / $totalFeedbackCount) * 100;
				?>
				<b>Feedback:</b> <?php echo $totalFeedbackCount; ?> totaal.<br/><br/>
				<b>Positief:</b> <?php echo $positiveFeedbackCount;
				echo " (" . $positivePercentage . "%)" ?>      <br>
				<b>Neutraal:</b> <?php echo $neutralFeedbackCount;
				echo " (" . $neutralPercentage . "%)" ?>           <br>
				<b>Negatief:</b> <?php echo $negativeFeedbackCount;
				echo " (" . $negativePercentage . "%)" ?>              <br>
			</p>
		</div>
	</div>
	<h3><?php echo Voorwerp::getField('Titel', $productId); ?></h3>
	<hr/>
	<b>Resterende tijd:</b>

	<div class="timeRemaining">
		<?php
		$gesloten = Voorwerp::getAuctionStatus($productId);
		if ($gesloten !== 'Deze aanbieding is al verlopen!')
		{
			$eindDag = Voorwerp::getAuctionEndDate($productId);
			$eindTijd = Voorwerp::getAuctionEndTime($productId);
			include 'scripts/remainingtime.php';
		}
		else echo($gesloten);
		?>
	</div>
	<br/>
	<b>Huidig bod:</b> <?php $bid = Voorwerp::getHighestBid($productId);
	echo $bid === null ? "Nog geen biedingen" : "&euro;" . $bid; ?>.<br/>
	<hr/>
	<h3>Beschrijving</h3>

	<p>
		<?php echo strip_tags(Voorwerp::getField('Beschrijving', $productId), "<br><p>"); ?>
	</p>
	<hr/>
	<h3>Extra informatie</h3>
	<b>Startprijs: </b> &euro;<?php echo Voorwerp::getField('startprijs', $productId); ?>
	<br/>
	<b>Verzendinstructies: </b> <?php echo Voorwerp::getField('Verzendinstructies', $productId); ?>
	<br/>
	<b>Verzendkosten: </b> &euro;<?php echo Voorwerp::getField('Verzendkosten', $productId); ?>
	<br/>
	<b>Betalingswijze: </b>  <?php echo Voorwerp::getField('Betalingswijze', $productId); ?>
	<br/>
	<b>Betalingsinstructies: </b> <?php echo Voorwerp::getField('Betalingsinstructie', $productId); ?>
	<br/>
	<?php
	$fotoArray = Voorwerp::getImages($productId);
	if (sizeof($fotoArray) > 1)
	{
		?>
		<hr/>
		<h3>Extra foto's</h3>
		<b>Klik op foto's voor een grotere afbeelding.</b>
		<br/>
		<?php
		$index = 1;
		foreach ($fotoArray as $f)
		{
			$foto = $f['Bestandsnaam'];
			$fotoLoc = file_exists('upload/products/' . $foto) ?
					'upload/products/' . $foto : 'unavailable.jpg';
			echo "<a href=\"" . $fotoLoc . "\" style=\"display:inline-block; padding: 5px;\">";
			echo("<img src=\"" . $fotoLoc . "\" alt=\"Extra foto #" . $index .
					'" style="width: 200px; height: 200px; padding: 1px; border: 2px solid #3498DB;"/>');
			echo "</a>";
			$index++;
		}
	}
	?>
</div>
<?php
} else
{
	echo "Dit product bestaat niet!";
}
} else
{
	echo "Kies eerst een bieding uit.";
}
include('footer.php');?>
