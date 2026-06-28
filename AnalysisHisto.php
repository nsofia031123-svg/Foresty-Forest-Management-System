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
    <title>Forest Management System - Analysis Histogram</title>
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
            --image-bg: #1a3f2a;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-color: var(--secondary-color);
            color: var(--text-dark);
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
            width: 100%;
            flex: 1;
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
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            padding: 0.5rem 0;
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
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
            opacity: 0.1;
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
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }

        header p {
            font-size: 1.1rem;
            max-width: 800px;
            margin: 0 auto;
            opacity: 0.9;
        }

        /* MAIN CONTENT */
        main {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 2rem 0;
        }

        /* IMAGE GALLERY */
        .image-gallery {
            margin: 0 auto;
            text-align: center;
            padding: 0 20px;
            width: 100%;
            max-width: 1000px;
        }

        .image-gallery h2 {
            font-size: 2.2rem;
            margin-bottom: 2.5rem;
            color: var(--primary-color);
            position: relative;
            display: inline-block;
        }

        .image-gallery h2::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .gallery-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3rem;
            margin-top: 2rem;
        }

        .gallery-item {
            background-color: white;
            border-radius: 14px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
            max-width: 850px;
        }

        .gallery-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .image-container {
            background-color: var(--image-bg);
            padding: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-container img {
            max-width: 100%;
            width: 800px;
            height: auto;
            border-radius: 8px;
            object-fit: contain;
            transition: transform 0.3s ease;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }

        .gallery-item:hover .image-container img {
            transform: scale(1.03);
        }

        .gallery-item p {
            font-weight: 600;
            color: var(--text-dark);
            padding: 1.5rem;
            margin: 0;
            background: white;
            border-top: 1px solid rgba(0,0,0,0.1);
            text-align: center;
            font-size: 1.2rem;
        }

        footer {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            text-align: center;
            padding: 2.5rem 0;
            margin-top: auto;
        }

        footer p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }
           /* New styles for the recommendation box */
        .recommendation-box {
            background-color: #e8f5e9;
            border-left: 5px solid var(--primary-dark);
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 0 8px 8px 0;
        }

        .recommendation-title {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .recommendation-text {
            color: var(--text-dark);
            margin: 0;
        }

        .sustainability-note {
            font-style: italic;
            color: var(--primary-dark);
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            background-color: #f0fff0;
            border-radius: 5px;
        }

        @media (max-width: 900px) {
            .image-container {
                padding: 30px;
            }
            
            .image-container img {
                width: 100%;
            }
            
            .gallery-item {
                max-width: 95%;
            }
        }

        @media (max-width: 768px) {
            .top-nav .container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
            }
            
            header h1 {
                font-size: 2rem;
            }
            
            .image-gallery h2 {
                font-size: 2rem;
                margin-bottom: 2rem;
            }
            
            .image-container {
                padding: 25px;
            }
            
            .gallery-item p {
                font-size: 1.1rem;
                padding: 1.2rem;
            }
        }

        @media (max-width: 480px) {
            .image-container {
                padding: 20px;
            }
            
            .image-gallery h2 {
                font-size: 1.8rem;
            }
            
            .gallery-item p {
                font-size: 1rem;
                padding: 1rem;
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
    <h1>Analysis Histogram</h1>
    <p>Visual representation of forest data distribution across different diameter classes</p>
</header>

<main>
    <section class="image-gallery">
        <h2>Diameter Class Distribution</h2>
        <div class="gallery-container">
            <div class="gallery-item">
                <div class="image-container">
                    <img src="images/45reg.jpg" alt="45 cm Diameter">
                </div>
                <p>45 cm Diameter</p>
            </div>
            <div class="gallery-item">
                <div class="image-container">
                    <img src="images/50.jpg" alt="50 cm Diameter">
                </div>
                <p>50 cm Diameter</p>
            </div>
            <div class="gallery-item">
                <div class="image-container">
                    <img src="images/55.jpg" alt="55 cm Diameter">
                </div>
                <p>55 cm Diameter</p>
            </div>
            <div class="gallery-item">
                <div class="image-container">
                    <img src="images/60.jpg" alt="60 cm Diameter">
                </div>
                <p>60 cm Diameter</p>
            </div>
            <div class="gallery-item">
                <div class="image-container">
                    <img src="images/65.jpg" alt="65 cm Diameter">
                </div>
                <p>65 cm Diameter</p>
            </div>
             <div class="container">
        <!-- Recommendation box similar to the image -->
        <div class="recommendation-box">
            <div class="recommendation-title">Recommended Cutting Size: 55-60cm DBH</div>
            <p class="recommendation-text">Based on comprehensive analysis of the 350cm diameter at breast height and long-term forest sustainability.</p>
        </div>

        <!-- Sustainability note -->
        <div class="sustainability-note">
            This cutting regime supports both economic needs and long-term forest sustainability
        </div>

        </div>
    </section>
</main>

<footer>
    <p>&copy; 2025 Forest Management System. All rights reserved.</p>
</footer>

</body>
</html>