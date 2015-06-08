<?php
include('functions.php');
require_once('php/Voorwerp.php');
htmlHeader('Home', true);
?>
<div id="content">
	<?php include('searchBar.php'); ?>
	<div class="camera_wrap camera_azure_skin" id="camera_wrap_1">
		<?php
		$products = Voorwerp::getPopularProducts();
		foreach ($products as $element)
		{
			$image = 'upload/products/' . Voorwerp::getImageLocation((float)$element['Voorwerpnummer']);
			if (!file_exists($image)) $image = 'upload/products/unavailable.jpg';
			?>
			<div data-src="<?php echo($image); ?>"
			     data-thumb="<?php echo($image); ?>">
				<div class="camera_caption fadeFromBottom">
					<a href="productpagina.php?productID=<?php echo($element['Voorwerpnummer']) ?>"
					   style="color: #FFF;">&raquo; <?php echo($element['Titel']); ?></a>
				</div>
			</div>
		<?php } ?>
	</div>
	&nbsp;
</div>
<?php include('footer.php'); ?>
