<?php
session_start();
// Garante que apenas usuários logados acessem esta página
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

include 'conexao.php';

// 1. Buscar Objetivos Estratégicos para preencher o SELECT
$sql_objetivos = "SELECT id, titulo FROM objetivos_estrategicos ORDER BY titulo ASC";
$result_objetivos = $conn->query($sql_objetivos);

// NOTA: Não fechamos a conexão aqui. Ela será fechada no final do arquivo
// ou automaticamente pelo PHP no término do script.

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Metas</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        .conditional-field { margin-top: 10px; padding: 10px; background-color: #f9f9f9; border-left: 4px solid #ccc; }
    </style>
</head>
<body>
    <h2>Cadastrar Metas para <?= htmlspecialchars($_SESSION['setor']); ?></h2>
    
    <!-- O formulário envia os dados para processar_acao.php -->
    <form action="processar_meta.php" method="POST" onchange="handleStatusChange()">
        
        <div class="form-group">
            <label for="objetivo_id">Objetivo Estratégico:</label>
            <select name="objetivo_id" id="objetivo_id" required>
                <?php
                if ($result_objetivos->num_rows > 0) {
                    while($row = $result_objetivos->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['titulo']) . "</option>";
                    }
                } else {
                    echo "<option value=''>Nenhum objetivo encontrado</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="meta">Meta Associada:</label>
            <textarea name="meta" id="meta" rows="3" required placeholder="Descreva a meta a ser atingida..."></textarea>
        </div>

        <div class="form-group">
            <button type="submit" name="cadastrar_acao">Cadastrar Meta</button>
        </div>
    
    </form>

     <script>
       
        // NOVO: Chama a função no carregamento da página para definir o estado inicial
        window.onload = handleStatusChange;
    </script>
    <?php
    // Fechamento seguro da conexão no final do script
    $conn->close();
    ?>
</body>
</html>
