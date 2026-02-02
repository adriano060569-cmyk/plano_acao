<?php
session_start();
include 'conexao.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit(); }

// 1. Buscar Objetivos Estratégicos para o primeiro SELECT
$sql_objetivos = "SELECT id, titulo FROM objetivos_estrategicos ORDER BY titulo ASC";
$result_objetivos = $conn->query($sql_objetivos);
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Calendário Filtrado</title>
</head>
<body>
    <h2>Calendário de Ações por Objetivo/Meta/Ação</h2>

    <div>
        <label for="objetivo_id">Objetivo Estratégico:</label>
        <select name="objetivo_id" id="objetivo_id" onchange="fetchMetas(this.value)">
            <option value="">Selecione um Objetivo</option>
            <?php while($row = $result_objetivos->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['titulo']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    
    <div style="margin-top: 15px;">
        <label for="meta_id">Meta Vinculada:</label>
        <!-- Este select será preenchido via JavaScript -->
        <select name="meta_id" id="meta_id" onchange="fetchAcoes(this.value)">
            <option value="">Aguardando seleção do Objetivo</option>
        </select>
    </div>

    <div style="margin-top: 15px;">
        <label for="acao_id">Ação Cadastrada:</label>
        <!-- Este select será preenchido via JavaScript -->
        <select name="acao_id" id="acao_id" onchange="displayAcaoDetails(this.value)">
            <option value="">Aguardando seleção da Meta</option>
        </select>
    </div>

    <hr>
    <h3>Detalhes da Ação (Calendário Simples)</h3>
    <div id="detalhes_acao" style="border: 1px solid #ccc; padding: 20px; min-height: 100px;">
        <!-- Os detalhes da ação selecionada aparecerão aqui -->
        Selecione uma ação para ver os detalhes.
    </div>

    <script>
        // Função para buscar Metas via AJAX
        function fetchMetas(objetivoId) {
            if (!objetivoId) { return; }
            fetch('api_filtros.php?type=metas&id=' + objetivoId)
                .then(response => response.json())
                .then(data => {
                    const selectMeta = document.getElementById('meta_id');
                    selectMeta.innerHTML = '<option value="">Selecione uma Meta</option>';
                    data.forEach(item => {
                        selectMeta.innerHTML += `<option value="${item.id}">${item.descricao}</option>`;
                    });
                    document.getElementById('acao_id').innerHTML = '<option value="">Aguardando seleção da Meta</option>';
                    document.getElementById('detalhes_acao').innerText = 'Selecione uma ação para ver os detalhes.';
                });
        }

        // Função para buscar Ações via AJAX
        function fetchAcoes(metaId) {
            if (!metaId) { return; }
            // Nota: Sua tabela planos_acao usa meta_descricao como STRING, não ID.
            // A lógica aqui depende se você tem um ID de meta ou a descrição. 
            // Assumindo que meta_descricao é a coluna que você filtra na planos_acao.
            fetch('api_filtros.php?type=acoes&id=' + encodeURIComponent(metaId))
                .then(response => response.json())
                .then(data => {
                    const selectAcao = document.getElementById('acao_id');
                    selectAcao.innerHTML = '<option value="">Selecione uma Ação</option>';
                    data.forEach(item => {
                        selectAcao.innerHTML += `<option value="${item.id}">${item.descricao}</option>`;
                    });
                    document.getElementById('detalhes_acao').innerText = 'Selecione uma ação para ver os detalhes.';
                });
        }
        
        // Função para exibir detalhes (simulando um calendário simples)
        function displayAcaoDetails(acaoId) {
            if (!acaoId) { return; }
             fetch('api_filtros.php?type=detalhes&id=' + acaoId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('detalhes_acao').innerHTML = `
                        <h4>${data.acao_descricao}</h4>
                        <p>Status: ${data.status}</p>
                        <p>Percentual: ${data.percentual_execucao}%</p>
                        <p>Justificativa: ${data.justificativa_reprogramacao || 'N/A'}</p>
                    `;
                });
        }
    </script>
    <a href="dashboard.php">Voltar</a>
</body>
</html>
