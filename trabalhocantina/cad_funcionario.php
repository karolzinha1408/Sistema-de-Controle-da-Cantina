<?php
include("conexao.php");
session_start();

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $funcionarioId = $_POST['funcionarioId'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];

    if (!empty($funcionarioId) && !empty($nome) && !empty($email)) {

        $query = $conn->prepare("INSERT INTO Funcionario (FuncionarioId, Nome, Email) VALUES (?, ?, ?)");
        $query->bind_param("iss", $funcionarioId, $nome, $email);

        if ($query->execute()) {

            // Após cadastrar funcionário → volta para página principal
            header("Location: index.php");
            exit;

        } else {
            $mensagem = "❌ Erro ao cadastrar funcionário: " . $conn->error;
        }

    } else {
        $mensagem = "⚠️ Preencha todos os campos.";
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Cadastrar Funcionário</title>
<style>
body {font-family: Arial; background: #f3f3f3;}
.container {max-width: 400px; margin: 80px auto; background: white; padding: 30px; border-radius: 10px;}
input, button {width: 100%; padding: 10px; margin-top: 10px;}
button {background: #2980b9; color: white; border: none; border-radius: 5px;}
</style>
</head>
<body>

<div class="container">
<h2>👨‍💼 Cadastro de Funcionário</h2>

<form method="POST">
    <input type="number" name="funcionarioId" placeholder="ID do Funcionário" required>
    <input type="text" name="nome" placeholder="Nome" required>
    <input type="email" name="email" placeholder="Email" required>

    <button type="submit">Cadastrar</button>
</form>

<?php if (!empty($mensagem)) echo "<p>$mensagem</p>"; ?>

</div>
</body>
</html>
