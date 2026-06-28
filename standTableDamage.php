<?php
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

// Validate cutting regime (ensure it's one of the allowed values)
$allowedRegimes = [45, 50, 55, 60, 65];
if (!in_array((int)$cuttingRegime, $allowedRegimes)) {
    $cuttingRegime = '45';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stand Table - Damage | Forest Management System</title>
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

        .cutting-regime-info {
            background: linear-gradient(135deg, var(--accent-color), #FFE55C);
            color: var(--primary-dark);
            padding: 15px 25px;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 3px 10px rgba(255, 215, 0, 0.3);
        }

        .cutting-regime-info h3 {
            margin: 0 0 5px 0;
            font-weight: 600;
        }

        .cutting-regime-info p {
            margin: 0;
            opacity: 0.8;
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
<?php
// Simplified damage calculation functions based on tree proximity and environmental factors
function calculate_proximity_damage($treeX, $treeY, $allTrees, $currentTreeIndex)
{
    $damageScore = 0;
    $currentTree = $allTrees[$currentTreeIndex];
    
    foreach ($allTrees as $index => $otherTree) {
        if ($index == $currentTreeIndex) continue;
        
        $distance = sqrt(pow($treeX - $otherTree['x'], 2) + pow($treeY - $otherTree['y'], 2));
        
        // If trees are too close (competition damage)
        if ($distance < 5) {
            $damageScore += (5 - $distance) * 2; // Closer trees cause more damage
        }
        
        // Large trees can damage smaller nearby trees (falling, shading)
        if ($otherTree['diameter'] > $currentTree['diameter'] * 1.5 && $distance < $otherTree['height']) {
            $damageScore += ($otherTree['height'] - $distance) * 0.5;
        }
    }
    
    return round($damageScore);
}

function calculate_edge_damage($treeX, $treeY, $blockSize = 100)
{
    // Trees near block edges are more susceptible to wind damage
    $distanceToEdge = min($treeX, $treeY, $blockSize - $treeX, $blockSize - $treeY);
    
    if ($distanceToEdge < 10) {
        return round((10 - $distanceToEdge) * 1.5);
    }
    
    return 0;
}

function calculate_size_stress_damage($diameter, $height)
{
    // Very large or very small trees may have stress-related damage
    $damageScore = 0;
    
    // Small trees (diameter < 10cm) are more vulnerable
    if ($diameter < 10) {
        $damageScore += (10 - $diameter) * 0.8;
    }
    
    // Very tall trees relative to diameter (unstable) 
    $heightDiameterRatio = $height / ($diameter / 100); // Convert diameter to meters
    if ($heightDiameterRatio > 80) {
        $damageScore += ($heightDiameterRatio - 80) * 0.3;
    }
    
    return round($damageScore);
}
?>

<body>
    <header>
        <div class="container">
            <h1>Stand Table - Damage (Per Hectare)</h1>
            <p>Showing damaged trees by species group and diameter class</p>
        </div>
    </header>

    <div class="container">
        <!-- Cutting Regime Information -->
        <div class="cutting-regime-info">
            <h3>Current Cutting Regime: <?= $cuttingRegime ?>cm</h3>
            <p>Damage analysis for trees with cutting threshold of <?= $cuttingRegime ?>cm diameter</p>
        </div>

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
                    <?php 
                    // Initialize arrays for 7 Species Groups and 5 Diameter Classes
                    $StandTable_Num = array_fill(1, 7, array_fill(1, 5, 0));
                    $StandTable_Vol = array_fill(1, 7, array_fill(1, 5, 0));
                    
                    // Calculate damage data for each species group and diameter class
                    for ($g = 1; $g <= 7; $g++) {
                        for ($d = 1; $d <= 5; $d++) {
                            [$min, $max] = $dclassRanges[$d];

                            // Get all trees in this group and diameter class
                            $query = "SELECT coordX as x, coordY as y, diameter, height 
                                    FROM trees 
                                    WHERE group_G = $g AND diameter >= $min AND diameter < $max";
                            
                            $result = $conn->query($query);
                            
                            if (!$result) {
                                continue;
                            }

                            // Store all trees for proximity calculations
                            $treesInClass = [];
                            while ($tree = $result->fetch_assoc()) {
                                $treesInClass[] = $tree;
                            }

                            $damagedTreesCount = 0;
                            $totalDamageVolume = 0;

                            // Calculate damage for each tree
                            foreach ($treesInClass as $index => $tree) {
                                $treeX = $tree['x'];
                                $treeY = $tree['y'];
                                $diameter = $tree['diameter'];
                                $height = $tree['height'];

                                // Determine if tree would be cut based on cutting regime
                                $status = ($diameter >= $cuttingRegime) ? 'cut' : 'keep';

                                // Only calculate damage for trees that would be kept (not cut)
                                // Trees that are cut don't contribute to damage statistics
                                if ($status == 'keep') {
                                    // Calculate different types of damage
                                    $proximityDamage = calculate_proximity_damage($treeX, $treeY, $treesInClass, $index);
                                    $edgeDamage = calculate_edge_damage($treeX, $treeY);
                                    $sizeDamage = calculate_size_stress_damage($diameter, $height);

                                    $totalTreeDamage = $proximityDamage + $edgeDamage + $sizeDamage;

                                    // If tree has any damage (damage threshold of 3), count it as damaged
                                    if ($totalTreeDamage > 3) {
                                        $damagedTreesCount++;
                                        
                                        // Calculate tree volume using basic formula: V = π * (D/2)² * H * form_factor
                                        // Using form factor of 0.7 as approximation
                                        $radius = $diameter / 200; // Convert cm to meters and get radius
                                        $treeVolume = pi() * pow($radius, 2) * $height * 0.7;
                                        
                                        // Apply damage factor to volume (higher damage = more volume loss)
                                        $damageFactor = min($totalTreeDamage / 20, 1.0); // Cap at 100% damage
                                        $totalDamageVolume += $treeVolume * $damageFactor;
                                    }
                                }
                            }

                            // Convert to per hectare
                            // Assuming the total area surveyed is 100 hectares (10x10 blocks of 1 hectare each)
                            $totalAreaHa = 100; 
                            
                            // Store in arrays
                            $StandTable_Num[$g][$d] = round($damagedTreesCount / $totalAreaHa);
                            $StandTable_Vol[$g][$d] = round($totalDamageVolume / $totalAreaHa, 2);
                        }
                    }
                    
                    // Display the table using the arrays
                    for ($g = 1; $g <= 7; $g++): 
                        $numRow = "<tr><td class='sg-label'>SG$g Num</td>";
                        $volRow = "<tr><td class='sg-label'>SG$g Vol</td>";
                        $totalNum = 0;
                        $totalVol = 0;

                        for ($d = 1; $d <= 5; $d++) {
                            $numRow .= "<td>" . $StandTable_Num[$g][$d] . "</td>";
                            $volRow .= "<td>" . $StandTable_Vol[$g][$d] . "</td>";

                            $totalNum += $StandTable_Num[$g][$d];
                            $totalVol += $StandTable_Vol[$g][$d];
                        }

                        $numRow .= "<td class='total-col'>" . round($totalNum) . "</td></tr>";
                        $volRow .= "<td class='total-col'>" . round($totalVol, 2) . "</td></tr>";

                        echo $numRow . $volRow;
                    endfor; ?>
                </tbody>
            </table>
        </div>

        <a href="index.php?cutting-regime=<?= $cuttingRegime ?>" class="back-btn">Back to Dashboard</a>

    </div>

    <footer>
        <div class="footer-content">
            <p>&copy; 2025 Forest Management System | Sustainable Ecosystem Analytics</p>
        </div>
    </footer>
</body>
</html>
<?php $conn->close()
?>