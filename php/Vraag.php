<?php
require_once('DAO.php');

class Vraag
{
	/**
	 * Krijg een lijst van al de geheime vragen van de database.
	 *
	 * @return array|bool Geïndexeerde array met gegevens (vraagnummer en vraag) in associatieve array bij succes,
	 * <code>null</code> als er geen rows meer zijn, <code>false</code> bij een fout.
	 */
	public static function getSecretQuestions()
	{
		$dao = new DAO();
		if ($dao->openConnection() === false) return false;

		$stmt = $dao->queryOpen('select Vraagnummer, Vraag from Vraag');
		if ($stmt === false)
		{
			$dao->closeConnection();
			return false;
		}
		$vragenLijst = array();
		$index = 0;

		while ($row = $dao->fetchNextRowArrayAssoc($stmt))
		{
			if ($row === false)
			{
				$dao->queryClose($stmt);
				$dao->closeConnection();
				return false;
			}
			$vragenLijst[$index] = array('Vraagnummer' => $row['Vraagnummer'], 'Vraag' => $row['Vraag']);
			$index++;
		}
		$dao->queryClose($stmt);
		$dao->closeConnection();

		unset($dao);
		return $vragenLijst;
	}
}
