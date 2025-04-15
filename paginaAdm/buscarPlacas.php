<?php
include_once '../Conexao.php';

$termo = $_GET['term'] ?? '';

$sql = "SELECT DISTINCT placa FROM registro_veiculos WHERE placa LIKE :termo LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute([':termo' => "%$termo%"]);

echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
?>
