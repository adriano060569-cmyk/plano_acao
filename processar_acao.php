<?php
session_start();
include 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar_acao'])) {
    // Captura os novos campos
    $objetivo_id = $_POST['objetivo_id'];
    $meta_id = $_POST['meta_id'];
    $acao_descricao = $_POST['acao_descricao'];
    $status = $_POST['status'];
    $responsavel_id = $_SESSION['usuario_id'];

    // Lógica condicional para campos opcionais
    // MySQL aceita 0 para INT/DECIMAL e NULL para DATE/TEXT
    $percentual = ($status == 'em_andamento') ? $_POST['percentual'] : 0;
    $justificativa = ($status == 'reprogramada' && !empty($_POST['justificativa'])) ? $_POST['justificativa'] : NULL;
    $data_repro = ($status == 'reprogramada' && !empty($_POST['data_repro'])) ? $_POST['data_repro'] : NULL;

    // Use prepared statements para segurança
    $sql = "INSERT INTO planos_acao (
                objetivo_id, meta_descricao, acao_descricao, responsavel_id, status, 
                percentual_execucao, justificativa_reprogramacao, data_reprogramacao
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    
    // CORREÇÃO: A string de tipos AGORA tem 8 caracteres, correspondendo aos 8 '?'
    // i = INT, s = STRING
    // Tipos: objetivo_id(i), meta(s), acao(s), responsavel_id(i), status(s), percentual(i), justificativa(s), data_repro(s)
    $stmt->bind_param("issiisss", 
        $objetivo_id, 
        $meta_id, 
        $acao_descricao, 
        $responsavel_id, 
        $status, 
        $percentual, 
        $justificativa,
        $data_repro
    );


    if ($stmt->execute()) {
        $_SESSION['mensagem_sucesso'] = "Nova ação cadastrada com sucesso!";
    } else {
        // Capture o erro exato para depuração
        $_SESSION['mensagem_erro'] = "Erro ao cadastrar ação: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();

    // Redireciona de volta para o dashboard para exibir o pop-up
    header("Location: dashboard.php");
    exit();
}
?>
