<?php
session_start();
include_once '../conexao.php';

$usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING);
$senha = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING);

if ($usuario && $senha) {

    $query = "SELECT UsuarioID, Nome, Senha FROM usuarios WHERE Nome = :usuario LIMIT 1";
    $stmt = $conexao->prepare($query);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($senha, $user['Senha'])) {

            echo "Bem-vindo de volta!"; // ← AQUI

            $_SESSION['usuario_id'] = $user['UsuarioID'];
            $_SESSION['usuario_nome'] = $user['Nome'];

            header("Refresh: 1; URL=../inicio.html"); 
            exit;

        } else {
            echo "Senha incorreta.";
        }

    } else {
        echo "Erro ao realizar o login. Tente novamente.";
    }

} else {
    echo "Preencha todos os campos obrigatórios.";
}
?>
