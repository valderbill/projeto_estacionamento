<?php
// Dados de conexão com o banco de dados
$host = 'localhost';
$dbname = 'projeto_estacionamento';  // Nome do banco de dados
$username = 'root';  // Seu nome de usuário do banco de dados
$password = '';  // Sua senha do banco de dados (deixe em branco se não houver senha)

try {
    // Criando a conexão PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Configurar o PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Se ocorrer um erro de conexão, exibe a mensagem
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
?>
