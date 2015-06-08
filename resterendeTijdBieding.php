<?php
/*
	function itemTijdResterend($postDate)
	{
		//$hours = $postDate * 24;
		//$

		$date =  millis($postDate);
		$remaining =  $date;

		$days_remaining = $remaining;

		$hours_remaining = ($days_remaining) / 2 * 24;

		echo ("Er zijn $days_remaining dagen en $hours_remaining uur over");
	}
	*/


function itemTijdResterend($postDate)
{
	$hours = $postDate * 24;
	$millis = $hours * 3600000;
	$currentTimeMillis = round(microtime(true) * 1000);
	$timeRemaining = formatMilliseconds((round(microtime(true) * 1000) + 5000) - $currentTimeMillis);
	echo(" $timeRemaining ");
}

function formatMilliseconds($milliseconds)
{
	$seconds = floor($milliseconds / 1000);
	$minutes = floor($seconds / 60);
	$hours = floor($minutes / 60);
	$milliseconds = $milliseconds % 1000;
	$seconds = $seconds % 60;
	$minutes = $minutes % 60;

	$format = '%u:%02u:%02u.%03u';
	$time = sprintf($format, $hours, $minutes, $seconds, $milliseconds);
	return rtrim($time, '0');
}
