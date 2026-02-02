<?php
// Ativar exibição de erros para depuração (Remova em produção)
global $conn;
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'conexao.php';
session_start();

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar e sanear entradas logo no início
    $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_SPECIAL_CHARS);
    $senha_digitada = $_POST['senha']; // Não sanear a senha antes de verificar/hashear
    $acao = filter_input(INPUT_POST, 'status_submit', FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($login) || empty($senha_digitada) || empty($acao)) {
        $erro = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        if ($acao == 'login') {
            // --- LÓGICA DE LOGIN ---
            // Certifique-se de que o nome da tabela está correto (usuario ou usuarios)
            $sql = "SELECT id, login, senha, setor FROM usuario WHERE login = ?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt === false) { 
                $erro = "Erro interno na preparação da consulta SQL.";
            } else {
                $stmt->bind_param("s", $login);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($row = $result->fetch_assoc()) {
                    // Usuário encontrado, verificar senha
                    if (password_verify($senha_digitada, $row['senha'])) {
                        // Senha correta, iniciar sessão e redirecionar
                        $_SESSION['usuario_id'] = $row['id'];
                        $_SESSION['setor'] = $row['setor'];
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $erro = "Senha incorreta.";
                    }
                } else {
                    $erro = "Usuário não encontrado.";
                }
                $stmt->close();
            }

        } elseif ($acao == 'cadastro') {
            // --- LÓGICA DE CADASTRO ---
            $setor = filter_input(INPUT_POST, 'setor', FILTER_SANITIZE_SPECIAL_CHARS);

            if (empty($setor)) {
                $erro = "Por favor, informe o seu setor para o cadastro.";
            } else {
                // 1. Verificar se o login já existe
                $sql_check = "SELECT id FROM usuario WHERE login = ?";
                $stmt_check = $conn->prepare($sql_check);
                $stmt_check->bind_param("s", $login);
                $stmt_check->execute();
                $stmt_check->store_result();

                if ($stmt_check->num_rows > 0) {
                    $erro = "Este login já está em uso. Por favor, escolha outro.";
                } else {
                    // 2. Hash da senha antes de salvar no DB
                    $senha_hashed = password_hash($senha_digitada, PASSWORD_DEFAULT);

                    // 3. Inserir novo usuário
                    $sql_insert = "INSERT INTO usuario (login, senha, setor) VALUES (?, ?, ?)";
                    $stmt_insert = $conn->prepare($sql_insert);
                    $stmt_insert->bind_param("sss", $login, $senha_hashed, $setor);

                    if ($stmt_insert->execute()) {
                        $sucesso = "Cadastro realizado com sucesso! Você já pode fazer login.";
                    } else {
                        $erro = "Erro ao cadastrar usuário: " . $conn->error;
                    }
                    $stmt_insert->close();
                }
                $stmt_check->close();
            }
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <!-- Corrigido o viewport tag -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login / Cadastro - Plano de Ação</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f4f4; }
        .container { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 350px; }
        .form-group { margin-bottom: 10px; }
        input[type=text], input[type=password] { width: 100%; padding: 10px; box-sizing: border-box; }
        .error { color: red; }
        .success { color: green; }
        button { padding: 10px; width: 100%; margin-top: 10px; }
        .toggle-form { margin-top: 15px; text-align: center; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Acesso ao Sistema de Ações</h2>

        <?php if ($erro) echo "<p class='error'>".htmlspecialchars($erro)."</p>"; ?>
        <?php if ($sucesso) echo "<p class='success'>".htmlspecialchars($sucesso)."</p>"; ?>

        <!-- Formulário Único -->
        <form method="POST" name="Login" action="">
            <input type="hidden" name="status_submit" id="status_submit" value="login">

            <div class="form-group">
                <label for="login">Login (Usuário):</label>
                <input type="text" id="login" name="login" required>
            </div>
            
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>

            <!-- Campo de Setor: Removido 'required' do HTML inicial, será gerenciado pelo JS -->
            <div class="form-group" id="div_setor" style="display: none;">
                <label for="setor">Seu Setor:</label>
                <input type="text" id="setor" name="setor" placeholder="Ex: DEINF, DIAI, DIFEM, DPEE, DIPED, DEAC, SUPED">
            </div>
            
            <button type="submit" id="main_button">Entrar</button>
        </form>

        <div class="toggle-form">
            Ainda não tem cadastro? <a href="#" onclick="toggleForm(); return false;">Cadastre-se aqui</a>
        </div>
    </div>

    <script>
        function toggleForm() {
            const statusSubmit = document.getElementById('status_submit');
            const divSetor = document.getElementById('div_setor');
            divSetor.style = "style";
            const mainButton = document.getElementById('main_button');
            const linkText = document.querySelector('.toggle-form a');
            const inputSetor = document.getElementById('setor');

            if (statusSubmit.value === 'login') {
                // Muda para modo cadastro
                statusSubmit.value = 'cadastro';
                divSetor.style.display = 'block';
                mainButton.textContent = 'Cadastrar e Entrar';
                linkText.textContent = 'Já tenho cadastro, fazer login';
                inputSetor.setAttribute('required', 'required'); // Adiciona 'required' via JS
            } else {
                // Muda para modo login
                statusSubmit.value = 'login';
                divSetor.style.display = 'none';
                mainButton.textContent = 'Entrar';
                linkText.textContent = 'Ainda não tem cadastro? Cadastre-se aqui';
                inputSetor.removeAttribute('required'); // Remove 'required' via JS
            }
        }
    </script>
</body>
</html>
