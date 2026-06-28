<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forest1";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Species groups eligible for prod30 calculation
$eligibleGroups = [1, 2, 3, 5];

// Get selected diameter threshold from URL parameter or default to 45
$selectedThreshold = isset($_GET['cutting-regime']) ? intval($_GET['cutting-regime']) : 45;
$diameterThresholds = [45, 50, 55, 60, 65];
if (!in_array($selectedThreshold, $diameterThresholds)) {
    $selectedThreshold = 45;
}

// Fixed diameter class ranges (cm) - consistent with forestry standards
$dclassRanges = [
    1 => [5, 15],      // 5 ≤ diameter < 15
    2 => [15, 30],     // 15 ≤ diameter < 30
    3 => [30, 45],     // 30 ≤ diameter < 45
    4 => [45, 60],     // 45 ≤ diameter < 60
    5 => [60, 80]      // 60 ≤ diameter < 80
];

// Function to calculate volume at 30 years production (corrected formula implementation)
function calculate_prod30_volume($diameter, $height) {
    // Diameter increment rates by diameter class (cm/year) - from the table in image
    $diameterIncrements = [
        [5, 15, 0.4],    // 5-15cm: 0.4 cm/year
        [15, 30, 0.6],   // 15-30cm: 0.6 cm/year  
        [30, 45, 0.5],   // 30-45cm: 0.5 cm/year
        [45, 60, 0.5],   // 45-60cm: 0.5 cm/year
        [60, 999, 0.7]   // 60+cm: 0.7 cm/year
    ];
    
    // Start with current diameter (D0)
    $currentDiameter = $diameter;
    
    // Project diameter growth over 30 years (D1 to D30)
    for ($year = 1; $year <= 30; $year++) {
        // Find appropriate increment rate for current diameter at the start of this year
        $incrementRate = 0.4; // default fallback
        foreach ($diameterIncrements as $range) {
            if ($currentDiameter >= $range[0] && $currentDiameter < $range[1]) {
                $incrementRate = $range[2];
                break;
            }
        }
        
        // Add annual increment to get diameter for this year
        $currentDiameter += $incrementRate;
    }
    
    $diameter30 = $currentDiameter;
    
    // Calculate volume at year 30 using the formula: Volume = -0.0971 + 9.503 * D²
    // Where D is diameter at year 30 in meters
    $diameterInMeters = $diameter30 / 100;
    $volume30 = -0.0971 + (9.503 * pow($diameterInMeters, 2));
    
    // Ensure volume is not negative
    $volume30 = max(0, $volume30);
    
    return $volume30;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Year 30 Calculator | Forest Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
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

        .chart-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 2rem;
            position: relative;
            height: 500px;
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
            <h1>Production Year 30 Calculator (Per Hectare)</h1>
            <p>30-year yield projection for trees below <?= $selectedThreshold ?>cm cutting regime</p>
        </div>
    </header>

    <div class="container">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <?php for ($d = 1; $d <= 5; $d++): ?>
                            <th class="dclass-header">DClass<?= $d ?><br>
                            <?= $dclassRanges[$d][0] ?>-<?= $dclassRanges[$d][1] ?>cm
                            </th>
                        <?php endfor; ?>
                        <th class="total-col">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Initialize arrays for 7 Species Groups and 5 Diameter Classes
                    $StandTable_Num = array_fill(1, 7, array_fill(1, 5, 0));
                    $StandTable_Prod30 = array_fill(1, 7, array_fill(1, 5, 0));
                    
                    // Initialize histogram data array
                    $histogramData = [];
                    
                    // Calculate production data for each species group and diameter class
                    for ($g = 1; $g <= 7; $g++) {
                        $pro0_total = 0; // Current production total for histogram
                        $pro30_total = 0; // 30-year production total for histogram
                        
                        for ($d = 1; $d <= 5; $d++) {
                            [$min, $max] = $dclassRanges[$d];

                            // Query to get trees within the diameter range AND below cutting regime threshold
                            $query = "SELECT diameter, height 
                                    FROM trees 
                                    WHERE group_G = $g 
                                    AND diameter >= $min 
                                    AND diameter < $max
                                    AND diameter < $selectedThreshold";
                            
                            $result = $conn->query($query);
                            
                            if (!$result) {
                                continue;
                            }
                            
                            $count = 0;
                            $totalProdForClass = 0;

                            while ($tree = $result->fetch_assoc()) {
                                // Only process trees that belong to the eligible species groups for prod30
                                if (in_array($g, $eligibleGroups)) {
                                    $count++;
                                    
                                    // Calculate prod30 volume for eligible trees
                                    $prod30 = calculate_prod30_volume($tree['diameter'], $tree['height']);
                                    $totalProdForClass += $prod30;
                                }
                            }

                            // Per hectare calculations (keeping the +1 adjustment)
                            // Store in arrays - removed decimal points for tree counts
                            $StandTable_Num[$g][$d] = round(($count + 1) / 100);
                            $StandTable_Prod30[$g][$d] = round($totalProdForClass / 100, 2);
                            
                            $pro30_total += $StandTable_Prod30[$g][$d];
                        }
                        
                        // Calculate Pro_0 (Current Production) for histogram - trees above cutting regime
                        $pro0_query = "SELECT diameter, height 
                                     FROM trees 
                                     WHERE group_G = $g 
                                     AND diameter >= $selectedThreshold";
                        $pro0_result = $conn->query($pro0_query);
                        
                        $totalPro0ForGroup = 0;
                        while ($tree = $pro0_result->fetch_assoc()) {
                            if (in_array($g, $eligibleGroups)) {
                                $D = $tree['diameter'] / 100;
                                $H = $tree['height'];
                                $prod = pi() * pow($D, 2) * $H / 4;
                                $totalPro0ForGroup += $prod;
                            }
                        }
                        $pro0_total = round($totalPro0ForGroup / 100, 2);
                        
                        // Store histogram data
                        $histogramData[] = [
                            'species_group' => "SG$g",
                            'pro_0' => $pro0_total,
                            'pro_30' => $pro30_total
                        ];
                    }
                    
                    // Display the table using the arrays
                    for ($g = 1; $g <= 7; $g++): 
                        $numRow = "<tr><td class='sg-label'>SG$g Num</td>";
                        $prodRow = "<tr><td class='sg-label'>SG$g Prod30</td>";
                        $totalNum = 0;
                        $totalProd = 0;

                        for ($d = 1; $d <= 5; $d++) {
                            $numRow .= "<td>" . $StandTable_Num[$g][$d] . "</td>";
                            $prodRow .= "<td>" . $StandTable_Prod30[$g][$d] . "</td>";

                            $totalNum += $StandTable_Num[$g][$d];
                            $totalProd += $StandTable_Prod30[$g][$d];
                        }

                        $numRow .= "<td class='total-col'>" . round($totalNum) . "</td></tr>";
                        $prodRow .= "<td class='total-col'>" . round($totalProd, 2) . "</td></tr>";

                        echo $numRow . $prodRow;
                    endfor; ?>
                </tbody>
            </table>
        </div>

        <div class="chart-container">
            <canvas id="productionChart"></canvas>
        </div>

        <a href="index.php?cutting-regime=<?= $selectedThreshold ?>" class="back-btn">Back to Dashboard</a>

    </div>

    <footer>
        <div class="footer-content">
            <p>&copy; 2025 Forest Management System | Production Year 30 Analysis</p>
        </div>
    </footer>

    <script>
        // Chart data from PHP
        const chartData = <?= json_encode($histogramData) ?>;
        
        // Extract data for chart
        const speciesGroups = chartData.map(item => item.species_group);
        const pro0Data = chartData.map(item => item.pro_0);
        const pro30Data = chartData.map(item => item.pro_30);
        
        // Create the chart
        const ctx = document.getElementById('productionChart').getContext('2d');
        const productionChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: speciesGroups,
                datasets: [
                    {
                        label: 'Current Production (Pro_0)',
                        data: pro0Data,
                        backgroundColor: 'rgba(46, 139, 87, 0.7)',
                        borderColor: 'rgba(46, 139, 87, 1)',
                        borderWidth: 2
                    },
                    {
                        label: '30-Year Production (Pro_30)',
                        data: pro30Data,
                        backgroundColor: 'rgba(255, 215, 0, 0.7)',
                        borderColor: 'rgba(255, 215, 0, 1)',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Production Comparison by Species Group (Cutting Regime: <?= $selectedThreshold ?>cm)',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Production Volume (Per Hectare)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Species Groups'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>