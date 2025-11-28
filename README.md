# ***Sistema-de-Controle-da-Cantina***

## **Descrição:**

O Sistema de Controle da Cantina serve para controlar os pedidos dos clientes e controlar o dinheiro que entra e sai da cantina.
Para melhorar o trabalho da dona da cantina, será feito um  aplicativo para fazer reservas, os alunos, professores e outros 
funcionários poderão reservar seus lanches antes do recreio, e quando chegarem na cantina eles só precisarão pagar. Isso
poupará o tempo da dona da cantina e do cliente, que já poderá escolher o seu pedido antes do recreio e já deixar reservado.

## **Links:** 

http://localhost/phpmyadmin/index.php?route=/database/structure&db=cantina

http://localhost/trabalhocantina/home.php

http://localhost/trabalhocantina/cad_produto.php

## **Banco de Dados:** 

CREATE TABLE Produto (
  ProdutoCodigo INT PRIMARY KEY,
  Nome VARCHAR(20) NOT NULL,
  Quantidade INT NOT NULL,
  Preco DECIMAL(10,2) NOT NULL
);

CREATE TABLE Aluno (
  AlunoId INT PRIMARY KEY,
  Nome VARCHAR(120) NOT NULL,
  Email VARCHAR(120) NOT NULL,
  DataNascimento DATE
);

CREATE TABLE Funcionario (
  FuncionarioId INT PRIMARY KEY,
  Nome VARCHAR(120) NOT NULL,
  Email VARCHAR(120) NOT NULL
);

CREATE TABLE Fornecedor (
  FornecedorId INT PRIMARY KEY,
  Nome VARCHAR(120) NOT NULL,
  Telefone VARCHAR(20) NOT NULL,
  Empresa VARCHAR(120) NOT NULL,
  CNPJ VARCHAR(20) NOT NULL
);


CREATE TABLE Venda (
  VendaId INT PRIMARY KEY,
  DataVenda DATE,
  FuncionarioId INT,
  AlunoId INT,
  ValorTotal DECIMAL(10,2),
  
  CONSTRAINT fk_venda_funcionario
    FOREIGN KEY (FuncionarioId)
    REFERENCES Funcionario(FuncionarioId),

  CONSTRAINT fk_venda_aluno
    FOREIGN KEY (AlunoId)
    REFERENCES Aluno(AlunoId)
);

CREATE TABLE Item_Venda (
  ItemVendaId INT PRIMARY KEY,
  VendaId INT,
  ProdutoId INT,
  Quantidade INT NOT NULL,
  Preco DECIMAL(10,2) NOT NULL,
  
  CONSTRAINT fk_itemvenda_venda
    FOREIGN KEY (VendaId)
    REFERENCES Venda(VendaId),

  CONSTRAINT fk_itemvenda_produto
    FOREIGN KEY (ProdutoId)
    REFERENCES Produto(ProdutoCodigo)
);

CREATE TABLE Compra (
  CompraId INT PRIMARY KEY,
  DataCompra DATE,
  FuncionarioId INT,
  FornecedorId INT,
  ValorTotal DECIMAL(10,2) NOT NULL,
  
  CONSTRAINT fk_compra_funcionario
    FOREIGN KEY (FuncionarioId)
    REFERENCES Funcionario(FuncionarioId),

  CONSTRAINT fk_compra_fornecedor
    FOREIGN KEY (FornecedorId)
    REFERENCES Fornecedor(FornecedorId)
);

CREATE TABLE Item_Compra (
  ItemCompraId INT PRIMARY KEY,
  CompraId INT,
  ProdutoId INT,
  Quantidade INT NOT NULL,
  Preco DECIMAL(10,2),

  CONSTRAINT fk_itemcompra_compra
    FOREIGN KEY (CompraId)
    REFERENCES Compra(CompraId),

  CONSTRAINT fk_itemcompra_produto
    FOREIGN KEY (ProdutoId)
    REFERENCES Produto(ProdutoCodigo)
);

## **Autores e Colaboradores:** 💻

Karoline Vitória, 
Matheus Melo, 
Ana Clara.
