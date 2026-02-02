<?php
$servername = "localhost";
$username = "root";       // Seu usuário do MySQL
$password = "";           // Sua senha do MySQL (vazio para XAMPP padrão)
$dbname = "plano_acao.db"; // O nome do banco de dados que você criou

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    // Em um ambiente de produção, você registraria o erro em um log
    // e mostraria uma mensagem genérica ao usuário.
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// Define o charset para garantir acentuação correta e compatibilidade moderna
// Use utf8mb4 se o seu DB suportar para melhor compatibilidade (geralmente suporta)
if (!$conn->set_charset("utf8mb4")) { 
    // Em caso de erro ao carregar o charset
    error_log("Erro ao carregar o conjunto de caracteres utf8mb4: " . $conn->error);
}

// O script termina aqui e a variável $conn está pronta para ser usada nos outros arquivos.

