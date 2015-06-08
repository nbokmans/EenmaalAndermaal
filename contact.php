<?php
include('functions.php');
htmlHeader('Contact', false);
?>
<div id="content">
	<?php include('searchBar.php'); ?>
	<img src="images/veilingshamer.jpg" alt="Velings hamer" style="width: 30%; float: right;"/>

	<h3>Contact</h3>

	<p>
		Onze klantenservice is van de hoogste kwaliteit.
		Hieronder zijn onze gegevens te vinden, maar u kunt ook het contactformulier invullen.
	</p>
	<address>
		E-mailadres: <a href="mailto:service@eenmaalandermaal.nl">service@eenmaalandermaal.nl</a>
		<br/>
		Telefoonnummer: <a href="tel:0123456789">0123-456789</a>
		<br/>
		Mobiel nummer: <a href="tel:0612345678">06-12345678</a>
	</address>

	<h4>Contactformulier</h4>

	<p>Velden die met een * gemarkeerd zijn zijn verplicht.</p>

	<form id="contact" action="contact.php" method="post">
		<!-- TODO: Stuur ook echt een mailtje. -->
		<label>
			Voor- en achternaam*<br/>
			<input type="text" name="name" required/>
		</label>
		<br/>
		<label>
			E-mailadres*<br/>
			<input type="email" name="email" required/>
		</label>
		<br/>
		<label>
			Telefoonnummer<br/>
			<input type="tel" name="tel"/>
		</label>
		<br/>
		<label>
			Uw vraag/opmerking*<br/>
			<textarea name="question" rows="5" required></textarea>
		</label>
		<br/>
		<input type="submit" value="Verzenden"/>
	</form>
</div>
<?php include('footer.php'); ?>
