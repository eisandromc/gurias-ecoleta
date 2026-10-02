<?php
include_once '../conexao.php';
 
// Retrieve form data
$nome = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$senha = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING);
$cep = filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_STRING);
 

// Validate required fields
if ($nome && $email && $senha && $cep) {
    // Hash the password for security
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
 
    // Prepare SQL query to insert data into the database
    $query = "INSERT INTO usuarios (Nome, Email, Senha, CEP) VALUES (:nome, :email, :senha, :cep)";
   
    $stmt = $conexao->prepare($query);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':senha', $senhaHash);
    $stmt->bindParam(':cep', $cep);
 
 
    if ($stmt->execute()) {
        echo "Cadastro realizado com sucesso!";
    } else {
        echo "Erro ao realizar o cadastro. Tente novamente.";
    }
} else {
    echo "Preencha todos os campos obrigatórios.";
}
?>
 
 
 
 
 