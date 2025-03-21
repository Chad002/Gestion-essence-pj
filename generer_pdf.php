<?php
include("config.php");
require('fpdf/fpdf.php');

if (isset($_POST['numEntr']) && !empty($_POST['numEntr'])) {
    $numEntr = explode(',', $_POST['numEntr']);

    // Validation de la récupération des numéros d'entretiens cochés
    if (empty($numEntr)) {
        die("Aucun entretien sélectionné.");
    }

    // Initialisation du PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(0, 10, 'RECU D\'ENTRETIEN', 0, 1, 'C');
    $pdf->Ln(15);

    // Récupération des informations générales (Client, véhicule, etc.)
    $numEntrStr = implode("','", $numEntr);
    $query = "
        SELECT e.numEntr, e.immatriculation_voiture, e.nomClient, e.dateEntretien, s.service, s.prix, COUNT(e.numServ) AS quantite
        FROM ENTRETIEN e
        JOIN SERVICE s ON e.numServ = s.numServ
        WHERE e.numEntr IN ('$numEntrStr')
        GROUP BY e.numEntr, e.numServ
    ";

    $result = $conn->query($query);

    if (!$result || $result->num_rows == 0) {
        die("Aucun service trouvé pour les entretiens sélectionnés.");
    }

    // Informations générales pour le premier entretien sélectionné
    $row = $result->fetch_assoc();
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(50, 10, 'Numero : ' . htmlspecialchars($row['numEntr']), 0, 1);
    $pdf->Cell(50, 10, 'Date : ' . date('d/m/Y'), 0, 1); // Date d'aujourd'hui
    $pdf->Cell(50, 10, 'Client : ' . htmlspecialchars($row['nomClient']), 0, 1);
    $pdf->Cell(50, 10, 'Vehicule : ' . htmlspecialchars($row['immatriculation_voiture']), 0, 1);
    $pdf->Ln(10);

    // Réinitialisation du pointeur de résultat pour la lecture des services
    $result->data_seek(0);

    // Tableau des services regroupés
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(10, 10, '#', 1, 0, 'C');
    $pdf->Cell(80, 10, 'Service', 1, 0, 'C');
    $pdf->Cell(30, 10, 'Quantité', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Prix Total (Ar)', 1, 1, 'C');

    $pdf->SetFont('Arial', '', 12);
    $compteur = 1;

    while ($row = $result->fetch_assoc()) {
        $prix_total = $row['prix'] * $row['quantite'];
        $prix_affiche = number_format($prix_total, 0, '', ' '); // Format sans décimales

        $pdf->Cell(10, 10, $compteur++, 1, 0, 'C');
        $pdf->Cell(80, 10, htmlspecialchars($row['service']), 1, 0, 'C');
        $pdf->Cell(30, 10, $row['quantite'], 1, 0, 'C');
        $pdf->Cell(40, 10, $prix_affiche, 1, 1, 'C');
    }

    $pdf->Output('D', 'Entretien_Services.pdf');
}

?>
