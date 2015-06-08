<?php
require_once('php/Voorwerp.php');

Voorwerp::closeEndedAuctions();

/*echo('START: ' . time() . '<br/>');
echo('RESULTAAT CLOSEENDED: ');
$result = Voorwerp::closeEndedAuctions();
if ($result === true) echo ('TRUE');
else if ($result === null) echo ('NULL');
else echo ('FALSE');
unset($result);

echo(' : EINDE RESULTAAT<br/>');
echo('END: ' . time());*/
