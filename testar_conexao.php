<?php
// testar_conexao.php
$servername = "localhost";
$username = "root";     // Verifique se é 'root'
$password = "";         // Verifique se a senha está realmente vazia
$dbname = "plano_acao_db"; // Verifique se o nome do DB está correto

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("ERRO DE CONEXÃO: " . $conn->connect_error);
}

echo "SUCESSO: Conexão bem-sucedida com o banco de dados '$dbname'.";

// Você pode fechar a conexão aqui se quiser testar tudo
$conn->close();
