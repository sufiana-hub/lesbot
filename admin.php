<?php
date_default_timezone_set("Asia/Kuala_Lumpur");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pims_pbu";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomboric = $_POST['SCAN'];

    $sql = "SELECT * FROM signup WHERE noic='$nomboric'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nama = $row["fullname"];
        $nopend = $row["nomatric"];

        // Convert date and time to PHP DateTime object
        $datetime = new DateTime(); // Use the current date and time
        $day = $datetime->format("l"); // Get the day in the full textual representation

        // Insert participant data into the database
        $sql_insert = "INSERT INTO program_participants (ic, name, kad_matric, date_time, day_of_week) VALUES ('$nomboric', '$nama', '$nopend', NOW(), '$day')";
        if ($conn->query($sql_insert) === TRUE) {
            echo "Participant data inserted successfully.";
        } else {
            echo "Error: " . $sql_insert . "<br>" . $conn->error;
        }
    } else {
        echo "User not found.";
    }
}

// Retrieve participant data from the database
$sql = "SELECT * FROM program_participants";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>PIMS</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="assets\img\pimslogo.png" rel="icon">
    <link href="assets\img\pimslogo.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">

</head>

<body>
    <h1>SENARAI NAMA KEHADIRAN PELAJAR RAPI</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>IC</th>
            <th>NAMA</th>
            <th>KAD MATRIK</th>
            <th>MASA DAN WAKTU</th>
            <th>HARI</th><!-- Add a new table header for the day -->
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Convert date and time to PHP DateTime object
                $datetime = new DateTime($row["date_time"]);
                $date = $datetime->format("Y-m-d");
                $time = $datetime->format("h:i:s a");
                $day = $datetime->format("l"); // Get the day in the full textual representation

                echo "<tr>";
                echo "<td>" . $row["id"] . "</td>";
                echo "<td>" . $row["ic"] . "</td>";
                echo "<td>" . $row["name"] . "</td>";
                echo "<td>" . $row["kad_matric"] . "</td>";
                echo "<td>" . $date . " - " . $time . "</td>";
                echo "<td>" . $day . "</td>"; // Display the day in the table cell
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No participant data found.</td></tr>";
        }
        ?>
    </table>

    <!-- ======= Header ======= -->
    <header id="header" class="fixed-top">
        <!-- Rest of the header content remains the same -->
    </header><!-- End Header -->

    <main id="main">
        <!-- ======= Services Section ======= -->
        <section id="services" class="services">
            <div class="container" data-aos="fade-up">
                <div class="section-title">
                    <h2>DATA PELAJAR</h2>
                </div>

                <form action="admin.php" method="POST">
                    <div class="row">
                        <div class="col-lg-9 col-md-9">
                            <label for="name"><b>SCAN :</b></label>
                            <div class="form-group mt-2">
                                <input type="text" name="SCAN" class="form-style" placeholder="SCAN" autofocus required>
                                <input type="submit" value="Submit">
                            </div>
                        </div>
                    </div>
                </form>

                <div class="row">
                    <div class="col-lg-9 col-md-9">
                        <div class="form-group">
                            <label for="name">NAMA :</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $nama; ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="matric">KAD MATRIK :</label>
                            <input type="text" class="form-control" id="matric" name="matric" value="<?php echo $nopend; ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="email">WAKTU DAN MASA :</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo date(" h :  i  :  s a  - ") . date("Y - m - d"); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="hari">HARI :</label>
                            <input type="text" class="form-control" id="hari" name="hari" value="<?php echo $day; ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </section><!-- End Services Section -->
    </main>

    <!-- ======= Footer ======= -->
    <footer id="footer">
        <!-- Rest of the footer content remains the same -->
    </footer><!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>

    <script>
        // Add event listener to the "Log Keluar" button
        document.getElementById("logout-btn").addEventListener("click", function(event) {
            event.preventDefault();

            if (confirm('Adakah anda pasti?')) {
                // Perform any necessary logout operations here
                window.location.href = 'index.php';
            }
        });
    </script>
</body>

</html>
