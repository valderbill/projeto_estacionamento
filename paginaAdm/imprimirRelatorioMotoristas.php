<?php
require_once '../Conexao.php'; // Conexão com o banco de dados
require_once '../vendor/autoload.php'; // Carregar o DomPDF

use Dompdf\Dompdf;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Buscar dados de "Motoristas Oficiais"
$query = "SELECT nome, matricula, data_cadastro, usuario_cadastro FROM motoristas_oficiais";
if ($search) {
    $query .= " WHERE nome LIKE :search OR matricula LIKE :search";
}
$query .= " ORDER BY nome ASC";
$stmt = $pdo->prepare($query);
if ($search) {
    $stmt->bindValue(':search', "%$search%");
}
$stmt->execute();
$motoristas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gerar o HTML para o relatório no formato de planilha
$html = '<style>
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid black; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
         </style>';
$html .= '<h2 style="text-align: center;">Relatório de Motoristas Oficiais</h2>';
$html .= '<table>';
$html .= '<thead><tr><th>Nome</th><th>Matrícula</th><th>Data de Cadastro</th><th>Usuário</th></tr></thead>';
$html .= '<tbody>';
foreach ($motoristas as $motorista) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($motorista['nome']) . '</td>';
    $html .= '<td>' . htmlspecialchars($motorista['matricula']) . '</td>';
    $html .= '<td>' . date('d/m/Y H:i:s', strtotime($motorista['data_cadastro'])) . '</td>';
    $html .= '<td>' . htmlspecialchars($motorista['usuario_cadastro']) . '</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';

// Gerar o PDF com DomPDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // Orientação horizontal para parecer mais com uma planilha
$dompdf->render();
$dompdf->stream('Relatorio_Motoristas_Oficiais.pdf', ['Attachment' => false]);
?>
