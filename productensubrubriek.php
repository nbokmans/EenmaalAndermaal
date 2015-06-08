<?php
include('functions.php');
htmlHeader('Producten', false);
require_once('php/Voorwerp.php');
require_once('php/Gebruiker.php');
require_once('php/Rubriek.php');
$rubrieknumber = $_GET['rubrieknumber'];
$subnumber = $_GET['subNumber'];
$searchProduct = array_key_exists('searchProduct', $_GET) ? $_GET['searchProduct'] : null;
settype($subnumber, "int");
?>
<div id="content">
	<?php include('searchBar.php'); ?>
	<a href="producten.php?number=<?php echo($rubrieknumber); ?>">Klik hier om terug te gaan naar de ouderrubriek.</a>
	<nav class="subNavigatie">
		<?php
		$subrubrieken = Rubriek::getSubRubriekName($subnumber);
		foreach ($subrubrieken as $sub)
		{
			?>
			<a href="productensubrubriek.php?rubrieknumber=<?php echo $rubrieknumber ?>&subNumber=<?php echo $sub['rubrieknummer'] ?>"><?php echo $sub['Rubrieknaam'] ?></a>
		<?php
		}
		?>
	</nav>
	<?php
	$voorwerp = Voorwerp::getVoorwerpInRubriek($subnumber, 500, $searchProduct);

	if ($voorwerp === null && isset($_GET['subNumber']))
	{
		echo "Er zijn voor deze categorie geen biedingen gevonden.";
	}
	else if ($voorwerp !== null && sizeof($voorwerp) === 0)
	{
		echo "Er is een databasefout opgetreden, probeer het later nog eens.";
	}
	else
	{
		foreach ($voorwerp as $element)
		{
			$voorwerpImage = Gebruiker::getBidsImage((float)$element['voorwerpnummer'], 1);
			if ($voorwerpImage === null) $voorwerpImage = array('unavailable.jpg');

			foreach ($voorwerpImage as $imagenumber)
			{
				$bod = Voorwerp::getHighestBid((float)$element['voorwerpnummer']);
				?>
				<br/>
				<div class="product">
					<a class="productImage" href="productpagina.php?productID=<?php echo $element['voorwerpnummer'] ?>"
					   title="Product"><img src="upload/products/<?php
						echo(file_exists('upload/products/' . $imagenumber['Bestandsnaam']) ?
								$imagenumber['Bestandsnaam'] : 'unavailable.jpg');
						?>"
					                        alt="Product"></a>

					<div class="productContent">
						<h3><?php echo $element['titel'] ?></h3>
					</div>
					<div class="productContent">
						<h5>Beschrijving</h5>
					</div>
					<div class="productContent">
						<p>&euro; <?php echo $bod ?></p>

						<p>Bieders</p>
					</div>
				</div>
			<?php
			}
		}
	}
	?>
</div>
<?php include('footer.php'); ?>
