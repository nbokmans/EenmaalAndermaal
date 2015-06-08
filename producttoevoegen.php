<?php
include('functions.php');
htmlHeader('Product toevoegen', false);
require_once('php/Rubriek.php');
require_once('php/Voorwerp.php');
require_once('php/Bestand.php');
?>
<div id="content"><?php
	include('searchBar.php');
	if ($GLOBALS['userLoggedIn']) {
	?>

	<div class="RegistratieHeader">
		<h3>Product toevoegen - alle gegevens met een * zijn verplicht.</h3>
	</div>
	<?php
	$accDetails = Gebruiker::getAccountDetails($_SESSION['username']);
	if ((boolean)$accDetails['IsVerkoper'] === true) {
	$checkPostVars = array('titel', /*'image',*/
			'beschrijving', 'rubriekId', 'subrubriekId', 'startprijs', 'betalingswijze', 'looptijd', 'verzendkosten');
	$checkPostVarsLength = array('betalinginstructie', 'verzendinstructie');
	$varLength = sizeof($checkPostVars);
	$postVarsValues = array();
	$verifiedVars = 0;
	foreach ($checkPostVars as $cpv)
	{
		if (isset($_POST[$cpv]))
		{
			$verifiedVars++;
		}
	}

	if ($verifiedVars === $varLength)
	{
		foreach ($checkPostVars as $cpv)
		{
			$postVarsValues[$cpv] = $_POST[$cpv];
		}
		foreach ($checkPostVarsLength as $cpvl)
		{
			$postVarsValues[$cpvl] = $_POST[$cpvl];
		}
		$auctionResult = Voorwerp::addAuction($postVarsValues['titel'], $postVarsValues['beschrijving'], $postVarsValues['startprijs'],
				$postVarsValues['betalingswijze'], $postVarsValues['betalinginstructie'], $postVarsValues['looptijd'],
				$postVarsValues['verzendkosten'], $postVarsValues['verzendinstructie'], $postVarsValues['rubriekId'], $postVarsValues['subrubriekId']);
		if ($auctionResult === true)
		{
			/* FILE UPLOAD */
			$newProductnumber = Voorwerp::getLatestPID();
			$imageName = 'pr_' . $newProductnumber . '_' . time() . '_' . rand(11111, 99999) . '.jpg';
			if (Bestand::addFile((float)$newProductnumber, $imageName) === false)
				echo 'Fout toevoegen foto naar database. Probeer het later nog eens.';

			if (!move_uploaded_file($_FILES['upfile']['tmp_name'], 'upload/products/' . $imageName))
			{
				echo 'Fout uploaden foto naar webserver. Probeer het later nog eens.';
			}
		}
		else
		{
			echo $auctionResult;
		}
	}
	if (isset($auctionResult) && $auctionResult === true)
		echo('De veiling is succesvol toevoegd aan de database.');

	if (!isset($auctionResult) || $auctionResult !== true)
	{
	?>
	<form id="contact" action="producttoevoegen.php" method="post" enctype="multipart/form-data">
		<label>
			Titel*<br/>
			<input type="text" name="titel"
					<?php if (array_key_exists('titel', $_POST)) echo('value="' . $_POST['titel'] . '"'); ?>
                   required/>
		</label>
		<br/>
		<input type="hidden" name="MAX_FILE_SIZE" value="3000000"/>
		<label for="file">Foto </label>
		<input type="file" name="upfile" id="file">
		<br/>
		<label>
			Beschrijving*<br/>
			<textarea rows="4" name="beschrijving" required
					><?php if (array_key_exists('beschrijving', $_POST)) echo($_POST['beschrijving']); ?></textarea>


		</label>
		<br/>
		<label>
			<select name="rubriekId" onchange="this.form.submit();">
				<option disabled <?php
				if (isset($_POST['rubriekId']) === false)
				{
					echo "selected";
				}
				?>
						>
					Selecteer een rubriek
				</option>
				<?php
				$rubrieken = Rubriek::getRubriekname();
				foreach ($rubrieken as $rubriek)
				{
					echo "<option value=\"" . $rubriek['Rubrieknummer'] . "\"";
					if (isset($_POST['rubriekId']))
					{
						if ($rubriek['Rubrieknummer'] === (int)$_POST['rubriekId'])
						{
							echo " selected";
						}
					}
					echo ">";
					echo $rubriek['Rubrieknaam'];
					echo "</option>";
				}
				?>
			</select>
		</label>
		<br/>
		<?php
		if (isset($_POST['rubriekId'])) {
		?>
		<label>
			<select name="subrubriekId">
				<option value="-1" selected>Selecteer een subrubriek</option>
				<?php
				$subrubrieken = Rubriek::getSubRubriekName($_POST['rubriekId']);
				foreach ($subrubrieken as $subrubriek)
				{
					echo "<option value=\"" . $subrubriek['rubrieknummer'] . "\">" . $subrubriek['Rubrieknaam'] . "</option>";
				}
				}?>
			</select>
		</label>
		<br/>
		<label>
			Startprijs*<br/>
			<input type="text" name="startprijs"
					<?php if (array_key_exists('startprijs', $_POST)) echo('value="' . $_POST['startprijs'] . '"'); ?>
                   required/>
		</label>
		<br/>
		<label>
			Betalingswijze*<br/>
			<select name="betalingswijze">
				<!-- TODO: Onthoud combobox selectie. -->
				<option value="creditcard" selected>creditcard</option>
				<option value="paypal">paypal</option>
				<option value="contant">contant</option>
				<option value="bank">bank</option>
			</select>
		</label>
		<br/>

		<label>
			Betalingsinstructie <br/>
			<textarea rows="4" name="betalinginstructie"
					><?php if (array_key_exists('betalinginstructie', $_POST)) echo($_POST['betalinginstructie']); ?></textarea>
		</label>

		<label>
			Looptijd*<br/>
			<select name="looptijd">
				<!-- TODO: Onthoud combobox selectie. -->
				<option value="0">1</option>
				<option value="1">3</option>
				<option value="2">5</option>
				<option value="3" selected>7</option>
				<option value="4">10</option>
			</select>

		</label>
		<br/>

		<label>
			Verzendkosten* <br/>
			<input type="text" name="verzendkosten"
					<?php if (array_key_exists('verzendkosten', $_POST)) echo('value="' . $_POST['verzendkosten'] . '"'); ?>
                   required/>
		</label>
		<br/>

		<label>
			Verzendinstructie <br/>
			<textarea rows="4" name="verzendinstructie"
					><?php if (array_key_exists('verzendinstructie', $_POST)) echo($_POST['verzendinstructie']); ?></textarea>

		</label>
		<br/><br/>
		<input type="submit" value="Verzenden"/>
	</form>
</div>
<?php
}
} else
{
	echo "Om een voorwerp ter verkoop aan te bieden, dient u zich te registreren als verkoper.";
}
} else
{
	echo "Gelieve eerst in te loggen voordat u een product toevoegt.";
}
include('footer.php'); ?>
