<?php require_once('php/Rubriek.php');
require_once('php/Voorwerp.php'); ?>
<link rel="stylesheet" type="text/css" href="stylesheet.css"/>
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico"/>
<!--[if IE]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
</head>
<body>
<nav class="navMenu">
	<a href="./">HOME</a>
	<a href="producten.php">PRODUCTEN</a>
	<a href="overons.php">OVER ONS</a>
	<a href="contact.php">CONTACT</a>
</nav>
<div class="navMenu" id="navMenuMargin">
	<a href="#">HOME</a>
	<a href="#">PRODUCTEN</a>
	<a href="#">OVER ONS</a>
	<a href="#">CONTACT</a>
</div>

<aside id="asideLeft">
	<header class="bucketHeader">
		<h3>Rubrieken</h3>
	</header>
	<aside class="bucketContent">
		<ul class="bucketList">
			<?php
			$rubrieken = Rubriek::getRubriekname();
			if ($rubrieken === false)
			echo('<li>Databasefout</li><li>Probeer het later nog eens</li>');
			else foreach ($rubrieken as $element)
			{
			?>
			<li><a href="producten.php?number=<?php echo $element['Rubrieknummer'] ?>">
					<?php
					echo('&raquo; ');
					echo($element['Rubrieknaam']);
					echo('</a></li>');
					echo("\n");
					}
					unset($rubrieken);
					?>
		</ul>
	</aside>
</aside>

<aside id="asideRight">
	<div class="bucketHeader">
		<h3><?php echo $GLOBALS['userLoggedIn'] ? "Mijn profiel" : "Inloggen" ?></h3>
	</div>
	<div class="bucketContent">
		<?php
		if ($GLOBALS['userLoggedIn'] === false)
			echo('<p>Er is een fout opgetreden, probeer het later nog eens.</p>');
		else if (isset($GLOBALS['userBanned']) && $GLOBALS['userBanned'] === true)
			echo '<p>Uw account is verbannen, u kunt geen gebruik maken van de website.</p>';
		else if ($GLOBALS['userLoggedIn'] === null)
		{
			if (array_key_exists('login', $_POST))
				echo('<p>Foutieve inloggegevens, probeer het nog eens.</p>');
			?>
			<form method="post">
				<label>
					Gebruikersnaam<br/>
					<input type="text" name="username" required/><br/>
				</label>
				<label>
					Wachtwoord (<a href="wachtwoordvergeten.php">Vergeten</a>)<br/>
					<input type="password" name="password" required/><br/>
				</label>
				<label>
					<input type="checkbox" name="rememberLogin" value="false"/>
					Onthouden<br/>
				</label>
				<input type="hidden" name="rememberLogin" value="true"/>
				<input type="hidden" name="login" value="true"/>
				<input type="submit" value="Inloggen"/>
				<a href="registreren.php">Registreren</a>
			</form>
		<?php
		}
		else if ($GLOBALS['userLoggedIn'] === true)
		{
			?>
			Welkom, <?php echo($_SESSION['username']); ?> <br>
			<a href="profiel.php?user=<?php echo(htmlspecialchars($_SESSION['username'])); ?>">
				» Profiel
			</a>            <br>
			<a href="mijnbiedingen.php?user=<?php echo(htmlspecialchars($_SESSION['username'])); ?>">
				» Mijn biedingen </a><br>
			<a href="mijnveilingen.php?user=<?php echo(htmlspecialchars($_SESSION['username'])); ?>">
				» Mijn veilingen </a><br>
			<a href="mijnfeedback.php?user=<?php echo(htmlspecialchars($_SESSION['username'])); ?>">
				» Mijn feedback </a><br>
			<?php
			$accDetails = Gebruiker::getAccountDetails($_SESSION['username']);
			$isSeller = $accDetails['IsVerkoper'];
			if ((boolean)$isSeller === true)
			{
				?>
				<a href="producttoevoegen.php">
					» Product toevoegen </a><br>   <?php } ?>

			<form method="post">
				<input type="hidden" name="logout" value="true"/>
				<input type="submit" value="Uitloggen"/>
			</form>
		<?php } ?>
	</div>
</aside>

<aside id="asideRightNieuwsteVeiling">
	<div class="bucketHeader" id="bucketHeaderMargin">
		<h3>Nieuwste veilingen</h3>
	</div>
	<div class="bucketContent" id="nieuwsteVeiling">
		<div class="scrollContent">
			<?php
			$voowerp = Voorwerp::getVoorwerpInRubriek(null, 4);
			if (isset($voorwerp) && $voorwerp !== null && sizeof($voorwerp) === 0)
			{
				echo "Er is een databasefout opgetreden, probeer het later nog eens.";
			}
			else
			{
				foreach ($voowerp as $element)
				{
					$image = 'upload/products/' . Voorwerp::getImageLocation((float)$element['voorwerpnummer']);
					if (!file_exists($image)) $image = 'upload/products/unavailable.jpg';

					?>
					<a href="productpagina.php?productID=<?php echo $element['voorwerpnummer'] ?>"><img
								src="<?php echo($image); ?>"
								alt="<?php echo $element['titel']; ?>"><br></a>
				<?php
				}
			}
			?>
		</div>
	</div>
</aside>
