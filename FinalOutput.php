<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forest1";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get cutting regime
$regime = isset($_GET['cutting-regime']) ? intval($_GET['cutting-regime']) : 45; // Default to 45

// Group Names 
$groupNames = [
    1 => "Mersawa",
    2 => "Keruing",
    3 => "Dip Marketable",
    4 => "Dip Non Market",
    5 => "Non Dip Market",
    6 => "Non Dip Non Market",
    7 => "Others"
];

// Volume Function
function calculateVolume($diameter, $height) {
    $D = $diameter / 100; // Convert diameter to meters
    return round((pi() * pow($D, 2) * $height) / 4, 2);
}

function calculateD30($diameter) {
    return $diameter + 30;
}

function calculateVOL30($d30, $height) {
    $D = $d30 / 100;
    return round((pi() * pow($D, 2) * $height) / 4, 2);
}

function calculateProd30($d30, $vol30) {
    $D = $d30 / 100;
    return round((pi() * pow($D, 2) / 4) * $vol30, 2);
}

// Growth rates for Volume30 and Prod30
function getGrowthRate($diameter) {
    if ($diameter >= 5 && $diameter < 15) {
        return 0.4;
    } elseif ($diameter >= 15 && $diameter < 30) {
        return 0.6;
    } elseif ($diameter >= 30 && $diameter < 45) {
        return 0.5;
    } elseif ($diameter >= 45 && $diameter < 60) {
        return 0.5;
    } else {
        return 0.7;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Final Output</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        header, footer { background: #2E8B57; color: white; text-align: center; padding: 1rem; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background-color: #1F6B3D; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .back-btn { margin-top: 20px; display: inline-block; padding: 10px 20px; background: #2E8B57; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <header>
        <h1>Final Output Summary</h1>
        <p>Cutting Regime: <?= $regime ?></p>
    </header>

    <div class="container">
        <table>
            <thead>
                <tr>
                    <th>Species Name</th>
                    <th>Species Group</th>
                    <th>Total Volume 0</th>
                    <th>Total Number 0</th>
                    <th>Production 0</th>
                    <th>Damage</th>
                    <th>Remaining Trees</th>
                    <th>Growth 30</th>
                    <th>Production 30</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($g = 1; $g <= 7; $g++) {
                    // Adjust query to only show trees with diameter < cutting regime
                    $sql = "SELECT * FROM trees WHERE group_G = $g AND diameter < $regime";
                    $result = $conn->query($sql);

                    $totalVol = 0;
                    $totalNum = 0;
                    $cutVol = 0;
                    $damageCount = 0;
                    $growth30 = 0;
                    $prod30 = 0;

                    $trees = [];

                    while ($row = $result->fetch_assoc()) {
                        $vol = calculateVolume($row['diameter'], $row['height']);
                        $d30 = calculateD30($row['diameter']);
                        $v30 = calculateVOL30($d30, $row['height']);
                        $p30 = calculateProd30($d30, $v30);

                        // For group 1, 2, 3, 5, calculate prod30
                        if (in_array($g, [1, 2, 3, 5])) {
                            $prod30 += $p30;
                        }

                        $trees[] = [
                            'id' => $row['IDtree'],
                            'x' => $row['coordX'],
                            'y' => $row['coordY'],
                            'diameter' => $row['diameter'],
                            'height' => $row['height'],
                            'volume' => $vol,
                            'D30' => $d30,
                            'VOL30' => $v30,
                            'prod30' => $p30
                        ];

                        $totalVol += $vol;
                        $growth30 += $v30;
                        $totalNum++;
                    }

                    
                    if (in_array($g, [4, 6, 7])) {
                        $prod30 = 0;
                        $cutVol = 0;
                    }

                
                    $prod0 = 0;
                    if (in_array($g, [4, 6, 7])) {
                        $prod0 = 0;
                    } else {
                        $prod0 = round($totalVol, 2); // Calculate production for groups that are cut
                    }

                    
                    if (count($trees) === 0) {
                        echo "<tr><td>{$groupNames[$g]}</td><td>Group $g</td><td colspan='7'>No trees under regime</td></tr>";
                        continue;
                    }

                    // Simulate cut: pick largest diameter tree
                    usort($trees, fn($a, $b) => $b['diameter'] <=> $a['diameter']);
                    $cutTree = $trees[0];
                    $cutVol = $cutTree['volume'];

                    // Simulate damage: trees within 5m of cut tree
                    $cutX = $cutTree['x'];
                    $cutY = $cutTree['y'];
                    foreach ($trees as $tree) {
                        $distance = sqrt(pow($tree['x'] - $cutX, 2) + pow($tree['y'] - $cutY, 2));
                        if ($tree['id'] !== $cutTree['id'] && $distance <= 5) {
                            $damageCount++;
                        }
                    }

                    $remaining = $totalNum - 1 - $damageCount;

                    echo "<tr>
                        <td>{$groupNames[$g]}</td>
                        <td>Group $g</td>
                        <td>" . round($totalVol, 2) . "</td>
                        <td>$totalNum</td>
                        <td>" . round($prod0, 2) . "</td>
                        <td>$damageCount</td>
                        <td>$remaining</td>
                        <td>" . round($growth30, 2) . "</td>
                        <td>" . round($prod30, 2) . "</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>

        <a class="back-btn" href="index.php">Back to Dashboard</a>
    </div>

    <footer>
        <p>&copy; 2025 Forest Management System</p>
    </footer>
</body>
</html>
<?php $conn->close(); ?>