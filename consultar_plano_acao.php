<?php
session_start();
include 'conexao.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit(); }

// Consulta SQL que une as três tabelas: planos_acao, objetivos_estrategicos e meta
$sql = "
    SELECT 
        pa.id AS acao_id,
        oe.titulo AS objetivo_titulo,
        m.titulo AS meta_titulo,
        pa.acao_descricao,
        pa.status,
        pa.percentual_execucao
    FROM 
        planos_acao pa
    JOIN 
        objetivos_estrategicos oe ON pa.objetivo_id = oe.id
    JOIN 
        meta m ON pa.meta_id = m.id
    ORDER BY 
        pa.id DESC
";

$result = $conn->query($sql);

if ($result === FALSE) {
    die("Erro na consulta SQL: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Consulta Consolidada do Plano de Ação</title>
    
    <style>
        body { font-family: sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f4f4f4; }
        
        /* Estilos específicos para impressão em formato A4 Paisagem */
        @media print {
            @page {
                size: A4 landscape; /* Define o formato da página para impressão */
                margin: 10mm; /* Margens reduzidas para aproveitar o espaço */
            }
            body {
                font-size: 10pt; /* Fonte menor para caber mais informação */
            }
            /* Esconde botões de navegação ao imprimir */
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <h2>Plano de Ação Consolidado</h2>
        <a href="dashboard.php">Voltar ao Dashboard</a> | 
        <button onclick="window.print()">Imprimir (A4 Paisagem)</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Ação</th>
                <th>Objetivo Estratégico</th>
                <th>Meta Vinculada</th>
                <th>Descrição da Ação</th>
                <th>Status</th>
                <th>%</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['acao_id']) ?></td>
                    <td><?= htmlspecialchars($row['objetivo_titulo']) ?></td>
                    <td><?= htmlspecialchars($row['meta_titulo']) ?></td>
                    <td><?= htmlspecialchars($row['acao_descricao']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= htmlspecialchars($row['percentual_execucao']) ?>%</td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">Nenhum registro encontrado. Verifique se as tabelas estão populadas e se os JOINs estão corretos.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
