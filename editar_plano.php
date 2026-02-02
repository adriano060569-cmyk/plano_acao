<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$setor_id = $_SESSION['usuario_id'];
$acao_para_editar = null;
$mensagem_sucesso = '';
$mensagem_erro = '';

// Captura e limpa mensagens de feedback da sessão
if (isset($_SESSION['mensagem_sucesso'])) {
    $mensagem_sucesso = $_SESSION['mensagem_sucesso'];
    unset($_SESSION['mensagem_sucesso']);
}
if (isset($_SESSION['mensagem_erro'])) {
    $mensagem_erro = $_SESSION['mensagem_erro'];
    unset($_SESSION['mensagem_erro']);
}

// --- Lógica para buscar a ação se um ID de edição for fornecido ---
if (isset($_GET['id_acao_editar'])) {
    $id_acao = filter_input(INPUT_GET, 'id_acao_editar', FILTER_SANITIZE_NUMBER_INT);

    $sql_edit = "SELECT * FROM planos_acao WHERE id = ? AND responsavel_id = ?";
    $stmt_edit = $conn->prepare($sql_edit);
    $stmt_edit->bind_param("ii", $id_acao, $setor_id);
    $stmt_edit->execute();
    $result_edit = $stmt_edit->get_result();

    if ($result_edit->num_rows == 1) {
        $acao_para_editar = $result_edit->fetch_assoc();
    } else {
        $mensagem_erro = "Ação não encontrada ou você não tem permissão para editá-la.";
    }
    $stmt_edit->close();
}

// --- Lógica para processar a ATUALIZAÇÃO (UPDATE) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['atualizar_acao'])) {
    
    // 2. Validação e Saneamento de Inputs
    $id_acao_update = filter_input(INPUT_POST, 'acao_id', FILTER_SANITIZE_NUMBER_INT);
    $meta_descricao = filter_input(INPUT_POST, 'meta_descricao', FILTER_SANITIZE_SPECIAL_CHARS);
    $acao_descricao = filter_input(INPUT_POST, 'acao_descricao', FILTER_SANITIZE_SPECIAL_CHARS);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if (empty($id_acao_update) || empty($meta_descricao) || empty($acao_descricao) || empty($status)) {
        $_SESSION['mensagem_erro'] = "Erro: Campos obrigatórios da ação vazios.";
        header("Location: editar_plano.php");
        exit();
    }

    // Lógica condicional para campos opcionais (com saneamento)
    $percentual = 0;
    if ($status == 'em_andamento' && isset($_POST['percentual'])) {
        $percentual = filter_input(INPUT_POST, 'percentual', FILTER_SANITIZE_NUMBER_INT);
    }
    
    $justificativa = NULL;
    $data_repro = NULL;
    if ($status == 'reprogramada') {
        $justificativa = filter_input(INPUT_POST, 'justificativa', FILTER_SANITIZE_SPECIAL_CHARS);
        $data_repro = filter_input(INPUT_POST, 'data_repro', FILTER_SANITIZE_SPECIAL_CHARS);
    }

    $sql_update = "UPDATE planos_acao SET 
                    meta_descricao=?, acao_descricao=?, status=?, percentual_execucao=?, 
                    justificativa_reprogramacao=?, data_reprogramacao=? 
                  WHERE id=? AND responsavel_id=?";
                  
    $stmt_update = $conn->prepare($sql_update);
    
    // Verificação de erro na preparação
    if ($stmt_update === false) {
        $_SESSION['mensagem_erro'] = "Erro interno ao preparar a atualização: " . $conn->error;
        header("Location: editar_plano.php");
        exit();
    }

    $stmt_update->bind_param("sssissii", 
        $meta_descricao, 
        $acao_descricao, 
        $status, 
        $percentual, 
        $justificativa, 
        $data_repro,
        $id_acao_update,
        $setor_id
    );

    if ($stmt_update->execute()) {
        $_SESSION['mensagem_sucesso'] = "Ação ID $id_acao_update atualizada com sucesso!";
    } else {
        $_SESSION['mensagem_erro'] = "Erro ao atualizar ação: " . $stmt_update->error;
    }
    $stmt_update->close();
    
    header("Location: editar_plano.php");
    exit();
}

// --- Lógica para listar as ações do setor (continuação) ---
$sql_acoes = "SELECT * FROM planos_acao WHERE responsavel_id = ?";
$stmt_acoes = $conn->prepare($sql_acoes);
$stmt_acoes->bind_param("i", $setor_id);
$stmt_acoes->execute();
$result_acoes = $stmt_acoes->get_result();
$stmt_acoes->close();
$conn->close();
?>

<!-- O HTML ABAIXO PRECISA SER ATUALIZADO PARA INCLUIR OS CAMPOS DO STATUS E O JS -->


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Plano de Ação</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        a.button { padding: 5px 10px; background-color: #ffc107; color: black; text-decoration: none; border-radius: 4px; }
        .edit-form { margin-top: 20px; padding: 20px; background-color: #e9e9e9; }
        .success { color: green; background-color: #e8fadf; padding: 10px; }
        .error { color: red; background-color: #fceaea; padding: 10px; }
        /* Adicione estilos para os campos de formulário */
        .form-group { margin-bottom: 10px; }
        input, textarea, select { width: 100%; padding: 8px; box-sizing: border-box; margin-top: 5px; }
    </style>
</head>
<body>
    <h2>Gerenciar Ações do Setor de <?= htmlspecialchars($_SESSION['setor']); ?></h2>
    <a href="dashboard.php">Voltar a Página Inicial</a>

    <!-- Exibe mensagens de feedback (Ponto 3) -->
    <?php if ($mensagem_sucesso): ?>
        <div class="success"><?= htmlspecialchars($mensagem_sucesso); ?></div>
    <?php endif; ?>
    <?php if ($mensagem_erro): ?>
        <div class="error"><?= htmlspecialchars($mensagem_erro); ?></div>
    <?php endif; ?>

    <!-- Formulário de Edição (aparece se uma ação foi selecionada) -->
    <?php if ($acao_para_editar): ?>
    <div class="edit-form">
        <h3>Editando Ação ID <?= $acao_para_editar['id']; ?></h3>
        <!-- Adicionado onchange="handleStatusChange()" para a funcionalidade JS -->
        <form action="editar_plano.php" method="POST" onchange="handleStatusChange()">
            <input type="hidden" name="acao_id" value="<?= $acao_para_editar['id']; ?>">
            
            <div class="form-group">
                <label for="meta_descricao">Meta:</label>
                <textarea name="meta_descricao" id="meta_descricao" rows="2" required><?= htmlspecialchars($acao_para_editar['meta_descricao']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="acao_descricao">Ação:</label>
                <textarea name="acao_descricao" id="acao_descricao" rows="2" required><?= htmlspecialchars($acao_para_editar['acao_descricao']); ?></textarea>
            </div>
            
            <!-- CAMPOS FALTANTES ADICIONADOS AQUI (Ponto 1) -->
            <div class="form-group">
                <label for="status">Status da Ação:</label>
                <select name="status" id="status" required>
                    <!-- Define a opção selecionada dinamicamente com base no valor atual do BD -->
                    <option value="em_andamento" <?= ($acao_para_editar['status'] == 'em_andamento') ? 'selected' : ''; ?>>Em Andamento</option>
                    <option value="concluida" <?= ($acao_para_editar['status'] == 'concluida') ? 'selected' : ''; ?>>Concluída</option>
                    <option value="reprogramada" <?= ($acao_para_editar['status'] == 'reprogramada') ? 'selected' : ''; ?>>Reprogramada</option>
                </select>
            </div>

            <!-- Área Condicional para Percentual (Em Andamento) -->
            <!-- Estilo inicial definido por JS abaixo -->
            <div id="div_percentual" class="form-group">
                <label for="percentual">Percentual Executado (%):</label>
                <input type="number" name="percentual" id="percentual" min="0" max="100" value="<?= htmlspecialchars($acao_para_editar['percentual_execucao']); ?>">
            </div>

            <!-- Área Condicional para Justificativa e Data (Reprogramada) -->
            <!-- Estilo inicial definido por JS abaixo -->
            <div id="div_reprogramacao" class="form-group">
                <label for="justificativa">Justificativa da Reprogramação:</label>
                <textarea name="justificativa" id="justificativa" rows="2"><?= htmlspecialchars($acao_para_editar['justificativa_reprogramacao']); ?></textarea>
                <label for="data_repro">Nova Data:</label>
                <input type="date" name="data_repro" id="data_repro" value="<?= htmlspecialchars($acao_para_editar['data_reprogramacao']); ?>">
            </div>
            
            <button type="submit" name="atualizar_acao">Salvar Alterações</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Tabela de Ações -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Descrição da Ação</th>
                <th>Status</th>
                <th>%</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_acoes->num_rows > 0): ?>
                <?php while($row = $result_acoes->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td><?= htmlspecialchars($row['acao_descricao']); ?></td>
                    <td><?= $row['status']; ?></td>
                    <td><?= $row['percentual_execucao']; ?>%</td>
                    <td>
                        <!-- Botão "mais" / Editar -->
                        <a href="editar_plano.php?id_acao_editar=<?= $row['id']; ?>" class="button">Editar</a>
                        <!-- Botão Deletar -->
                        <a href="deletar_acao.php?id=<?= $row['id']; ?>" class="button" onclick="return confirm('Tem certeza que deseja deletar esta ação?');">Deletar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">Nenhuma ação encontrada para seu setor.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
 <script>
        function handleStatusChange() {
            const status = document.getElementById('status').value;
            document.getElementById('div_percentual').style.display = 'none';
            document.getElementById('div_reprogramacao').style.display = 'none';

            if (status === 'em_andamento') {
                document.getElementById('div_percentual').style.display = 'block';
            } else if (status === 'reprogramada') {
                document.getElementById('div_reprogramacao').style.display = 'block';
            }
        }
        
        // Chama a função na carga da página se o formulário de edição estiver visível, 
        // para garantir que o estado inicial esteja correto com base nos dados do BD.
        window.onload = function() {
            if (document.querySelector('.edit-form')) {
                handleStatusChange();
            }
        }
    </script>
</body>
</html>
