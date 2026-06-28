<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forest1";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Diameter class ranges (cm)
$dclassRanges = [
    1 => [5, 15],   // 5 ≤ diameter < 15
    2 => [15, 30],  // 15 ≤ diameter < 30
    3 => [30, 45],   // 30 ≤ diameter < 45
    4 => [45, 60],   // 45 ≤ diameter < 60
    5 => [60, 80]    // 60 ≤ diameter < 80
];

// Get the selected cutting regime from the query parameter (default to 45 if not set)
$cuttingRegime = isset($_GET['cutting-regime']) ? $_GET['cutting-regime'] : '45';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stand Table - Production | Forest Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
         :root {
            --primary-color: #2E8B57;
            --primary-dark: #1F6B3D;
            --secondary-color: #F5F5F5;
            --accent-color: #FFD700;
            --text-dark: #333;
            --text-light: #f8f8f8;
            --table-header-bg: #2E8B57;
            --table-row-odd: #f9f9f9;
            --table-row-even: #ffffff;
            --table-border: #e0e0e0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--secondary-color);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--text-light);
            padding: 2rem 0;
            text-align: center;
            margin-bottom: 2rem;
        }

        header h1 {
            font-size: 2rem;
            margin: 0;
            font-weight: 600;
        }

        header p {
            font-size: 1rem;
            opacity: 0.9;
            margin: 0.5rem 0 0;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 2rem;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }

        th {
            background-color: var(--table-header-bg);
            color: white;
            padding: 12px 15px;
            text-align: center;
            font-weight: 500;
        }

        td {
            padding: 10px 15px;
            text-align: center;
            border-bottom: 1px solid var(--table-border);
        }

        tr:nth-child(even) {
            background-color: var(--table-row-even);
        }

        tr:nth-child(odd) {
            background-color: var(--table-row-odd);
        }

        tr:hover {
            background-color: #f0f0f0;
        }

        .dclass-header {
            background-color: #e8f5e9;
            color: var(--primary-dark);
            font-weight: 600;
        }

        .sg-label {
            font-weight: 500;
            color: var(--primary-dark);
        }

        .total-col {
            font-weight: 600;
            background-color: #e8f5e9;
        }

        .back-btn {
            display: inline-block;
            padding: 0.6rem 1.5rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            transition: background 0.3s ease;
            font-weight: 500;
            margin-top: 1rem;
        }

        .back-btn:hover {
            background: var(--primary-dark);
        }

        footer {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--text-light);
            text-align: center;
            padding: 1.5rem 0;
            margin-top: 2rem;
        }

        .footer-content p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .table-container {
                padding: 10px;
            }
            
            th, td {
                padding: 8px 10px;
                font-size: 0.85rem;
            }
            
            header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Stand Table - Production (Per Hectare)</h1>
            <p>Showing harvested trees by species group and diameter class</p>
        </div>
    </header>

    <div class="container">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <?php for ($d = 1; $d <= 5; $d++): ?>
                            <th class="dclass-header">DClass<?= $d ?><br><?= $dclassRanges[$d][0] ?>-<?= $dclassRanges[$d][1] ?>cm</th>
                        <?php endfor; ?>
                        <th class="total-col">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($g = 1; $g <= 7; $g++): 
                        $numRow = "<tr><td class='sg-label'>SG$g Num</td>";
                        $prodRow = "<tr><td class='sg-label'>SG$g Prod</td>";  // Renamed from volRow to prodRow
                        $totalNum = 0;
                        $totalProd = 0;  // Store total production

                        for ($d = 1; $d <= 5; $d++) {
                            [$min, $max] = $dclassRanges[$d];

                            // Query to get trees within the diameter range (ignoring the status in the database)
                            $query = "SELECT diameter, height 
                                    FROM trees 
                                    WHERE group_G = $g AND diameter >= $min AND diameter < $max";
                            $result = $conn->query($query);
                            
                            $count = 0;
                            $totalProdForClass = 0;

                            while ($tree = $result->fetch_assoc()) {
                                // Convert diameter from cm to meters
                                $D = $tree['diameter'] / 100;  
                                $H = $tree['height'];

                                // Assign "cut" or "keep" status based on diameter and cutting regime
                                $status = ($tree['diameter'] >= $cuttingRegime) ? 'cut' : 'keep';

                                // Only process trees that are "cut" and belong to the eligible species groups
                                if ($status == 'cut' && in_array($g, [1, 2, 3, 5])) {
                                    $count++;  // Increase the tree count for this diameter class
                                    
                                    // Calculate production for "cut" trees only in species groups 1, 2, 3, and 5
                                    $prod = pi() * pow($D, 2) * $H / 4;
                                    $totalProdForClass += $prod;  // Sum the production for all trees in the class
                                }
                            }

                            // Per hectare calculations (keeping the +1 adjustment)
                            $numPerHa = round(($count + 1) / 100, 2);  // +1 adjustment for per-hectare calculation
                            $prodPerHa = round($totalProdForClass / 100, 2);  // Per hectare production

                            $numRow .= "<td>$numPerHa</td>";
                            $prodRow .= "<td>$prodPerHa</td>";

                            $totalNum += $numPerHa;
                            $totalProd += $prodPerHa;  // Add to total production
                        }

                        $numRow .= "<td class='total-col'>" . round($totalNum, 2) . "</td></tr>";
                        $prodRow .= "<td class='total-col'>" . round($totalProd, 2) . "</td></tr>";

                        echo $numRow . $prodRow;
                    endfor; ?>
                </tbody>
            </table>
        </div>

        <a href="index.php" class="back-btn">Back to Dashboard</a>
    </div>

    <footer>
        <div class="footer-content">
            <p>&copy; 2025 Forest Management System | Sustainable Ecosystem Analytics</p>
        </div>
    </footer>
</body>

</html>

<?php
$conn->close();
?>