
<?php
// ---------- Handle AJAX Request ----------
if (isset($_GET['ajax'])) {
    // DB connection
    $conn = new mysqli("localhost", "root", "", "forest1");
    if ($conn->connect_error) die("Connection failed");

    // Get parameters
    $regime = $_GET['regime'];
    $block_i = $_GET['block_i'];
    $block_j = $_GET['block_j'];

    // Fetch trees
    $stmt = $conn->prepare("SELECT * FROM trees WHERE block_I = ? AND block_J = ?");
    $stmt->bind_param("ii", $block_i, $block_j);
    $stmt->execute();
    $result = $stmt->get_result();
    $trees = [];
    while ($row = $result->fetch_assoc()) $trees[] = $row;

    // Damage functions
    function calculate_crown_damage($treeX, $treeY, $cutX0, $cutY0, $angleDeg, $height) {
        $angle = deg2rad($angleDeg);
        $cutX1 = $cutX0 + $height * cos($angle);
        $cutY1 = $cutY0 + $height * sin($angle);

        $dx = $cutX1 - $cutX0;
        $dy = $cutY1 - $cutY0;
        $length = sqrt($dx**2 + $dy**2);
        if ($length == 0) return 0;

        $px = $treeX - $cutX0;
        $py = $treeY - $cutY0;
        $proj = ($px * $dx + $py * $dy) / $length;

        if ($proj < 0 || $proj > $length) return 0;

        $ix = $cutX0 + ($proj / $length) * $dx;
        $iy = $cutY0 + ($proj / $length) * $dy;
        $dist = sqrt(($ix - $treeX)**2 + ($iy - $treeY)**2);

        return ($dist < 3) ? 1 : 0;
    }

    function calculate_stem_damage($treeX, $treeY, $cutX0, $cutY0, $angleDeg, $height) {
        $angle = deg2rad($angleDeg);
        $cutX1 = $cutX0 + $height * cos($angle);
        $cutY1 = $cutY0 + $height * sin($angle);

        $dx = $cutX1 - $cutX0;
        $dy = $cutY1 - $cutY0;
        $length = sqrt($dx**2 + $dy**2);
        if ($length == 0) return 0;

        $px = $treeX - $cutX0;
        $py = $treeY - $cutY0;
        $proj = ($px * $dx + $py * $dy) / $length;

        if ($proj < 0 || $proj > $length) return 0;

        $ix = $cutX0 + ($proj / $length) * $dx;
        $iy = $cutY0 + ($proj / $length) * $dy;
        $dist = sqrt(($ix - $treeX)**2 + ($iy - $treeY)**2);

        return ($dist < 2) ? 1 : 0;
    }

    $datasets = [
        'keep' => ['label' => 'Keep', 'color' => 'green', 'data' => []],
        'cut' => ['label' => 'Cut', 'color' => 'red', 'data' => []],
        'crown' => ['label' => 'Damaged Crown', 'color' => 'orange', 'data' => []],
        'stem' => ['label' => 'Damaged Stem', 'color' => 'yellow', 'data' => []]
    ];

   // Initialize arrays to track damaged trees
$damagedCrownIds = [];
$damagedStemIds = [];

foreach ($trees as $tree) {
    $x = $tree['coordX'];
    $y = $tree['coordY'];
    $h = $tree['height'];
    $dbh = $tree['diameter'];

    if ($dbh >= $regime) {
        $datasets['cut']['data'][] = ['x' => $x, 'y' => $y];

        foreach ($trees as $other) {
            if ($other['IDtree'] === $tree['IDtree']) continue;

            $oid = $other['IDtree'];
            $ox = $other['coordX'];
            $oy = $other['coordY'];

            if (!in_array($oid, $damagedCrownIds) && calculate_crown_damage($ox, $oy, $x, $y, 45, $h)) {
                $datasets['crown']['data'][] = ['x' => $ox, 'y' => $oy];
                $damagedCrownIds[] = $oid;
            }
            if (!in_array($oid, $damagedStemIds) && calculate_stem_damage($ox, $oy, $x, $y, 45, $h)) {
                $datasets['stem']['data'][] = ['x' => $ox, 'y' => $oy];
                $damagedStemIds[] = $oid;
            }
        }
    } else {
        $datasets['keep']['data'][] = ['x' => $x, 'y' => $y];
    }
}

    echo json_encode(['datasets' => array_values($datasets)]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tree Spatial Analysis | Forest Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #2E8B57;
            --primary-dark: #1F6B3D;
            --secondary-color: #F5F5F5;
            --accent-color: #FFD700;
            --text-dark: #333;
            --text-light: #f8f8f8;
            --card-bg: #ffffff;
            --card-shadow: 0 5px 15px rgba(0,0,0,0.1);
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

        .card {
            background: var(--card-bg);
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .controls {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: center;
        }

        .control-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        label {
            font-weight: 500;
            color: var(--primary-dark);
        }

        select {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
        }

        button {
            padding: 0.6rem 1.5rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 30px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: var(--primary-dark);
        }

        .chart-container {
            position: relative;
            height: 70vh;
            width: 100%;
            margin-top: 1.5rem;
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

          /* TOP NAV */
        .top-nav {
            background-color: white;
            box-shadow: var(--nav-shadow);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .top-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .nav-links a {
            margin-left: 1.5rem;
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            transition: color 0.3s ease;
        }

         nav {
            background-color: white;
            box-shadow: var(--nav-shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            display: flex;
            justify-content: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        nav a {
            padding: 1rem 1.5rem;
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        nav a:hover {
            color: var(--primary-color);
        }

        nav a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 3px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        nav a:hover::after {
            width: 70%;
        }
        .nav-links a:hover {
            color: var(--primary-color);
        }
 /* HEADER */
        header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--text-light);
            padding: 2rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1448375240586-882707db888b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') center/cover;
            opacity: 0.15;
            z-index: 0;
        }

     header h1, header p {
            position: relative;
            z-index: 1;
        }

        header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        header p {
            font-size: 1.1rem;
            max-width: 800px;
            margin: 0 auto;
            opacity: 0.9;
        }
        @media (max-width: 768px) {
            .controls {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .chart-container {
                height: 50vh;
            }
            
            header h1 {
                font-size: 1.5rem;
            }
             .nav-container {
                flex-wrap: wrap;
            }
            
            nav a {
                padding: 0.8rem 1rem;
                font-size: 0.9rem;
            }
            
            header h1 {
                font-size: 2rem;
            }
            
            .card-container {
                grid-template-columns: 1fr;
            }
              .nav-links a {
                display: block;
                margin: 0.5rem 0;
            }
        }
    </style>
</head>
<body>

<nav class="top-nav">
    <div class="container">
        <div class="logo">🌲 FMS</div>
        <div class="nav-links">
            <a href="info.php">Project Info</a>
            <a href="index.php">Calculations</a>
                <a href="AnalysisHisto.php">Analysis Histogram</a>
                 <a href="TreesDistrubute.php">Trees Distribution</a>
            
        </div>
    </div>
</nav>
    <header>
        <div class="container">
            <h1>Forest Management System</h1>
     <p>Comprehensive forest data management and analysis platform</p>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2>Tree Distribution Visualization</h2>
            <p>Analyze tree distribution patterns and damage assessment based on cutting regimes.</p>
            
            <div class="controls">
                <div class="control-group">
                    <label for="regime">Cutting Regime (DBH):</label>
                    <select id="regime">
                        <option value="45">45 cm</option>
                        <option value="50">50 cm</option>
                        <option value="55">55 cm</option>
                        <option value="60">60 cm</option>
                        <option value="65">65 cm</option>
                    </select>
                </div>
                
                <div class="control-group">
                    <label for="block_i">Block I:</label>
                    <select id="block_i">
                        <script>for (let i = 1; i <= 10; i++) document.write(`<option value="${i}">${i}</option>`);</script>
                    </select>
                </div>
                
                <div class="control-group">
                    <label for="block_j">Block J:</label>
                    <select id="block_j">
                        <script>for (let j = 1; j <= 10; j++) document.write(`<option value="${j}">${j}</option>`);</script>
                    </select>
                </div>
                
                <button onclick="loadData()">Generate Visualization</button>
            </div>
            
            <div class="chart-container">
                <canvas id="treeChart"></canvas>
                <h3>Legend</h3>
            <ul>
                <li><strong style="color: green">Green</strong>: Trees to be kept (below cutting regime)</li>
                <li><strong style="color: red">Red</strong>: Trees to be cut (above cutting regime)</li>
                <li><strong style="color: orange">Orange</strong>: Trees with crown damage</li>
                <li><strong style="color: yellow">Yellow</strong>: Trees with stem damage</li>
            </ul>
            </div>
        </div>
        

        
        <a href="index.php" class="back-btn">Back to Dashboard</a>
    </div>

    <footer>
        <div class="footer-content">
            <p>&copy; 2025 Forest Management System | Spatial Analysis Module</p>
        </div>
    </footer>

<script>
function loadData() {
    const regime = document.getElementById("regime").value;
    const block_i = document.getElementById("block_i").value;
    const block_j = document.getElementById("block_j").value;

    fetch(`TreesDistrubute.php?ajax=1&regime=${regime}&block_i=${block_i}&block_j=${block_j}`)
        .then(res => res.json())
        .then(data => drawChart(data));
}

function drawChart(data) {
    const ctx = document.getElementById("treeChart").getContext("2d");
    if (window.myChart) window.myChart.destroy();

      // Define colors explicitly to match your original design
    const colorMap = {
        'Keep': 'green',
        'Cut': 'red',
        'Damaged Crown': 'orange',
        'Damaged Stem': 'yellow'
    };

   const jitteredDatasets = data.datasets.map(dataset => {
    const pointCount = {};
    const jitteredData = dataset.data.map(point => {
        const key = `${point.x},${point.y}`;
        pointCount[key] = (pointCount[key] || 0) + 1;

        return {
            x: point.x + (Math.random() * 0.3 - 0.15) * pointCount[key],
            y: point.y + (Math.random() * 0.3 - 0.15) * pointCount[key],
        };
    });

    return {
        ...dataset,
        data: jitteredData,
        pointRadius: 5,
        backgroundColor: colorMap[dataset.label] || 'gray',  // Set color from map
        borderColor: colorMap[dataset.label] || 'gray',
    };
});


    window.myChart = new Chart(ctx, {
        type: 'scatter',
        data: {
            datasets: jitteredDatasets,
        },
        options: {
            plugins: { 
                legend: { 
                    position: 'right',
                    labels: { usePointStyle: true }, // Show color dots in legend
                },
            },
            scales: {
                x: { title: { display: true, text: 'X Coord' } },
                y: { title: { display: true, text: 'Y Coord' } },
            },
            elements: {
                point: { radius: 5, hoverRadius: 7 }, // Consistent point size
            },
        },
    });
}
</script>
</body>
</html>