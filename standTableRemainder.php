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
    1 => [5, 15],
    2 => [15, 30],
    3 => [30, 45],
    4 => [45, 60],
    5 => [60, 80]
];

$cuttingRegime = isset($_GET['cutting-regime']) ? (int)$_GET['cutting-regime'] : 45;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stand Table - Remainder</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f5f5f5; color: #333; margin: 0; padding: 0; }
        header, footer { background-color: #2E8B57; color: white; text-align: center; padding: 1.5rem 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        th, td { padding: 10px; text-align: center; border: 1px solid #ddd; }
        th { background-color: #1F6B3D; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .back-btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #2E8B57; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <header>
        <h1>Stand Table - Remainder</h1>
        <p>Remaining trees summary by species group and diameter class</p>
    </header>

    <div class="container">
        <table>
            <thead>
                <tr>
                    <th>Group</th>
                    <?php foreach ($dclassRanges as $i => [$min, $max]): ?>
                        <th>DClass<?= $i ?><br>(<?= $min ?>-<?= $max ?> cm)</th>
                    <?php endforeach; ?>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($g = 1; $g <= 7; $g++): 
                    $row = "<tr><td>SG$g</td>";
                    $groupTotal = 0;

                    foreach ($dclassRanges as $range) {
                        [$min, $max] = $range;

                        // skip if diameter class minimum is greater or equal to cutting regime
                        if ($min >= $cuttingRegime) {
                            $row .= "<td>-</td>";
                            continue;
                        }

                        $effectiveMax = min($max, $cuttingRegime);

                        $query = "SELECT COUNT(*) as count FROM trees WHERE group_G = $g AND diameter >= $min AND diameter < $effectiveMax";
                        $result = $conn->query($query);
                        $count = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['count'] : 0;

                        $perHa = round($count / 100, 2);
                        $row .= "<td>$perHa</td>";
                        $groupTotal += $perHa;
                    }

                    $row .= "<td><strong>" . round($groupTotal, 2) . "</strong></td></tr>";
                    echo $row;
                endfor; ?>
            </tbody>
        </table>

        <a class="back-btn" href="index.php">Back to Dashboard</a>
    </div>

    <footer>
        <p>&copy; 2025 Forest Management System</p>
    </footer>
</body>
</html>
<?php $conn->close(); 
?>