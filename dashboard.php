<?php
session_start();
include 'conexao.php'; // Assume que $conn está disponível aqui.

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$setor_id = $_SESSION['usuario_id'];
$mensagem_sucesso = '';
$mensagem_erro = '';

// Captura mensagens de feedback da sessão e limpa-as
if (isset($_SESSION['mensagem_sucesso'])) {
    $mensagem_sucesso = $_SESSION['mensagem_sucesso'];
    unset($_SESSION['mensagem_sucesso']);
}
if (isset($_SESSION['mensagem_erro'])) {
    $mensagem_erro = $_SESSION['mensagem_erro'];
    unset($_SESSION['mensagem_erro']);
}

// --- Lógica para listar as ações do setor (AGORA SEGURO COM PREPARED STATEMENTS) ---
$sql_acoes = "SELECT * FROM planos_acao WHERE responsavel_id = ?";
$stmt = $conn->prepare($sql_acoes);

if ($stmt === false) {
    $mensagem_erro = "Erro interno ao preparar a consulta de ações: " . $conn->error;
    $result_acoes = false;
} else {
    $stmt->bind_param("i", $setor_id);
    $stmt->execute();
    $result_acoes = $stmt->get_result();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Plano de Ação</title>
    <style>
        body { font-family: sans-serif; margin: 0; padding: 20px; }
        .menu-container { background-color: #007bff; padding: 15px; color: white; }
        .menu-container a { color: white; padding: 10px 15px; text-decoration: none; background-color: #0056b3; margin-right: 5px; border-radius: 4px; }
        .content { margin-top: 20px; }
        .success { color: green; background-color: #e8fadf; padding: 10px; border: 1px solid green; margin-bottom: 10px; }
        .error { color: red; background-color: #fceaea; padding: 10px; border: 1px solid red; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="menu-container">
        Bem-vindo, Setor de <?= htmlspecialchars($_SESSION['setor']); ?>! |

        <!-- NOVOS BOTÕES DE ACESSO -->
        <a href="cadastrar_objetivo.php">Cadastrar Objetivo</a> 
        <a href="cadastrar_acao.php">Cadastrar Ação</a>
        <a href="cadastrar_meta.php">Cadastrar Metas</a>
        <a href="form_acoes_semanais.php">Calendário Semanal</a>
        <a href="consultar_plano_acao.php">Imprimir Plano de Ação</a>
        <a href="calendario_filtrado.php">Imprimir o Calendário Semanal</a>
        <a href="editar_plano.php">Gerenciar Ações</a>
        
        
        <a href="logout.php">Sair</a>
    </div>

    <div class="content">
        <!-- Exibe mensagens de feedback -->
        <?php if ($mensagem_sucesso): ?>
            <div class="success"><?= htmlspecialchars($mensagem_sucesso); ?></div>
        <?php endif; ?>
        <?php if ($mensagem_erro): ?>
            <div class="error"><?= htmlspecialchars($mensagem_erro); ?></div>
        <?php endif; ?>

        <h3>Visão Geral das Ações</h3>
        
        <?php
        if ($result_acoes && $result_acoes->num_rows > 0) {
            echo "<p>Você tem ações pendentes. Use o menu acima para gerenciar.</p>";
        } elseif ($result_acoes) {
            echo "<p>Nenhuma ação encontrada para o seu setor.</p>";
        }
        ?>
    </div>
</body>
</html>
