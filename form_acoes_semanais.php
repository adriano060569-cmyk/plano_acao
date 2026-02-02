<?php
session_start();
// Inclua seu arquivo de conexão e verifique o login, se necessário
include 'conexao.php'; 
if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit(); }

$mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar_semanal'])) {
    $data_evento = filter_input(INPUT_POST, 'data_evento', FILTER_SANITIZE_SPECIAL_CHARS);
    $nome_evento = filter_input(INPUT_POST, 'nome_evento', FILTER_SANITIZE_SPECIAL_CHARS);
    $publico_alvo = filter_input(INPUT_POST, 'publico_alvo', FILTER_SANITIZE_SPECIAL_CHARS);
    $setor_responsavel = filter_input(INPUT_POST, 'setor_responsavel', FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($data_evento) || empty($nome_evento) || empty($publico_alvo) || empty($setor_responsavel)) {
        $mensagem = "<p style='color:red;'>Erro: Preencha todos os campos.</p>";
    } else {
        $sql = "INSERT INTO acoes_semanais (data_evento, nome_evento, publico_alvo, setor_responsavel) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $data_evento, $nome_evento, $publico_alvo, $setor_responsavel);

        if ($stmt->execute()) {
            $mensagem = "<p style='color:green;'>Ação semanal cadastrada com sucesso!</p>";
        } else {
            $mensagem = "<p style='color:red;'>Erro ao cadastrar: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Ações Semanais</title>
    <style>
        .form-group { margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Formulário de Ações Semanais</h2>
    <?= $mensagem ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="data_evento">Data:</label>
            <input type="date" name="data_evento" required>
        </div>
        <div class="form-group">
            <label for="nome_evento">Nome do Evento:</label>
            <input type="text" name="nome_evento" required>
        </div>
        <div class="form-group">
            <label for="publico_alvo">Público-alvo:</label>
            <input type="text" name="publico_alvo" required>
        </div>
        <div class="form-group">
            <label for="setor_responsavel">Setor Responsável:</label>
            <input type="text" name="setor_responsavel" required>
        </div>
        <button type="submit" name="cadastrar_semanal">Cadastrar Ação</button>
    </form>
    <a href="dashboard.php">Página Inicial</a>
</body>
</html>
