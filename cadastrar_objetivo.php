<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}
// Não precisa incluir conexao.php aqui, pois é só um formulário HTML
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Objetivo</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 15px; background-color: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Cadastrar Novo Objetivo Estratégico</h2>
    <form action="processar_objetivo.php" method="POST">
        <div class="form-group">
            <label for="titulo">Título do Objetivo:</label>
            <input type="text" name="titulo" id="titulo" required>
        </div>
        <div class="form-group">
            <label for="descricao">Descrição Detalhada:</label>
            <textarea name="descricao" id="descricao" rows="4"></textarea>
        </div>
        <button type="submit" name="cadastrar_objetivo">Cadastrar Objetivo</button>
    </form>
    <a href="dashboard.php">Voltar para o Dashboard</a>
</body>
</html>
