<?php
include('functions.php');
htmlHeader('Product toevoegen', false);
?>
	<div id="content">
		<?php include('searchBar.php'); ?>
		<div class="RegistratieHeader">
			<h3>Mijn toegevoegde producten.</h3>
		</div>
		<br/>
		<table id="geplaatsteProducten">
			<tr>
				<th>Product</th>
				<th>Afbeelding</th>
				<th id="omschrijvingProduct">Omschrijving</th>
				<th>Vraagprijs</th>
				<th>Hoogste bod</th>
			</tr>
			<tr>
				<td>Mercedes SLS AMG</td>
				<td><a href="productpagina.php"><img src="upload/products/merc2.jpg" alt="Mercedes SLS AMG"></td>
				<!-- moet link naar pagina worden -->
				<td>Dit is een fantastische auto, maar je kan hem zeker weten toch niet betalen</td>
				<td>50000</td>
				<td>2000</td>
			</tr>

		</table>


	</div>
<?php include('footer.php'); ?>