<?php
include("conexao.php");

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Dados do fornecedor
    $fornecedor_nome = $_POST['fornecedor_nome'];
    $fornecedor_telefone = $_POST['fornecedor_telefone'];
    $fornecedor_empresa = $_POST['fornecedor_empresa'];
    $fornecedor_cnpj = $_POST['fornecedor_cnpj'];

    // Dados do produto
    $produto_codigo = $_POST['produto_codigo']; // <- NOVO
    $produto_nome = $_POST['produto_nome'];
    $produto_preco = $_POST['produto_preco'];
    $produto_quantidade = $_POST['produto_quantidade'];

    // 1️⃣ Inserir fornecedor
    $sqlFornecedor = $conn->prepare("
        INSERT INTO Fornecedor (Nome, Telefone, Empresa, CNPJ)
        VALUES (?, ?, ?, ?)
    ");
    $sqlFornecedor->bind_param("ssss", $fornecedor_nome, $fornecedor_telefone, $fornecedor_empresa, $fornecedor_cnpj);

    if ($sqlFornecedor->execute()) {
        
        // Pega o ID gerado automaticamente
        $fornecedorId = $conn->insert_id;

        // 2️⃣ Inserir produto com ProdutoCodigo informado
        $sqlProduto = $conn->prepare("
            INSERT INTO Produto (ProdutoCodigo, Nome, Preco, Quantidade)
            VALUES (?, ?, ?, ?)
        ");
        $sqlProduto->bind_param("isdi", $produto_codigo, $produto_nome, $produto_preco, $produto_quantidade);

        if ($sqlProduto->execute()) {
            $mensagem = "✅ Produto e fornecedor cadastrados com sucesso!";
        } else {
            $mensagem = "❌ Erro ao cadastrar produto: " . $conn->error;
        }
    } else {
        $mensagem = "❌ Erro ao cadastrar fornecedor: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Cadastrar Produto</title>
<style>
body {font-family: Arial, sans-serif; background: #f3f3f3;}
.container {max-width: 450px; margin: 40px auto; background: white; padding: 30px; border-radius: 12px;}
input, button {width: 100%; padding: 10px; margin: 8px 0;}
button {background: #27ae60; color: white; border: none; border-radius: 5px;}
.mensagem {text-align: center; color: red; font-weight: bold;}
h2 {text-align: center;}
</style>
</head>
<body>

<div class="container">
    <h2>📦 Cadastrar Produto</h2>

    <form method="POST">

        <h3>🧑‍💼 Dados do Fornecedor</h3>
        <input type="text" name="fornecedor_nome" placeholder="Nome do Fornecedor" required>
        <input type="text" name="fornecedor_telefone" placeholder="Telefone">
        <input type="text" name="fornecedor_empresa" placeholder="Empresa">
        <input type="text" name="fornecedor_cnpj" placeholder="CNPJ">

        <h3>📦 Dados do Produto</h3>
        <input type="number" name="produto_codigo" placeholder="Código do Produto" required> <!-- NOVO -->
        <input type="text" name="produto_nome" placeholder="Nome do Produto" required>
        <input type="number" step="0.01" name="produto_preco" placeholder="Preço" required>
        <input type="number" name="produto_quantidade" placeholder="Quantidade" required>

        <button type="submit">Cadastrar</button>
    </form>

    <?php if (!empty($mensagem)): ?>
        <p class="mensagem"><?= $mensagem ?></p>
    <?php endif; ?>
</div>

</body>
</html>
