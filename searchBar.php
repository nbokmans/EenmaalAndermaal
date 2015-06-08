<div id="searchBar">
	<form action="producten<?php if(array_key_exists('subNumber', $_GET)) echo('subrubriek'); ?>.php" method="get">
		<!-- TODO: Verander misschien de naam van dit input veld. -->
		<div id="inputDiv">
			<div id="searchBarButton">
				<input type="submit" value="Zoeken">
			</div>
			<?php
			if(array_key_exists('subNumber', $_GET))
			{
				if (array_key_exists('rubrieknummer', $_GET))
					echo('<input type="hidden" name="rubrieknummer" value="' . $_GET['rubrieknummer'] . '"/>');
				if (array_key_exists('subNumber', $_GET))
					echo('<input type="hidden" name="subNumber" value="' . $_GET['subNumber'] . '"/>');
			}
			else if (array_key_exists('number', $_GET))
				echo('<input type="hidden" name="number" value="' . $_GET['number'] . '"/>');

			/* Als je min lengte woord aanpast pas dan ook aan in Voorwerp::getVoorwerpInRubriek. */
			?>
			<input type="text" name="searchProduct" placeholder="Zoeken (woorden korter dan 3 tekens worden genegeerd)"/>
		</div>
	</form>
	<img class="logo" src="logo.png" alt="logo">
</div>
