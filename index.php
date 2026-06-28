<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forest Management System</title>
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
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--secondary-color);
            color: var(--text-dark);
            line-height: 1.6;
        }

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

        main {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
            min-height: calc(100vh - 400px);
        }

        .welcome-section {
            text-align: center;
            margin-bottom: 3rem;
        }

        .welcome-section h2 {
            font-size: 2rem;
            color: var(--primary-dark);
            margin-bottom: 1rem;
        }

        .welcome-section p {
            max-width: 800px;
            margin: 0 auto 2rem;
            color: #555;
        }

        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .card h3 {
            color: var(--primary-dark);
            margin-bottom: 1rem;
        }

        .card p {
            color: #666;
            margin-bottom: 1.5rem;
        }

        .card a {
            display: inline-block;
            padding: 0.6rem 1.5rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            transition: background 0.3s ease;
            font-weight: 500;
        }

        .card a:hover {
            background: var(--primary-dark);
        }

        footer {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--text-light);
            text-align: center;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .footer-content p {
            margin: 0;
            opacity: 0.9;
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

            .regime-selector {
    margin-top: 15px;
}

.regime-selector label {
    margin-right: 10px;
    font-weight: bold;
}

.regime-selector select {
    padding: 5px 10px;
    border-radius: 4px;
    border: 1px solid #ccc;
    background-color: white;
}



      
        }


   /* Top Nav */
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
        <p>Comprehensive data management for sustainable forest ecosystems</p>
    </header>


    <main>
     <section class="welcome-section">
    <h2>Forest Data Dashboard</h2>
    <p>Access comprehensive forest management data and analytics to make informed decisions about sustainable forest ecosystems.</p>
    <div class="regime-selector">
    <form id="regime-form" method="GET" action="">
        <label for="cutting-regime">Select Cutting Regime:</label>
        <select id="cutting-regime" name="cutting-regime" onchange="document.getElementById('regime-form').submit()">
            <option value="45" <?php if (isset($_GET['cutting-regime']) && $_GET['cutting-regime'] == '45') echo 'selected'; ?>>Regime 45</option>
            <option value="50" <?php if (isset($_GET['cutting-regime']) && $_GET['cutting-regime'] == '50') echo 'selected'; ?>>Regime 50</option>
            <option value="55" <?php if (isset($_GET['cutting-regime']) && $_GET['cutting-regime'] == '55') echo 'selected'; ?>>Regime 55</option>
            <option value="60" <?php if (isset($_GET['cutting-regime']) && $_GET['cutting-regime'] == '60') echo 'selected'; ?>>Regime 60</option>
            <option value="65" <?php if (isset($_GET['cutting-regime']) && $_GET['cutting-regime'] == '65') echo 'selected'; ?>>Regime 65</option>
        </select>
        
    </form>
</div>
</section>

<div class="card-container">
    <div class="card">
        <h3>Final Output</h3>
        <p>View the comprehensive final analysis and reports of forest data.</p>
        <a href="FinalOutput.php?cutting-regime=<?php echo isset($_GET['cutting-regime']) ? $_GET['cutting-regime'] : '45'; ?>">View Data</a>
    </div>
    
    <div class="card">
        <h3>Stand Table All</h3>
        <p>Detailed information about all trees in the forest inventory.</p>
        <a href="standTableAll.php?cutting-regime=<?php echo isset($_GET['cutting-regime']) ? $_GET['cutting-regime'] : '45'; ?>">View Inventory</a>
    </div>
    
    <div class="card">
        <h3>Stand Table Damage</h3>
        <p>Records of damaged trees and assessment information.</p>
        <a href="standTableDamage.php?cutting-regime=<?php echo isset($_GET['cutting-regime']) ? $_GET['cutting-regime'] : '45'; ?>">View Reports</a>
    </div>
    
    <div class="card">
        <h3>Stand Table Production Year 0</h3>
        <p>Initial production data and baseline measurements.</p>
        <a href="standTableProd_0.php?cutting-regime=<?php echo isset($_GET['cutting-regime']) ? $_GET['cutting-regime'] : '45'; ?>">View Data</a>
    </div>
    
    <div class="card">
        <h3>Stand Table Production Year 30</h3>
        <p>Projected production data for year 30 of forest management.</p>
        <a href="standTableProd_30.php?cutting-regime=<?php echo isset($_GET['cutting-regime']) ? $_GET['cutting-regime'] : '45'; ?>">View Projections</a>
    </div>
    
    <div class="card">
        <h3>Stand Table Remainder</h3>
        <p>Additional forest data and supplementary information.</p>
        <a href="standTableRemainder.php?cutting-regime=<?php echo isset($_GET['cutting-regime']) ? $_GET['cutting-regime'] : '45'; ?>">View Details</a>
    </div>

    



</div>

    </main>

    <footer>
        <div class="footer-content">
            <p>&copy; 2025 Forest Management System | Sustainable Ecosystem Analytics</p>
        </div>
    </footer>

</body>

</html>