<?php
include 'conexao.php';

header('Content-Type: application/json'); // Define o tipo de resposta como JSON

$response = [];

if (isset($_GET['objetivo_id'])) {
    $objetivo_id = filter_input(INPUT_GET, 'objetivo_id', FILTER_SANITIZE_NUMBER_INT);
    
    // Usamos prepared statements para segurança na busca
    $sql = "SELECT id, meta, descricao FROM metas WHERE objetivo_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $objetivo_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }

    $stmt->close();
}

$conn->close();

echo json_encode($response); // Retorna os dados em formato JSON para o JavaScript
?>
