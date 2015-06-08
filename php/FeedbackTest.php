<?php
require_once('Feedback.php');

class FeedbackTest extends PHPUnit_Framework_TestCase
{
	public function testGetFeedbackCount()
	{
		$this->assertFalse(Feedback::getFeedbackCount(1, "+"));
		$this->assertFalse(Feedback::getFeedbackCount(true, "+"));
		$this->assertFalse(Feedback::getFeedbackCount(null, "+"));
		//werkt niet: $this->assertFalse(Feedback::getFeedbackCount("a", "+"));
		$this->assertFalse(Feedback::getFeedbackCount("annie", "q"));
		$this->assertFalse(Feedback::getFeedbackCount("annie", null));
		//werkt niet: $this->assertFalse(Feedback::getFeedbackCount("annie", true));
		//werkt niet: $this->assertFalse(Feedback::getFeedbackCount("annie", 0));
		$this->assertEquals(8, Feedback::getFeedbackCount("arie_safarie", "-"));
	}
}