<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forest1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>");
}

// Function to generate random float
function rand_float($min, $max)
{
    return $min + mt_rand() / mt_getrandmax() * ($max - $min);
}

// Species by group
$speciesByGroup = [
    1 => ["PHDK"],
    2 => ["CHBG", "CHBR", "CHTR", "CHMI", "KHLG", "TRAC"],
    3 => ["CHRH", "CRMS", "KKMS", "KKPN", "LMBI", "PCEK", "TBEG"],
    4 => ["KHOV", "KKDK", "KKKS", "KKTM", "PPEL", "RINM", "TRLT"],
    5 => ["ANKM", "ATTT", "BENG", "BSNK", "CHBK", "CHKM", "CHKO", "CHKR", "CHLK", "CHMC", "CHTP", "DCSP", "DYKL", "HUDN", "KMPR", "KRAY", "KREL", "KRKO", "KRPM", "KRYS", "MASK", "NNON", "PRDL", "PRLO", "PRNG", "SKRM", "SMPN", "SRAL", "SRKR", "SRLO", "SROL", "SWPR", "SYCR", "TAUR", "TEPI", "THNR", "THNS", "TRTM", "TRYG", "TTRV"],
    6 => ["ANKN", "ANKT", "ANOM", "ATNG", "BADM", "BAKG", "BDNG", "BELY", "BKSV", "BNKO", "BYPV", "CASA", "CCHB", "CHMK", "CHNY", "CHPL", "CHRK", "CHRM", "CHRS", "CHUT", "CKTM", "CREY", "DKOR", "DOKM", "HISN", "KAOM", "KCAS", "KDAG", "KDCH", "KDOL", "KES", "KKCM", "KKGN", "KKOM", "KNDL", "KNPR", "KRAG", "KRAS", "KRBO", "KRLA", "KRON", "KTOM", "KWAV", "LGNG", "MAKG", "MAKP", "MAKU", "MNPR", "NENS", "PANG", "PHNV", "PHON", "PHUT", "PLON", "PNAG", "PNGS", "POBY", "POCV", "POKH", "PONR", "PPTH", "PPUL", "PRPN", "PRUS", "PYPK", "RAIT", "ROKA", "RUNG", "SABL", "SARG", "SBMS", "SDEY", "SLEN", "SMCH", "SME", "SMKB", "SNAY", "SNOL", "SOUY", "SPOR", "SPPY", "SPTK", "SRKM", "SVAK", "SVCT", "SVPT", "TKOV", "TLOK", "TOLP", "TPOG", "TRAG", "TREN", "TRMN", "TRSK", "WYNG"],
    7 => ["ACSA", "ADCH", "AKSL", "AMBB", "AMCN", "AMPI", "ANCH", "ANKB", "ANRD", "APEN", "ATES", "ATSR", "BAKK", "BAKP", "BBOK", "BOPR", "BRCH", "BTIL", "CABB", "CHCK", "CHEK", "CHHA", "CHHU", "CHKG", "CHKP", "CHKU", "CHLS", "CHNA", "CHNO", "CHOV", "CHPS", "CHTU", "CTES", "DAKD", "DGPR", "DKPO", "DNKY", "DRDV", "DYSP", "EPSH", "KACL", "KAKU", "KANA", "KANE", "KANT", "KATG", "KAYK", "KBAL KRORLORNG", "KBDA", "KBDK", "KBKK", "KCHP", "KDCE", "KDCK", "KHMA", "KHNH", "KHOS", "KHTN", "KHVG", "KKAL", "KKKK", "KKLG", "KLIG", "KLNG", "KLPO", "KMPT", "KNAL", "KNAY", "KODK", "KOKH", "KOMT", "KOMY", "KORK", "KOTT", "KRAK", "KREG", "KREM", "KRMN", "KROH", "KRSR", "KRUS", "KRVN", "KTIT", "LORT", "LORV", "LOVG", "LRLT", "MADN", "MDAS", "MMAG", "MTYK", "NGOK", "NHAM", "NIV", "ONLK", "PAGA", "PAGS", "PECH", "PHLG", "PHMA", "PHNO", "PHOR", "PLNG", "PLOG", "PLOK", "PLOR", "PLPH", "PMVG", "PNKP", "PNOM", "POCH", "POPL", "POUN", "PPPR", "PPVK", "PREL", "PROM", "RANG", "ROCG", "RODL", "ROML", "ROTY", "ROVN", "RPCK", "SADA", "SAHA", "SAND", "SANK", "SAVP", "SBTS", "SEMN", "SKPL", "SLCH", "SLET", "SLNG", "SOPI", "SREG", "SRMO", "TAPL", "TBOT", "TENG", "THME", "THNO", "THTR", "TMAK", "TNIV", "TOUK", "TRBL", "TRCU", "TREL", "TROG", "TRSK", "TRYA", "TRYG", "TTPY", "UNKN", "VEAY", "VOEG", "YEAM", "YOUK"]
];

// Trees per group and diameter class (from table)
$treesPerGroupClass = [
    1 => [15, 12, 4, 2, 2],
    2 => [21, 18, 6, 4, 4],
    3 => [21, 18, 6, 4, 4],
    4 => [30, 27, 9, 5, 3],
    5 => [30, 27, 9, 4, 4],
    6 => [39, 36, 12, 7, 4],
    7 => [44, 42, 14, 9, 4]
];

// Diameter class ranges (cm)
$dclassRanges = [
    1 => [5, 15],
    2 => [15, 30],
    3 => [30, 45],
    4 => [45, 60],
    5 => [60, 80]
];

// Height ranges by diameter class (m)
$heightRanges = [
    1 => [5, 10],
    2 => [11, 15],
    3 => [16, 20],
    4 => [21, 25],
    5 => [26, 30]
];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forest Tree Data Insertion</title>
</head>

<body>
    <h2>Forest Tree Data Insertion</h2>
    <p>Processing data...</p>
    <hr>

    <?php

    for ($i = 1; $i <= 10; $i++) {
        for ($j = 1; $j <= 10; $j++) {
            for ($g = 1; $g <= 7; $g++) {
                for ($dclass = 1; $dclass <= 5; $dclass++) {
                    $treeData = [];
                    $noTrees = $treesPerGroupClass[$g][$dclass - 1];

                    for ($t = 1; $t <= $noTrees; $t++) {
                        $x = rand(0, 99);
                        $y = rand(0, 99);
                        $coordX = ($i - 1) * 100 + $x;
                        $coordY = ($j - 1) * 100 + $y;
                        $species = $speciesByGroup[$g][array_rand($speciesByGroup[$g])];
                        $diameter = round(rand_float(...$dclassRanges[$dclass]), 2);
                        $height = round(rand_float(...$heightRanges[$dclass]), 2);

                        $treeData[] = [
                            "x" => $coordX,
                            "y" => $coordY,
                            "species" => $species,
                            "diameter" => $diameter,
                            "height" => $height
                        ];
                    }

                    // Insert into database
                    foreach ($treeData as $tree) {
                        $sql = "INSERT INTO trees 
(block_I, block_J, coordX, coordY, group_G, species, diameter, height) 
VALUES 
($i, $j, {$tree['x']}, {$tree['y']}, $g, '{$tree['species']}', {$tree['diameter']}, {$tree['height']})";

                        if ($conn->query($sql) === TRUE) {
                            echo "<p style='color: green;'>Tree Added: Block ($i, $j), Species: {$tree['species']}, Diameter: " . round($tree['diameter'], 2) . " cm (Class $dclass), Height: " . round($tree['height'], 2) . " m</p>";
                        } else {
                            echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
                        }
                    }
                }
            }
        }
    }

    $result = $conn->query("SELECT COUNT(*) AS total FROM trees");
    $row = $result->fetch_assoc();
    $totalInserted = $row['total'];
    ?>

    <p><strong>Total Trees Inserted: <?php echo $totalInserted; ?></strong></p>
    <hr>
    <p>Data insertion completed.</p>
</body>

</html>

<?php
$conn->close();
?>