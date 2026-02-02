<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar_objetivo'])) {
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];

    // Usando prepared statements para segurança
    $sql = "INSERT INTO objetivos_estrategicos (titulo, descricao) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    
    // Vincula parâmetros: 's' para string (título), 's' para string (descrição)
    $stmt->bind_param("ss", $titulo, $descricao);

    if ($stmt->execute()) {
        $_SESSION['mensagem_sucesso'] = "Objetivo estratégico cadastrado com sucesso!";
    } else {
        $_SESSION['mensagem_erro'] = "Erro ao cadastrar objetivo: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();

    // Redireciona de volta para o dashboard
    header("Location: dashboard.php");
    exit();
} else {
    // Se acessado diretamente sem POST, redireciona
    header("Location: dashboard.php");
    exit();
}
