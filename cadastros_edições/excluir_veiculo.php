<?php
// Inclui o arquivo de conexão com o banco de dados
include_once('../Conexao.php');  // Caminho ajustado para incluir Conexao.php corretamente

// Inicializa uma variável de mensagem
$message = '';

// Verificar se o ID foi passado na URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Consultar os dados do veículo pelo ID
        $sql = "SELECT * FROM veiculos WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $veiculo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$veiculo) {
            $message = "Veículo não encontrado.";
        } else {
            // Deletar o veículo
            $deleteSql = "DELETE FROM veiculos WHERE id = :id";
            $deleteStmt = $pdo->prepare($deleteSql);
            $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $deleteStmt->execute();

            // Mensagem de sucesso
            $message = "Veículo excluído com sucesso!";
        }
    } catch (PDOException $e) {
        // Mensagem de erro em caso de exceção
        $message = "Erro ao excluir o veículo: " . $e->getMessage();
    }

    // Redirecionar para a página listaTodosVeiculos.php com a mensagem
    header("Location: ../paginaAdm/listaTodosVeiculos.php?message=" . urlencode($message));
    exit();
} else {
    // Caso não haja ID, redireciona com mensagem de erro
    header("Location: ../paginaAdm/listaTodosVeiculos.php?message=" . urlencode("ID do veículo não informado."));
    exit();
}
?>
