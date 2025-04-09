<?php
include_once '../Conexao.php';

$termo = strtoupper($_GET['term'] ?? '');

$sql = "SELECT DISTINCT motorista_entrada FROM registro_veiculos WHERE UPPER(motorista_entrada) LIKE :termo LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute([':termo' => "%$termo%"]);

echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
?>
