<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensagem'])) {
    $_SESSION['mensagem'] = $_POST['mensagem'];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Mensagem não fornecida.']);
}
?>
