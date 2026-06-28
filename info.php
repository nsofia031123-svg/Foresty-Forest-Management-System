<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forest1";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$groupNames = [
    1 => "Mersawa",
    2 => "Keruing",
    3 => "Dip Marketable",
    4 => "Dip Non Market",
    5 => "Non Dip Market",
    6 => "Non Dip Non Market",
    7 => "Others"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Forest Management System - Project Info</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2E8B57;
            --primary-dark: #1F6B3D;
            --secondary-color: #F5F5F5;
            --accent-color: #FFD700;
            --text-dark: #333;
            --text-light: #f8f8f8;
            --card-shadow: 0 10px 20px rgba(0,0,0,0.1);
            --nav-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-color: var(--secondary-color);
            color: var(--text-dark);
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
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

        /* CARDS */
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
        }

        .card h2 {
            margin-top: 0;
            color: var(--primary-dark);
        }

        .card ul {
            padding-left: 1.5rem;
        }

        .species-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }

        .species-table th, .species-table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .species-table th {
            background-color: var(--primary-color);
            color: white;
        }

        .card blockquote {
            font-style: italic;
            color: #555;
            border-left: 4px solid var(--primary-color);
            padding-left: 1rem;
        }

        .card blockquote span {
            display: block;
            margin-top: 0.5rem;
            font-weight: 500;
            color: var(--primary-dark);
        }

        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 1rem;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-family: inherit;
        }

        .contact-form button {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
        }

        .contact-form button:hover {
            background-color: var(--primary-dark);
        }

        .back-btn {
            display: inline-block;
            margin: 2rem 0;
            text-decoration: none;
            color: white;
            background-color: var(--primary-color);
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 500;
        }

        .back-btn:hover {
            background-color: var(--primary-dark);
        }

        footer {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            text-align: center;
            padding: 2rem 0;
        }

             @media (max-width: 768px) {
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
        <h1>Forest Management System</h1>
     <p>Comprehensive forest data management and analysis platform</p>
</header>

<div class="container">
    <div class="card">
        <h2>Project Overview</h2>
        <p>The Forest Management System is designed to monitor, analyze, and manage forest resources with a focus on sustainable management practices.</p>
        <h3>Key Features</h3>
        <ul>
            <li>Stand tables for comprehensive forest inventory</li>
            <li>Production data analysis by species and diameter class</li>
            <li>Damage assessment and monitoring</li>
            <li>Remainder analysis after cutting operations</li>
            <li>Per hectare calculations for standardized reporting</li>
        </ul>
    </div>

    <div class="card">
        <h2>Forest Information</h2>
        <p>The forest area under management consists of diverse tree species with varying commercial and ecological values.</p>
        <h3>Species Group Classification</h3>
        <table class="species-table">
            <thead>
                <tr>
                    <th>Group Code</th>
                    <th>Species Group Name</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groupNames as $code => $name): ?>
                    <tr>
                        <td>SG<?= $code ?></td>
                        <td><?= $name ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h3>Diameter Class System</h3>
        <ul>
            <li>DClass1: 5–15 cm</li>
            <li>DClass2: 15–30 cm</li>
            <li>DClass3: 30–45 cm</li>
            <li>DClass4: 45–60 cm</li>
            <li>DClass5: 60–80 cm</li>
        </ul>
    </div>

    <div class="card">
        <h2>Data Collection Methodology</h2>
        <ul>
            <li>Circular plots of 100m² (5.64m radius) established on a grid system</li>
            <li>All trees ≥5cm DBH measured</li>
            <li>Species identification and diameter recording for each tree</li>
            <li>Volume calculations using appropriate form factors</li>
            <li>Status assessment (keep, victim, cut) recorded</li>
        </ul>
        <h3>Data Analysis</h3>
        <ul>
            <li>Counts and volumes converted to per hectare basis</li>
            <li>Analysis by species group and diameter class</li>
            <li>Production and damage assessments</li>
            <li>Remainder projections based on cutting regimes</li>
        </ul>
    </div>

    <div class="card">
        <h2>What Experts Say</h2>
        <blockquote>
            “This system provides an essential framework for sustainable forestry, enabling data-driven decisions that protect both biodiversity and industry.”
            <span>- Dr. Hutan Lestari, Forestry Research Institute</span>
        </blockquote>
    </div>

    <div class="card">
        <h2>Contact Us</h2>
        <form class="contact-form">
            <input type="text" placeholder="Your Name" required />
            <input type="email" placeholder="Your Email" required />
            <textarea rows="4" placeholder="Your Message..." required></textarea>
            <button type="submit">Send Message</button>
        </form>
    </div>

    <a href="index.php" class="back-btn">← Back to Dashboard</a>
</div>

<footer>
    <p>&copy; 2025 Forest Management System | Sustainable Ecosystem Analytics</p>
</footer>

</body>
</html>
<?php $conn->close(); ?>