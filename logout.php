<?php
session_start();

// Destrói todas as variáveis de sessão
session_unset();

// Destrói a sessão em si
session_destroy();

// Redireciona para a página de login (index.php)
header("Location: index.php");
exit();
?>
