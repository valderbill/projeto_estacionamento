
// excluir_usuario.php
<?php
include('../Conexao.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
}

header('Location: ../paginaAdm/cadastrar_usuario.php');
exit;
?>
