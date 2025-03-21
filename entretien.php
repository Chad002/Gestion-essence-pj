<?php
session_start();
include 'config.php';
require('fpdf/fpdf.php');

// Générer numEntr
function generernumEntr($conn) {
    $result = $conn->query("SELECT MAX(numEntr) AS dernier FROM ENTRETIEN");
    if (!$result) {
        die("Erreur SQL : " . $conn->error);
    }
    $row = $result->fetch_assoc();
    $dernier = $row['dernier'] ?? 'EN0000'; // Valeur par défaut si NULL

    // Extraire le numéro et l'incrémenter
    $num = intval(substr($dernier, 2)) + 1;

    // Retourner le numéro formaté
    return 'EN' . str_pad($num, 4, '0', STR_PAD_LEFT);
}

// Générer PDF
if (isset($_GET['pdf'])) {
    genererPDF($conn, $_GET['pdf']);
    exit();
}

// Générer PDF
function genererPDF($conn, $numEntr) {
    // Requête SQL regroupant les services et comptant leur occurrence
    $result = $conn->query("
        SELECT e.*, s.service, s.prix, COUNT(e.numServ) AS quantite
        FROM ENTRETIEN e
        JOIN SERVICE s ON e.numServ = s.numServ
        WHERE e.numEntr = '$numEntr'
        GROUP BY e.numServ
    ");
    
    if (!$result) {
        die("Erreur SQL : " . $conn->error);
    }

    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Entête
    $pdf->SetFont('Arial','B',18);
    $pdf->Cell(0,10,'RECU D\'ENTRETIEN',0,1,'C');
    $pdf->Ln(15);

    // Informations générales
    $row = $result->fetch_assoc(); // Récupération des infos générales
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(50,10,'Numero : ' . ($row['numEntr'] ?? 'N/A'),0,1);
    $pdf->Cell(50,10,'Date : ' . date('d/m/Y', strtotime($row['dateEntretien'] ?? '0000-00-00')),0,1);
    $pdf->Cell(50,10,'Client : ' . ($row['nomClient'] ?? 'N/A'),0,1);
    $pdf->Cell(50,10,'Vehicule : ' . ($row['immatriculation_voiture'] ?? 'N/A'),0,1);

    $pdf->Ln(10); // Espacement avant le tableau

    // **Création du tableau**
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(10,10,'#',1,0,'C'); // Numéro auto-incrémenté
    $pdf->Cell(80,10,'Services',1,0,'C');
    $pdf->Cell(30,10,'Quantité',1,0,'C');
    $pdf->Cell(40,10,'Prix Total (Ar)',1,1,'C');


    $pdf->SetFont('Arial','',12);
    $compteur = 1;
    

    do {
        $prix_total = $row['prix'] * $row['quantite'];

        // Vérification pour afficher correctement les entiers ou les décimales
        $prix_affiche = (intval($prix_total) == $prix_total) 
                    ? number_format($prix_total, 0, '', ' ')   // Affichage sans décimales
                    : number_format($prix_total, 2, '.', ' '); // Affichage avec deux décimales

        $pdf->Cell(10, 10, $compteur++, 1, 0, 'C');
        $pdf->Cell(80, 10, $row['service'], 1, 0, 'C');
        $pdf->Cell(30, 10, $row['quantite'], 1, 0, 'C');
        $pdf->Cell(40, 10, $prix_affiche, 1, 1, 'C');

    } while ($row = $result->fetch_assoc());





    $pdf->Output('D', 'Entretien_'.$numEntr.'.pdf');
}

// Ajouter
if (isset($_POST['ajouter'])) {
    $numEntr = generernumEntr($conn);
    $stmt = $conn->prepare("INSERT INTO ENTRETIEN VALUES (?,?,?,?,?)");
    if (!$stmt) {
        die("Erreur SQL : " . $conn->error);
    }
    $stmt->bind_param("sssss", 
        $numEntr,
        $_POST['numServ'],
        $_POST['immat'],
        $_POST['client'],
        $_POST['date']
    );
    
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Entretien ajouté !';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Erreur : ' . $stmt->error;
        $_SESSION['message_type'] = 'error';
    }
    header("Location: entretien.php");
    exit();
}

// Modifier
if (isset($_POST['modifier'])) {
    $stmt = $conn->prepare("UPDATE ENTRETIEN SET 
        numServ = ?,
        immatriculation_voiture = ?,
        nomClient = ?,
        dateEntretien = ?
        WHERE numEntr = ?");
    if (!$stmt) {
        die("Erreur SQL : " . $conn->error);
    }
    $stmt->bind_param("sssss", 
        $_POST['numServ'],
        $_POST['immat'],
        $_POST['client'],
        $_POST['date'],
        $_POST['id']
    );
    $stmt->execute();
    header("Location: entretien.php");
    exit();
}

// Supprimer
if (isset($_GET['supprimer'])) {
    $conn->query("DELETE FROM ENTRETIEN WHERE numEntr='".$_GET['supprimer']."'");
    $_SESSION['message'] = 'Entretien supprimé !';
    $_SESSION['message_type'] = 'success';
    header("Location: entretien.php");
    exit();
}

// Récupérer données
$search = $_GET['search'] ?? '';
$sql = "SELECT e.*, s.service 
        FROM ENTRETIEN e 
        LEFT JOIN SERVICE s ON e.numServ = s.numServ
        WHERE e.numEntr LIKE '%$search%' 
        OR e.nomClient LIKE '%$search%' 
        OR e.immatriculation_voiture LIKE '%$search%'";

$result = $conn->query($sql);
if (!$result) {
    die("Erreur SQL : " . $conn->error);
}

$services = $conn->query("SELECT * FROM SERVICE");
if (!$services) {
    die("Erreur SQL : " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Entretiens</title>
    <style>
        /* Styles identiques à service.php */
        * { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body { 
            display: flex; 
            background: whitesmoke; 
            height: 100vh; 
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: linear-gradient(to right, #3498db, #2980b9);
            padding: 20px;
            color: white;
            position: fixed;
            overflow-y: auto;
        }

        .sidebar h2 {
            text-align: center; 
            margin-bottom: 60px; 
            font-size: 27px; 
        }

        .sidebar ul {
            list-style: none; 
            padding: 0; 
            text-align: center; 
            cursor: pointer; 
        }

        .sidebar ul li { 
            padding: 15px; 
            border-bottom: 2px solid rgba(255, 255, 255, 0.2); 
            font-weight: bold; 
            transition: 0.4s; 
        }

        .sidebar ul li a { 
            color: white; 
            text-decoration: none; 
            display: block; 
        }

        .sidebar ul li:hover { 
            background: rgba(255, 255, 255, 0.4); 
            transform: scale(1.1); 
        }
      .main-content { 
            margin-left: 250px;
            padding: 30px;
            width: calc(100% - 250px);
        }

        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .search-bar { 
            display: flex; 
            gap: 10px; 
            margin-bottom: 20px;
            align-items: center;
        }

        .search-input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 300px;
            font-size: 16px;
        }

        .add-btn {
            background: linear-gradient(to right, #3498db, #2980b9);
            color: white; 
            padding: 10px 20px;
            border: none; 
            cursor: pointer;
            border-radius: 5px; 
            font-size: 16px;
            transition: 0.4s;
        }
        .add-btn:hover {
            transform: scale(0.9);
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            background: white;
        }

        th, td { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: center; 
        }

        th { 
            background: black; 
            color: white; 
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:nth-child(odd) {
            background: #e8e8e8;
        }


        .modal {
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%;
            background: rgba(0,0,0,0.5); 
            justify-content: center; 
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 25px; 
            border-radius: 10px; 
            width: 400px; 
        }

        .edit-btn, .delete-btn {
            cursor: pointer; padding: 8px;
            border: none; color: white;
            border-radius: 5px; margin: 8px;
            transition: 0.4s;
        }

        .edit-btn { background: linear-gradient(to right, #f1c40f, #f39c12); }
        .delete-btn { background: linear-gradient(to right, #e74c3c, #c0392b); }
        .edit-btn:hover, .delete-btn:hover { transform: scale(0.9); }

        .pdf-btn{
            background: #17a2b8; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer;
            transition: 0.4s;
        }
        .pdf-btn:hover{
            transform: scale(0.9); 
        }
        .generate-pdf-btn{
            cursor: pointer; padding: 8px;
            border: none; color: blue;
            border-radius: 5px; margin: 8px;
            transition: 0.4s;
            border: 2px solid violet;
            background: transparent;
        }
        .generate-pdf-btn:hover{
            transform: scale(0.9); 
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Station Essence</h2>
        <ul>
            <li><a href="index.php">Tableau de bord</a></li>
            <li><a href="produits.php">PRODUIT</a></li>
            <li><a href="entree.php">ENTREE</a></li>
            <li><a href="achat.php">ACHAT</a></li>
            <li><a href="service.php">SERVICE</a></li>
            <li><a href="entretien.php">ENTRETIEN</a></li>
            <li><a href="statistiques.php">Statistiques</a></li>
        </ul>
    </div>

    <div class="main-content">
        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message_type'] ?>">
                <?= $_SESSION['message'] ?>
            </div>
            <?php 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

        <h1>Gestion des Entretiens</h1><br><br>
        
        <div class="search-bar">
            <button class="add-btn" onclick="openModal('add')">+ Ajouter Entretien</button>
            <form method="GET" style="margin-left: auto;">
                <input type="text" 
                       name="search" 
                       class="search-input"
                       placeholder="Rechercher..."
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="add-btn">🔍 Rechercher</button>
                <button type="button" onclick="window.location.href='entretien.php'" class="add-btn">🔄 Réinitialiser</button>
            </form>
        </div>
        <table>
    <tr>
        <th><input type="checkbox" onclick="toggleAll(this)" /></th>
        <th>Numero</th>
        <th>Services</th>
        <th>Immatriculations</th>
        <th>Clients</th>
        <th>Dates</th>
        <th>Actions</th>
    </tr>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td>
                    <!-- Mettre la checkbox dans un formulaire indépendant -->
                    <form id="checkbox-form">
                        <input type="checkbox" name="numEntr[]" value="<?= htmlspecialchars($row['numEntr'] ?? '') ?>"
                            data-client="<?= htmlspecialchars($row['nomClient'] ?? '') ?>"
                            onclick="verifierSelection(this)" />
                    </form>
                </td>
                <td><?= htmlspecialchars($row['numEntr'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['service'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['immatriculation_voiture'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['nomClient'] ?? 'N/A') ?></td>
                <td><?= date('d/m/Y', strtotime($row['dateEntretien'] ?? '0000-00-00')) ?></td>
                <td>
                    <button onclick="openEditModal(
                        '<?= $row['numEntr'] ?? '' ?>',
                        '<?= $row['numServ'] ?? '' ?>',
                        '<?= $row['immatriculation_voiture'] ?? '' ?>',
                        '<?= addslashes($row['nomClient'] ?? '') ?>',
                        '<?= $row['dateEntretien'] ?? '' ?>'
                    )" class="edit-btn">✏️</button>
                    <button onclick="confirmDelete('<?= $row['numEntr'] ?>')" class="delete-btn">🗑</button>
                    <button onclick="window.location='entretien.php?pdf=<?= $row['numEntr'] ?? '' ?>'" class="pdf-btn">
                        PDF
                    </button>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="7">Aucun entretien trouvé.</td>
        </tr>
    <?php endif; ?>
</table>

<!-- Formulaire dédié pour la génération du PDF -->
<form method="post" action="generer_pdf.php" id="pdf-form">
    <input type="hidden" name="numEntr" id="selected-entries" />
    <button type="submit" class="generate-pdf-btn" style="margin-top: 10px;">Générer PDF pour les services cochés</button>
</form>

<script>
    function toggleAll(source) {
        const checkboxes = document.querySelectorAll('input[name="numEntr[]"]');
        checkboxes.forEach(cb => cb.checked = source.checked);
        verifierSelection(source);
    }

    function verifierSelection(checkbox) {
        const checkboxes = document.querySelectorAll('input[name="numEntr[]"]');
        const selectedEntries = [];
        const clientSelectionne = checkbox.getAttribute('data-client');

        checkboxes.forEach(cb => {
            if (cb !== checkbox && cb.getAttribute('data-client') !== clientSelectionne) {
                cb.checked = false;
                cb.disabled = checkbox.checked;
            } else {
                cb.disabled = false;
            }

            if (cb.checked) {
                selectedEntries.push(cb.value);
            }
        });

        document.getElementById('selected-entries').value = selectedEntries.join(',');
    }
</script>
    </div>

    <!-- Modale Ajout -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <h2>Nouvel Entretien</h2>
            <form method="POST">
                <div style="margin-bottom: 15px;">
                    <label>Service :</label>
                    <select name="numServ" required style="width: 100%; padding: 8px;">
                        <?php while ($s = $services->fetch_assoc()): ?>
                            <option value="<?= $s['numServ'] ?>"><?= $s['service'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label>Immatriculation :</label>
                    <input type="text" name="immat" required style="width: 100%; padding: 8px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label>Client :</label>
                    <input type="text" name="client" required style="width: 100%; padding: 8px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label>Date :</label>
                    <input type="date" name="date" required style="width: 100%; padding: 8px;">
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="ajouter" class="add-btn">Enregistrer</button>
                    <button type="button" onclick="closeModal('add')" class="add-btn">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modale Édition -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <h2>Modifier Entretien</h2>
            <form method="POST">
                <input type="hidden" name="id" id="editId">
                
                <div style="margin-bottom: 15px;">
                    <label>Service :</label>
                    <select name="numServ" id="editServ" required style="width: 100%; padding: 8px;">
                        <?php $services->data_seek(0); ?>
                        <?php while ($s = $services->fetch_assoc()): ?>
                            <option value="<?= $s['numServ'] ?>"><?= $s['service'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div style="margin-bottom: 15px;">
                    <label>Immatriculation :</label>
                    <input type="text" name="immat" id="editImmat" required style="width: 100%; padding: 8px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label>Client :</label>
                    <input type="text" name="client" id="editClient" required style="width: 100%; padding: 8px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label>Date :</label>
                    <input type="date" name="date" id="editDate" required style="width: 100%; padding: 8px;">
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="modifier" class="add-btn">Enregistrer</button>
                    <button type="button" onclick="closeModal('edit')" class="add-btn">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Gestion des modales
        function openModal(type) {
            document.getElementById(type + 'Modal').style.display = 'flex';
        }

        function closeModal(type) {
            document.getElementById(type + 'Modal').style.display = 'none';
        }
        function confirmDelete(numServ) {
            if (confirm("Supprimer cet entretien ?")) {
                window.location.href = 'entretien.php?supprimer=' + numServ;
            }
        }

        function openEditModal(id, serv, immat, client, date) {
            document.getElementById('editId').value = id;
            document.getElementById('editServ').value = serv;
            document.getElementById('editImmat').value = immat;
            document.getElementById('editClient').value = client;
            document.getElementById('editDate').value = date;
            openModal('edit');
        }




    </script>
</body>
</html>