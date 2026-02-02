<?php
include 'conexao.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? 0;

$data = [];

if ($type === 'metas' && $id) {
    // Buscar metas relacionadas a um objetivo
    $stmt = $conn->prepare("SELECT DISTINCT meta_descricao AS descricao, id FROM planos_acao WHERE objetivo_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        // Usamos meta_descricao como ID temporário para o próximo filtro, já que não temos uma tabela de metas separada com IDs únicos.
        $data[] = ['id' => $row['descricao'], 'descricao' => $row['descricao']]; 
    }
    $stmt->close();
} elseif ($type === 'acoes' && $id) {
    // Buscar ações relacionadas a uma meta (filtrando por descricao da meta)
    $stmt = $conn->prepare("SELECT id, acao_descricao AS descricao FROM planos_acao WHERE meta_descricao = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $data[] = ['id' => $row['id'], 'descricao' => $row['descricao']];
    }
    $stmt->close();
} elseif ($type === 'detalhes' && $id) {
    // Buscar detalhes de uma ação específica
    $stmt = $conn->prepare("SELECT * FROM planos_acao WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
echo json_encode($data);
?>
