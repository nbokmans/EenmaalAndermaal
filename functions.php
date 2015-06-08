<?php
/**
 * Created by IntelliJ IDEA.
 * User: Melvin
 * Date: 13/05/2014
 * Time: 13:40
 */

/* HTML functions. */

/**
 * Include de header in de pagina.
 *
 * @param $title String Titel van de pagina.
 * @param $useGallery bool Image gallery gebruiken in deze pagina?
 */
function htmlHeader($title, $useGallery)
{
	require_once('header_functions.php');
	require_once('checkauctions.php');
	require_once('header_pretitle.php');
	echo('<title>' . $title . '</title>');
	if ($useGallery) include_once('header_gallery.php');
	require_once('header_posttitle.php');
}

/* Registration functions. */

/**
 * Stuur de gegeven registratiecode naar het gegeven email-adres.
 *
 * @param $email String e-mailadres.
 * @param $regCode String registratiecode.
 * @return bool <i>true</i> als de mail versturen gelukt is, anders <i>false</i>.
 */
function mailRegCode($email, $regCode)
{
	$headers = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'To: ' . $email . "\r\n";
	$headers .= 'From: noreply@eenmaalandermaal.nl' . "\r\n";
	$message = '
<!DOCTYPE HTML>
<html lang="nl" dir="ltr">
<head>
	<title>EenmaalAndermaal Registratiecode</title>
</head>
<body>
	<div style="background-color:#EBEBEB; padding:25px;	height: 100%;">
		<div style="background-color:#FAFAFA;
			padding:20px;
			text-align:center;
			border:1px solid #eeeeee;
			border-radius:10px;
			margin:0 auto;
			width:700px;
		">
			<div>
				<h1 style="
					font-size:35px;
					color: #606060;
					font-family:Arial,sans-serif;
				">Eenmaal Andermaal</h1>
			</div>
			<img style="width: 330px;" src="http://iproject35.icasites.nl/logo.png" alt="EenmaalAndermaal Logo"/>
			<hr style="
				margin-top: 35px;
				margin-bottom: 25px;
				border: 1px solid #606060;
			">
			<div>
				<h2 style="
					font-size:25px;
					color: #606060;
					font-family:Arial,sans-serif;
				">Uw registratie code:</h2>
				<div style="
					padding:5px;
					margin:0 auto;
					width:250px;
					text-align:center;
					color:white;
					font-weight:bold;
					font-size:35px;
					border-radius:10px;
					background-color:#3498DB;
				">
				' . $regCode . '
				</div>
			</div>
		</div>
	</div><p>'
			. date("Y-m-d H:i:s") .
			'</p></body>
			</html>
			';
	return (mail($email, 'EenmaalAndermaal Registratiecode', $message, $headers));
}

/**
 * Genereer een registratiecode.
 *
 * @param $email String e-mailadres.
 * @return String registratiecode.
 */
function generateRegCode($email)
{
	return substr(hash('md5', time() . $email, false), 0, 6);
}

/**
 * Stuur de gegeven registratiecode naar het gegeven email-adres.
 *
 * @param $email String e-mailadres.
 * @param $naam String naam van de gebruiker
 * @param $voorwerpnummer double van voorwerpnummer van de veiling
 * @param $titel String titel van de veiling
 * @return bool <i>true</i> als de mail versturen gelukt is, anders <i>false</i>.
 */
function mailNewHighestBid($email, $naam, $veilingnummer, $titel)
{
	$headers = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'To: ' . $email . "\r\n";
	$headers .= 'From: noreply@eenmaalandermaal.nl' . "\r\n";
	$message = '
<!DOCTYPE HTML>
<html lang="nl" dir="ltr">
<head>
	<title>EenmaalAndermaal - Hoogste bod</title>
</head>
<body>
	<div style="background-color:#EBEBEB; padding:25px;	height: 100%;">
		<div style="background-color:#FAFAFA;
			padding:20px;
			text-align:center;
			border:1px solid #eeeeee;
			border-radius:10px;
			margin:0 auto;
			width:700px;
		">
			<div>
				<h1 style="
					font-size:35px;
					color: #606060;
					font-family:Arial,sans-serif;
				">Eenmaal Andermaal</h1>
			</div>
			<img style="width: 330px;" src="http://iproject35.icasites.nl/logo.png" alt="EenmaalAndermaal Logo"/>
			<hr style="
				margin-top: 35px;
				margin-bottom: 25px;
				border: 1px solid #606060;
			">
			<div style="
					padding:5px;
					margin:0 auto;
					width:550px;
					text-align:left;
					color:#606060;
					
					font-size:14px;
					font-family:Arial,sans-serif;
					
				">
				<h2 style="font-size:18px;">U bent de nieuwe hoogste bieder!</h2>
				<div >
					<p><br>
						Geachte ' . $naam . ',<br><br>
						Onlangs heeft u een  bod geplaatst op de veiling "' . $titel . '", met als veilingnummer "' . $veilingnummer . '".<br><br>
						Het hoogste bod is ongeldig verklaard waardoor u momenteel het hoogste bod heeft.<br>
						<br>
						Gelieve verzoeken wij u contact met ons op te nemen indien u niet langer ge&iuml;ntereseerd bent in het artikel. Indien u wel ge&iuml;ntereseerd bent in het artikel hoeft u niets te doen.<br>
						<br>
						Met vriendelijke groet,<br>
						<br>
						EenmaalAndermaal Verkocht<br>
						www.iproject35.icasites.nl<br>
						info@iproject35.icasites.nl<br>

					</p>
				</div>
			</div>
		</div>
	</div><p>'
			. date("Y-m-d H:i:s") .
			'</p></body>
			</html>
			';
	return (mail($email, 'EenmaalAndermaal - Hoogste bod', $message, $headers));
}