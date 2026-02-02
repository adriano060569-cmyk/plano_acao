<?php
// Script temporário para gerar um hash. Execute isso no seu navegador.
$senhaQueVoceDigitou = '12345'; 
$hashGerado = password_hash($senhaQueVoceDigitou, PASSWORD_DEFAULT);
echo $hashGerado;
?>
