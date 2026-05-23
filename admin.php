<?php

session_start();



// AUTO LOGOUT AFTER 1 HOUR

$timeout_duration = 3600;



if (

    isset($_SESSION['LAST_ACTIVITY'])

    &&

    (
        time() -
        $_SESSION['LAST_ACTIVITY']
    ) > $timeout_duration

) {

    session_unset();

    session_destroy();

    header(
        "Location: index.html?admin=true"
    );

    exit();

}



// UPDATE LAST ACTIVITY TIME

$_SESSION['LAST_ACTIVITY'] = time();



// CHECK ADMIN LOGIN

if (

    !isset($_SESSION['admin'])

) {

    header(
        "Location: index.html?admin=true"
    );

    exit();

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Admin Portal
    </title>

    <link rel="stylesheet"
          href="style.css">

</head>

<body>

<header class="main-header">

    <h1>
        ORDNANCE FACTORY BADMAL
    </h1>

    <h2>
        Attendance Management System
    </h2>

</header>

<main class="main-container">

    <section class="admin-dashboard">

        <h2>
            Admin Dashboard
        </h2>

        <div class="admin-options">

            <button id="addContractorButton"
                    onclick="window.location.href='addcontractor.php'">

                Add Contractor

            </button>



            <button id="editManualButton"
                    onclick="window.location.href='editmanually.php'">

                Edit Manually

            </button>



            <button id="reportGenerateButton"
                    onclick="window.location.href='adminreport.php'">

                Report Generate

            </button>



            <button id="applyPrcButton"
                    onclick="window.location.href='applyprc.php'">

                Apply PRC

            </button>


             <button id="importCsvButton"
                    onclick="window.location.href='importcsv.php'">

                Import CSV

            </button>

        </div>



        <div class="admin-back">

            <button
            onclick="window.location.href='logout.php'">

                Log Out

            </button>

        </div>

    </section>

</main>

<footer class="main-footer">

    <p>
        Ordnance Factory Badmal
    </p>

</footer>

</body>

</html>