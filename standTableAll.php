<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forest1";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Diameter class ranges (cm) - now using precise boundary conditions
$dclassRanges = [
    1 => [5, 15],   // 5 ≤ diameter < 15
    2 => [15, 30],  // 15 ≤ diameter < 30
    3 => [30, 45],   // 30 ≤ diameter < 45
    4 => [45, 60],   // 45 ≤ diameter < 60
    5 => [60, 80]    // 60 ≤ diameter < 80
];


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stand Table - All Trees | Forest Management System</title>
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
            <h1>Stand Table - All Trees Data</h1>
            <p>Per hectare values for number of trees and volume by diameter class and species group</p>
        </div>
    </header>

    <div class="container">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <?php 
                        for ($d = 1; $d <= 5; $d++): 
                            // Display diameter class range
                            echo "<th class='dclass-header'>DClass$d<br>" . $dclassRanges[$d][0] . "-" . $dclassRanges[$d][1] . " cm</th>";
                        endfor; ?>
                        <th class="total-col">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Get the selected cutting regime (default to 45 if not set)
                    $cuttingRegime = isset($_GET['cutting-regime']) ? $_GET['cutting-regime'] : '45';

                    // Iterate over each species group (SG)
                    for ($g = 1; $g <= 7; $g++): 
                        $numRow = "<tr><td class='sg-label'>SG$g Num</td>";
                        $volRow = "<tr><td class='sg-label'>SG$g Vol</td>";
                        $totalNum = 0;
                        $totalVol = 0;

                        // Iterate over diameter classes
                        for ($d = 1; $d <= 5; $d++) {
                            [$min, $max] = $dclassRanges[$d];
                            
                            // Adjust the diameter range based on the selected cutting regime
                            if ($cuttingRegime != 45) {
                                $min = max($min, $cuttingRegime);  // Only consider diameter values greater than or equal to the selected regime
                            }

                            // Fetch trees within the diameter range (without using volume from the database)
                            $query = "SELECT 
                                        diameter, height 
                                      FROM trees 
                                      WHERE group_G = $g 
                                      AND diameter >= $min 
                                      AND diameter < $max";
                            $result = $conn->query($query);
                            
                            $count = 0;
                            $vol = 0;
                            while ($tree = $result->fetch_assoc()) {
                                // Calculate volume for each tree (in cubic meters)
                                $D = $tree['diameter'] / 100;  // Convert diameter from cm to meters
                                $H = $tree['height'];
                                $volume = (pi() * pow($D, 2) * $H) / 4;  // Volume of a cylinder formula

                                $count++;  // Increase the tree count for this diameter class
                                $vol += $volume;  // Sum up the volume of all trees
                            }

                            // Per hectare calculations
                            $numPerHa = round(($count + 1) / 100, 2);  // +1 adjustment for per-hectare calculation
                            $volPerHa = round($vol / 100, 2);  // Per hectare volume

                            $numRow .= "<td>$numPerHa</td>";
                            $volRow .= "<td>$volPerHa</td>";

                            $totalNum += $numPerHa;
                            $totalVol += $volPerHa;
                        }

                        $numRow .= "<td class='total-col'>" . round($totalNum, 2) . "</td></tr>";
                        $volRow .= "<td class='total-col'>" . round($totalVol, 2) . "</td></tr>";

                        echo $numRow . $volRow;
                    endfor; 
                    ?>
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