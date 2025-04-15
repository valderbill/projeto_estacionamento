<?php
require_once '../Conexao.php';  // Conexão com o banco de dados

// Verificar se o ID foi passado na URL
if (isset($_GET['id'])) {
    $idMotorista = $_GET['id'];

    try {
        // Obter o caminho da foto do motorista
        $stmt = $pdo->prepare("SELECT foto FROM motoristas_oficiais WHERE id = :id");
        $stmt->bindParam(':id', $idMotorista);
        $stmt->execute();
        $motorista = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar se o motorista existe
        if ($motorista) {
            // Remover a foto do diretório de uploads, se existir
            if (file_exists($motorista['foto'])) {
                unlink($motorista['foto']);
            }

            // Excluir o motorista do banco de dados
            $sql = "DELETE FROM motoristas_oficiais WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $idMotorista);

            if ($stmt->execute()) {
                // Redirecionar de volta para a página de painel de motoristas
                echo "<script>alert('Motorista Excluído com sucesso!'); window.location='../paginaAdm/cadastrar_motorista_oficial.php';</script>";
            } else {
                echo "<script>alert('Erro ao excluir motorista!'); window.location='../paginaAdm/cadastrar_motorista_oficial.php';</script>";
            }
        } else {
            echo "<script>alert('Motorista não encontrado!'); window.location='../paginaAdm/cadastrar_motorista_oficial.php';</script>";
        }
    } catch (PDOException $e) {
        echo 'Erro ao excluir motorista: ' . $e->getMessage();
    }
} else {
    echo "<script>alert('ID do motorista não informado!'); window.location='../paginaAdm/cadastrar_motorista_oficial.php';</script>";
}
?>
