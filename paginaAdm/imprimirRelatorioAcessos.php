<?php
require_once '../Conexao.php'; // Conexão com o banco de dados
require_once '../vendor/autoload.php'; // Carregar o DomPDF

use Dompdf\Dompdf;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$localizacao = isset($_GET['localizacao']) ? $_GET['localizacao'] : '';

// Buscar dados de "Acessos Liberados"
$query = "SELECT id, nome, matricula, localizacao, data_cadastro, usuario_cadastro FROM acessos_liberados";
$conditions = [];
if (!empty($search)) {
    $conditions[] = "(nome LIKE :search OR matricula LIKE :search)";
}
if (!empty($localizacao)) {
    $conditions[] = "localizacao = :localizacao";
}
if ($conditions) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
$query .= " ORDER BY nome ASC";

$stmt = $pdo->prepare($query);
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%");
}
if (!empty($localizacao)) {
    $stmt->bindValue(':localizacao', $localizacao);
}
$stmt->execute();
$acessos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gerar o HTML para o relatório com estilo de planilha
$html = '<h1 style="text-align: center;">Relatório de Acessos Liberados</h1>';
$html .= '<table border="1" width="100%" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">';
$html .= '<thead>';
$html .= '<tr style="background-color: #f2f2f2; text-align: left;">';
$html .= '<th style="padding: 8px; border: 1px solid #ddd;">Nome</th>';
$html .= '<th style="padding: 8px; border: 1px solid #ddd;">Matrícula ou OAB</th>';
$html .= '<th style="padding: 8px; border: 1px solid #ddd;">Localização</th>';
$html .= '<th style="padding: 8px; border: 1px solid #ddd;">Data de Cadastro</th>';
$html .= '<th style="padding: 8px; border: 1px solid #ddd;">Usuário</th>';
$html .= '</tr>';
$html .= '</thead>';
$html .= '<tbody>';
foreach ($acessos as $acesso) {
    $html .= '<tr>';
    $html .= '<td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($acesso['nome']) . '</td>';
    $html .= '<td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($acesso['matricula']) . '</td>';
    $html .= '<td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($acesso['localizacao']) . '</td>';
    $html .= '<td style="padding: 8px; border: 1px solid #ddd;">' . date('d/m/Y H:i:s', strtotime($acesso['data_cadastro'])) . '</td>';
    $html .= '<td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($acesso['usuario_cadastro']) . '</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';

// Gerar o PDF com DomPDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // Usar orientação paisagem para parecer mais com uma planilha
$dompdf->render();
$dompdf->stream('Relatorio_Acessos_Liberados.pdf', ['Attachment' => false]);
?>
