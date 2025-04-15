<?php
require '../vendor/autoload.php'; // Certifique-se de que o Dompdf está instalado via Composer
require '../Conexao.php';

use Dompdf\Dompdf;

// Recuperar dados com base na busca
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT nome, matricula, perfil, DATE_FORMAT(data_cadastro, '%d/%m/%Y %H:%i:%s') as data_cadastro, cadastrado_por 
          FROM usuarios 
          WHERE nome LIKE :search OR matricula LIKE :search";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gerar conteúdo HTML para o PDF
$html = '<h2 style="text-align: center;">Relatório de Usuários Cadastrados</h2>';
$html .= '<table border="1" width="100%" style="border-collapse: collapse; text-align: center; font-family: Arial, sans-serif;">';
$html .= '<thead>
            <tr style="background-color: #f2f2f2;">
                <th style="padding: 8px;">Nome</th>
                <th style="padding: 8px;">Matrícula</th>
                <th style="padding: 8px;">Perfil</th>
                <th style="padding: 8px;">Data de Cadastro</th>
                <th style="padding: 8px;">Cadastrado Por</th>
            </tr>
          </thead>';
$html .= '<tbody>';
foreach ($usuarios as $usuario) {
    $html .= '<tr>
                <td style="padding: 8px;">' . htmlspecialchars($usuario['nome']) . '</td>
                <td style="padding: 8px;">' . htmlspecialchars($usuario['matricula']) . '</td>
                <td style="padding: 8px;">' . htmlspecialchars($usuario['perfil']) . '</td>
                <td style="padding: 8px;">' . htmlspecialchars($usuario['data_cadastro']) . '</td>
                <td style="padding: 8px;">' . htmlspecialchars($usuario['cadastrado_por']) . '</td>
              </tr>';
}
$html .= '</tbody>';
$html .= '</table>';

// Inicializar Dompdf e gerar o PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // Configurado para paisagem
$dompdf->render();

// Enviar o PDF para o navegador
$dompdf->stream('Relatorio_Usuarios.pdf', ['Attachment' => false]);
exit;
