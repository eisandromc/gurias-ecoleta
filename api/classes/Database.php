<?php

class Database
{
    private string $host = "localhost";
    private string $database = "ecoleta";
    private string $user = "root";
    private string $password = "";

    public function conectar(): PDO
    {
        try {

            $pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4",
                $this->user,
                $this->password
            );

            $pdo->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            $pdo->setAttribute(
                PDO::ATTR_DEFAULT_FETCH_MODE,
                PDO::FETCH_ASSOC
            );

            return $pdo;

        } catch (PDOException $e) {

            // Quem chama responde em JSON; o detalhe fica só no log.
            error_log("Erro na conexão com o banco: " . $e->getMessage());
            throw new RuntimeException("Erro na conexão com o banco.");
        }
    }
}