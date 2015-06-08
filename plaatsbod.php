<?php
include('functions.php');
htmlHeader("BodPlaatsen", false);
require_once('php/Voorwerp.php');
?>
<div id="content">
	<?php
	if (isset($_POST['pid']) && isset($_POST['bod']))
	{
		$pid = $_POST['pid'];
		$bod = $_POST['bod'];
		$bod = str_replace(',', '.', $bod);
		$bod = strpos($bod, '.') !== false ? $bod : $bod .= '.00';
		if (preg_match("/^[1-9](?:[0-9]*)[.][0-9][0-9]$/", (String)$bod) === 1)
		{
			if ($GLOBALS['userLoggedIn'] === true)
			{
				if (Voorwerp::getField('Verkoper', $pid) !== $_SESSION['username'])
				{
					if (Voorwerp::isValidBid((float)$pid, floatval($bod)))
					{
						if (Voorwerp::confirmAuctionOwner($_SESSION['username'], (float)$pid) === false)
						{
							if (Voorwerp::addBidToAuction((float)$pid, floatval($bod)))
							{
								echo "Uw bod is met succes opgeslagen!";
							}
							else
							{
								echo "Er is iets misgegaan, probeert u het nog eens.";
							}
						}
						else
						{
							echo "U mag niet op uw eigen veiling bieden!";
						}
					}
					else
					{
						echo "Uw bod moet minstens het start bod bedragen als er nog geen biedingen zijn, of minimaal 5% hoger zijn dan het huidige bod!";
					}
				}
				else
				{
					echo "U kunt niet op uw eigen veiling bieden!";
				}
			}
			else
			{
				echo "U moet ingelogd zijn voordat u een bod kunt plaatsen!";
			}
		}
		else
		{
			echo "Ongeldig bod.";
		}
		?>
		<br>
		<button onclick="window.location='productpagina.php?productID=<?php echo $pid; ?>'">Terug</button>
	<?php
	}
	else
	{
		echo "Er is iets misgegaan, probeert u het nog eens!";
	}
	?>
</div>