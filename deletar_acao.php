<?php
session_start();
include 'conexao.php'; // Inclui a conexão, assume que $conn está disponível.

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

// --- 1. Validação de Entrada mais Robusta ---
// Usa filter_input para garantir que o ID seja um inteiro válido (retorna NULL se inválido)
$acao_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$responsavel_id = $_SESSION['usuario_id'];

// Verifica se ambos os IDs são válidos antes de prosseguir com a exclusão
if ($acao_id && $responsavel_id) {

    // --- 2. Execução Segura da Exclusão ---
    $sql = "DELETE FROM planos_acao WHERE id = ? AND responsavel_id = ?";
    $stmt = $conn->prepare($sql);

    // Verifica se a preparação da consulta falhou
    if ($stmt === false) {
        $_SESSION['mensagem_erro'] = "Erro interno do servidor ao preparar a exclusão.";
    } else {
        $stmt->bind_param("ii", $acao_id, $responsavel_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $_SESSION['mensagem_sucesso'] = "Ação deletada com sucesso!";
            } else {
                // Isso cobre tanto ID inexistente quanto usuário sem permissão para deletar
                $_SESSION['mensagem_erro'] = "Ação não encontrada ou você não tem permissão para deletá-la.";
            }
        } else {
            $_SESSION['mensagem_erro'] = "Erro ao deletar ação: " . $stmt->error;
        }
        $stmt->close();
    }
    
} else {
    // Caso o ID não tenha sido fornecido ou seja inválido no GET
    $_SESSION['mensagem_erro'] = "ID de ação inválido ou não fornecido.";
}

// Fechar a conexão explicitamente
$conn->close();

// Redireciona de volta para a página que lista as ações
header("Location: editar_plano.php");
exit();
