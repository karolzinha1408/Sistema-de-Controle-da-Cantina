<?php
include("conexao.php");

$mensagem = "";

// Inserir venda e itens
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['enviar_pedido'])) {
    $alunoId = $_POST['aluno_id'];
    $funcionarioId = $_POST['funcionario_id'];
    $produtos = $_POST['produtos']; // array de produtos [id => quantidade]

    if (!empty($alunoId) && !empty($funcionarioId) && !empty($produtos)) {
        $conn->begin_transaction();

        try {
            // 1️⃣ Calcular total
            $total = 0;
            foreach ($produtos as $id => $qtd) {
                $qtd = (int)$qtd;
                if ($qtd > 0) {
                    $p = $conn->query("SELECT Preco FROM Produto WHERE ProdutoCodigo=$id")->fetch_assoc();
                    $total += $p['Preco'] * $qtd;
                }
            }

            // 2️⃣ Inserir venda
            $stmt = $conn->prepare("INSERT INTO Venda (DataVenda, FuncionarioId, AlunoId, ValorTotal) VALUES (NOW(), ?, ?, ?)");
            $stmt->bind_param("iid", $funcionarioId, $alunoId, $total);
            $stmt->execute();
            $vendaId = $stmt->insert_id;
            $stmt->close();

            // 3️⃣ Inserir itens e atualizar estoque
            foreach ($produtos as $id => $qtd) {
                $qtd = (int)$qtd;
                if ($qtd > 0) {
                    $produto = $conn->query("SELECT Preco, Quantidade FROM Produto WHERE ProdutoCodigo=$id")->fetch_assoc();
                    $preco = $produto['Preco'];
                    $estoqueAtual = $produto['Quantidade'];

                    if ($estoqueAtual < $qtd) {
                        throw new Exception("Estoque insuficiente para o produto ID $id!");
                    }

                    // Inserir item
                    $stmtItem = $conn->prepare("INSERT INTO Item_Venda (VendaId, ProdutoId, Quantidade, Preco) VALUES (?, ?, ?, ?)");
                    $stmtItem->bind_param("iiid", $vendaId, $id, $qtd, $preco);
                    $stmtItem->execute();
                    $stmtItem->close();

                    // Atualizar estoque
                    $novoEstoque = $estoqueAtual - $qtd;
                    $conn->query("UPDATE Produto SET Quantidade=$novoEstoque WHERE ProdutoCodigo=$id");
                }
            }

            $conn->commit();
            $mensagem = "✅ Venda registrada e estoque atualizado com sucesso!";
        } catch (Exception $e) {
            $conn->rollback();
            $mensagem = "❌ Erro: " . $e->getMessage();
        }
    } else {
        $mensagem = "Preencha todos os campos corretamente.";
    }
}

// Buscar vendas
$vendas = $conn->query("
    SELECT v.VendaId, v.DataVenda, v.ValorTotal,
           a.Nome AS Aluno, f.Nome AS Funcionario
    FROM Venda v
    JOIN Aluno a ON v.AlunoId = a.AlunoId
    JOIN Funcionario f ON v.FuncionarioId = f.FuncionarioId
    ORDER BY v.DataVenda DESC
");
?>

<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Cantina Escolar - Sistema PHP</title>
<style>
body {font-family: Arial, sans-serif; background: #eef2f3; margin: 0; padding: 0;}
.container {max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 10px; margin-top: 30px;}
h1 {text-align: center;}
.mensagem {padding: 10px; background: #dfe6e9; margin-bottom: 15px; border-radius: 5px; text-align: center;}
input, select, button {margin: 8px 0; padding: 8px; width: 100%; border: 1px solid #ccc; border-radius: 5px;}
button {background: #00b894; color: white; font-weight: bold;}
button:hover {background: #01916d;}
table {width: 100%; border-collapse: collapse; margin-top: 20px;}
th, td {border: 1px solid #ccc; padding: 10px; text-align: left;}
th {background: #74b9ff; color: white;}
.produto-linha {display: flex; gap: 10px; align-items: center;}
.produto-linha input {width: 70px;}
</style>
</head>
<body>
<div class="container">
    <h1>🍽️ Sistema de Controle da Cantina</h1>

    <?php if ($mensagem): ?>
        <div class="mensagem"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Aluno:</label>
        <select name="aluno_id" required>
            <option value="">Selecione o aluno...</option>
            <?php
            $alunos = $conn->query("SELECT AlunoId, Nome FROM Aluno ORDER BY Nome");
            while ($a = $alunos->fetch_assoc()) {
                echo "<option value='{$a['AlunoId']}'>{$a['Nome']}</option>";
            }
            ?>
        </select>

        <label>Funcionário:</label>
        <select name="funcionario_id" required>
            <option value="">Selecione o funcionário...</option>
            <?php
            $funcs = $conn->query("SELECT FuncionarioId, Nome FROM Funcionario ORDER BY Nome");
            while ($f = $funcs->fetch_assoc()) {
                echo "<option value='{$f['FuncionarioId']}'>{$f['Nome']}</option>";
            }
            ?>
        </select>

        <h3>Produtos:</h3>
        <?php
        $produtos = $conn->query("SELECT ProdutoCodigo, Nome, Preco, Quantidade FROM Produto ORDER BY Nome");
        while ($p = $produtos->fetch_assoc()) {
            echo "
            <div class='produto-linha'>
                <label>{$p['Nome']} (R$ {$p['Preco']} | Estoque: {$p['Quantidade']}):</label>
                <input type='number' name='produtos[{$p['ProdutoCodigo']}]' min='0' max='{$p['Quantidade']}' value='0'>
            </div>";
        }
        ?>

        <button type="submit" name="enviar_pedido">Registrar Venda</button>
    </form>

    <h2>📋 Vendas Recentes</h2>
    <table>
        <tr><th>ID</th><th>Aluno</th><th>Funcionário</th><th>Data</th><th>Total</th></tr>
        <?php
        if ($vendas->num_rows > 0) {
            while ($v = $vendas->fetch_assoc()) {
                echo "<tr>
                        <td>#{$v['VendaId']}</td>
                        <td>{$v['Aluno']}</td>
                        <td>{$v['Funcionario']}</td>
                        <td>" . date('d/m/Y H:i', strtotime($v['DataVenda'])) . "</td>
                        <td>R$ " . number_format($v['ValorTotal'], 2, ',', '.') . "</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5' style='text-align:center;'>Nenhuma venda registrada.</td></tr>";
        }
        ?>
    </table>
</div>
</body>
</html>
