<?php
require_once('php/Voorwerp.php');

$gesloten = Voorwerp::getAuctionTime(111054230562);
$datum = settype($gesloten, "DateTime");
?>

<script type="text/javascript">
	function countDown(secs,elem) {
		var element = document.getElementById(elem);
		element.innerHTML = secs;
		if(secs < 1) {
			clearTimeout(timer);
			element.innerHTML = '<h2>Veiling gesloten</h2>';

		}
		secs--;
		 var timer = 'hallo'+setTimeout('countDown('+secs+',"'+elem+'")',1000);

	}
</script>
<div id="status"></div>
<script type="text/javascript">
	var tijd = countDown(<?php echo $gesloten ?>,"status") / 10000;

</script>



