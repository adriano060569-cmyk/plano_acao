<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

include 'conexao.php';

// 1. Buscar Objetivos Estratégicos para preencher o primeiro SELECT
$sql_objetivos = "SELECT id, titulo FROM objetivos_estrategicos ORDER BY titulo ASC";
$result_objetivos = $conn->query($sql_objetivos);

// NOTA: Não buscamos as metas aqui inicialmente, elas serão carregadas via JavaScript/AJAX.

// Fechamento seguro da conexão no final do script
// A conexão $conn será usada no script AJAX abaixo, então mantemos aberta por enquanto.
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Ação - Plano de Ação</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        .conditional-field { margin-top: 10px; padding: 10px; background-color: #f9f9f9; border-left: 4px solid #ccc; }
        #loading_metas { display: none; color: blue; }
    </style>
</head>
<body>
    <h2>Cadastrar Nova Ação para <?= htmlspecialchars($_SESSION['setor']); ?></h2>
    
    <form action="processar_acao.php" method="POST" onchange="handleStatusChange()">
        
        <div class="form-group">
            <label for="objetivo_id">Objetivo Estratégico:</label>
            <select name="objetivo_id" id="objetivo_id" required onchange="fetchMetas()">
                <option value="">Selecione um Objetivo</option>
                <?php
                if ($result_objetivos->num_rows > 0) {
                    while($row = $result_objetivos->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['titulo']) . "</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="meta_id">Meta Associada:</label>
           <select name="meta_id" id="meta_id" required>
                <option value="">Selecione primeiro um Objetivo acima</option>
            </select>
            <span id="loading_metas">Carregando metas...</span>
        </div>

        <div class="form-group">
            <label for="acao_descricao">Descrição da Ação:</label>
            <textarea name="acao_descricao" id="acao_descricao" rows="3" required placeholder="Descreva a ação a ser executada..."></textarea>
        </div>
        
        <div class="form-group">
            <label for="status">Status da Ação:</label>
            <select name="status" id="status">
                <option value="em_andamento">Em Andamento</option>
                <option value="concluida">Concluída</option>
                <option value="reprogramada">Reprogramada</option>
            </select>
        </div>

        <!-- Área Condicional para Percentual (Em Andamento) -->
        <div id="div_percentual" class="conditional-field">
            <label for="percentual">Percentual Executado (%):</label>
            <input type="number" name="percentual" id="percentual" min="0" max="100" value="0">
        </div>

        <!-- Área Condicional para Justificativa e Data (Reprogramada) -->
        <div id="div_reprogramacao" class="conditional-field" style="display: none;">
            <label for="justificativa">Justificativa da Reprogramação:</label>
            <textarea name="justificativa" id="justificativa" rows="2"></textarea>
            <label for="data_repro">Nova Data:</label>
            <input type="date" name="data_repro" id="data_repro">
        </div>

        <div class="form-group">
            <button type="submit" name="cadastrar_acao">Cadastrar Ação</button>
        </div>
    
    </form>

    <script>
        // Função para mostrar/esconder campos condicionais (seu JS original)
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

        // Função AJAX para buscar metas relacionadas ao objetivo
        function fetchMetas() {
            const objetivoId = document.getElementById('objetivo_id').value;
            const metaSelect = document.getElementById('meta_id');
            const loadingSpan = document.getElementById('loading_metas');

            if (objetivoId === "") {
                metaSelect.innerHTML = '<option value="">Selecione primeiro um Objetivo acima</option>';
                return;
            }

            loadingSpan.style.display = 'inline';
            metaSelect.disabled = true; // Desabilita o select enquanto carrega

            // Usa Fetch API para comunicação assíncrona
            fetch('fetch_metas.php?objetivo_id=' + objetivoId)
                .then(response => response.json())
                .then(data => {
                    metaSelect.innerHTML = '<option value="">Selecione uma Meta</option>';
                    data.forEach(meta => {
                        // Assume que a tabela metas tem colunas 'id' e 'meta' ou 'titulo'
                        metaSelect.innerHTML += `<option value="${meta.id}">${meta.meta} - ${meta.descricao}</option>`;
                    });
                    loadingSpan.style.display = 'none';
                    metaSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Erro ao buscar metas:', error);
                    metaSelect.innerHTML = '<option value="">Erro ao carregar metas</option>';
                    loadingSpan.style.display = 'none';
                    metaSelect.disabled = false;
                });
        }
        
        // NOVO: Chama a função no carregamento da página para definir o estado inicial
        window.onload = function() {
            handleStatusChange();
            // Também chama fetchMetas caso um objetivo já esteja selecionado (ex: após erro de validação)
            if(document.getElementById('objetivo_id').value !== "") {
                fetchMetas();
            }
        }
    </script>
    <?php
    // Fechamento seguro da conexão no final do script
    $conn->close();
    ?>
</body>
</html>
