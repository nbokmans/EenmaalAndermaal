<?php
include('functions.php');
htmlHeader('ProductPagina', false);
require_once('php/Feedback.php');
require_once('php/Gebruiker.php');
?>
<div id="content">
	<?php include('searchBar.php'); ?>
	<div class="RegistratieHeader">
		<h3>Mijn feedback</h3>
	</div>
	<?php
		if (isset($_GET['user']) && Gebruiker::usernameInUse($_GET['user']))
		{
			$seller = $_GET['user'];
			?>
			<p>
				<b>Gebruiker:</b> <?php
				echo "<a href=\"profiel.php?user=" . $seller . "\">";
				echo $seller;
				echo "</a>";?><br/><br/>
				<?php
				$totalFeedbackCount = Feedback::getFeedbackCount($seller, "all");
				$positiveFeedbackCount = Feedback::getFeedbackCount($seller, "+");
				$negativeFeedbackCount = Feedback::getFeedbackCount($seller, "-");
				$neutralFeedbackCount = Feedback::getFeedbackCount($seller, "0");
				$positivePercentage = ($positiveFeedbackCount / $totalFeedbackCount) * 100;
				$neutralPercentage = ($neutralFeedbackCount / $totalFeedbackCount) * 100;
				$negativePercentage = ($negativeFeedbackCount / $totalFeedbackCount) * 100;
				?>
				<b>Feedback:</b> <?php echo $totalFeedbackCount; ?> totaal.<br/><br/>
				<b>Positief:</b> <?php echo $positiveFeedbackCount;
				echo " (" . $positivePercentage . "%)" ?>      <br>
				<b>Neutraal:</b> <?php echo $neutralFeedbackCount;
				echo " (" . $neutralPercentage . "%)" ?>           <br>
				<b>Negatief:</b> <?php echo $negativeFeedbackCount;
				echo " (" . $negativePercentage . "%)" ?>              <br>
			</p>
		<?php
		}
		else
		{
			echo "Dit account bestaat niet!";
		}
	?>
</div>