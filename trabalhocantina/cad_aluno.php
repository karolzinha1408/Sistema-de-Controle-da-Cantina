<?php
include("conexao.php");
session_start();

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $alunoId = $_POST['alunoId'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $data = $_POST['datanascimento'];

    if (!empty($alunoId) && !empty($nome) && !empty($email) && !empty($data)) {

        // 1️⃣ Verifica se o ID já existe
        $check = $conn->prepare("SELECT * FROM Aluno WHERE AlunoId = ?");
        $check->bind_param("i", $alunoId);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $mensagem = "❌ Este ID já está cadastrado. Use outro.";
        } else {

            // 2️⃣ Insere no banco
            $query = $conn->prepare("INSERT INTO Aluno (AlunoId, Nome, Email, DataNascimento) VALUES (?, ?, ?, ?)");
            $query->bind_param("isss", $alunoId, $nome, $email, $data);

            if ($query->execute()) {
                // 3️⃣ Redireciona após cadastrar
                header("Location: index.php");
                exit;
            } else {
                $mensagem = "❌ Erro ao cadastrar aluno: " . $conn->error;
            }
        }

    } else {
        $mensagem = "Preencha todos os campos.";
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Cadastrar Aluno</title>
<style>
body {font-family: Arial; background: #f3f3f3;}
.container {max-width: 400px; margin: 80px auto; background: white; padding: 30px; border-radius: 10px;}
input, button {width: 100%; padding: 10px; margin-top: 10px;}
button {background: #27ae60; color: white; border: none; border-radius: 5px;}
p {color: red; font-weight: bold;}
</style>
</head>
<body>

<div class="container">
<h2>📘 Cadastro de Aluno</h2>

<form method="POST">
    <input type="number" name="alunoId" placeholder="ID do Aluno" required>
    <input type="text" name="nome" placeholder="Nome" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="date" name="datanascimento" required>

    <button type="submit">Cadastrar</button>
</form>

<?php if (!empty($mensagem)) echo "<p>$mensagem</p>"; ?>

</div>
</body>
</html>
