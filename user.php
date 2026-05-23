<?php

include 'db_connect.php';

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        User Portal
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

    <section>

        <form id="reportForm"
              method="GET"
              onsubmit="return false;">

            <div>

                <label for="supplyOverhead">
                    Supply Overhead
                </label>

                <select id="supplyOverhead"
                        name="supplyOverhead"
                        required>

                    <option value="">
                        Select Contractor
                    </option>

                    <?php

                    $query =
                        "
                        SELECT DISTINCT contractor_name
                        FROM master
                        ORDER BY contractor_name
                        ";

                    $result =
                        mysqli_query(
                            $connection,
                            $query
                        );

                    while (
                        $row =
                        mysqli_fetch_assoc($result)
                    ) {

                        echo
                            "<option value='" .
                            $row['contractor_name'] .
                            "'>" .
                            $row['contractor_name'] .
                            "</option>";

                    }

                    ?>

                </select>

            </div>



            <div class="date-row">

    <div class="date-box">

        <label for="fromDate">
            From Date
        </label>

        <input type="date"
               id="fromDate"
               name="fromDate"
               required>

    </div>

    <div class="date-box">

        <label for="toDate">
            To Date
        </label>

        <input type="date"
               id="toDate"
               name="toDate"
               required>

    </div>

</div>



            <p id="topMessageBox"></p>



            <button type="button"
                    id="submitButton"
                    disabled>

                Submit

            </button>



            <p id="bottomMessageBox"></p>



      <div class="download-section">

    <label>
        Download Report:
    </label>

    <br><br>

    <button type="button"
            id="pdfButton"
            disabled>

        PDF

    </button>

    &nbsp;&nbsp;&nbsp;&nbsp;

    <button type="button"
            id="excelButton"
            disabled>

        Excel

    </button>

</div>



            <br>

        </form>



        <br>



        <button type="button"
                onclick="window.location.href='index.html'">

            Back

        </button>

    </section>

</main>

<footer class="main-footer">

    <p>
        Ordnance Factory Badmal
    </p>

</footer>

<script src="script.js"></script>

</body>

</html>