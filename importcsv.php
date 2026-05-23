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


include 'db_connect.php';

$message = "";
$message_color = "green";



// IMPORT CSV

if (
    isset($_POST['import_csv'])
) {

    if (

        isset($_FILES['csv_file'])

        &&

        $_FILES['csv_file']['error'] == 0

    ) {

        $file_name =
        $_FILES['csv_file']['tmp_name'];



        $file =
        fopen(
            $file_name,
            "r"
        );



        if ($file) {

            // SKIP HEADER

            fgetcsv($file);

            $insert_count = 0;

            $new_dates = [];

            while (

                ($row = fgetcsv($file)) !== FALSE

            ) {

                if (
                    count($row) < 3
                ) {

                    continue;

                }



                $employee_id =

                mysqli_real_escape_string(
                    $connection,
                    trim($row[0])
                );



                $punch_date =

                mysqli_real_escape_string(
                    $connection,
                    trim($row[1])
                );

                // HANDLE DATE FORMAT

if (

    preg_match(
        "/^\d{2}-\d{2}-\d{4}$/",
        $punch_date
    )

) {

    // dd-mm-yyyy → yyyy-mm-dd

    $date_object =

    DateTime::createFromFormat(
        'd-m-Y',
        $punch_date
    );



    if ($date_object) {

        $punch_date =

        $date_object->format(
            'Y-m-d'
        );

    }

}

else if (

    preg_match(
        "/^\d{4}-\d{2}-\d{2}$/",
        $punch_date
    )

) {

    // already yyyy-mm-dd

    $date_object =

    DateTime::createFromFormat(
        'Y-m-d',
        $punch_date
    );



    if ($date_object) {

        $punch_date =

        $date_object->format(
            'Y-m-d'
        );

    }

}



                $punch_time =

                mysqli_real_escape_string(
                    $connection,
                    trim($row[2])
                );

                // CONVERT AM/PM TO 24-HOUR FORMAT

                    $timestamp =
                    strtotime($punch_time);



                    if (

                        $timestamp !== false

                    ) {

                        $punch_time =
                        date(
                            "H:i:s",
                            $timestamp
                        );

                    }



                // EMPTY CHECK

                if (

                    $employee_id == "" ||

                    $punch_date == "" ||

                    $punch_time == ""

                ) {

                    continue;

                }



                // CHECK EMPLOYEE EXISTS IN MASTER

                $master_check_query =
                "
                SELECT employee_id

                FROM master

                WHERE employee_id =
                '$employee_id'
                ";

                $master_check_result =
                mysqli_query(
                    $connection,
                    $master_check_query
                );



                if (

                    mysqli_num_rows(
                        $master_check_result
                    ) == 0

                ) {

                    continue;

                }



                // CHECK DUPLICATE RAW PUNCH

                $duplicate_query =
                "
                SELECT id

                FROM raw_punch

                WHERE

                employee_id =
                '$employee_id'

                AND punch_date =
                '$punch_date'

                AND punch_time =
                '$punch_time'
                ";

                $duplicate_result =
                mysqli_query(
                    $connection,
                    $duplicate_query
                );



                // INSERT ONLY UNIQUE DATA

                if (

                    mysqli_num_rows(
                        $duplicate_result
                    ) == 0

                ) {

                    $insert_query =
                    "
                    INSERT INTO raw_punch (

                        employee_id,
                        punch_date,
                        punch_time

                    )

                    VALUES (

                        '$employee_id',
                        '$punch_date',
                        '$punch_time'

                    )
                    ";

                    mysqli_query(
                        $connection,
                        $insert_query
                    );

                    $insert_count++;

                    if (

    !in_array(
        $punch_date,
        $new_dates
    )

) {

    $new_dates[] =
    $punch_date;

}

                }

            }



            fclose($file);



           // PROCESS NEW ATTENDANCE

$_SESSION['new_dates'] =
$new_dates;

include 'process_punching.php';


            $message =
            $insert_count .
            " rows imported successfully.";

            $message_color =
            "green";

        }

        else {

            $message =
            "Unable to read CSV file.";

            $message_color =
            "red";

        }

    }

    else {

        $message =
        "Please select a CSV file.";

        $message_color =
        "red";

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>
Import CSV
</title>

<link rel="stylesheet"
      href="style.css">

<style>

.import-container {

    width: 100%;

    max-width: 760px;

    margin: 20px auto;

    background:
    rgba(255,255,255,0.88);

    backdrop-filter: blur(12px);

    padding: 55px;

    border-radius: 30px;

    box-shadow:
    0 14px 35px rgba(0,0,0,0.12);

    border:
    1px solid rgba(255,255,255,0.45);

    text-align: center;

}



.import-container h2 {

    text-align: center;

    background:
    linear-gradient(
        90deg,
        #2563eb,
        #0f766e
    );

    -webkit-background-clip: text;

    -webkit-text-fill-color: transparent;

    margin-bottom: 45px;

    font-size: 54px;

    font-weight: 800;

}



.message-box {

    width: 100%;

    max-width: 520px;

    margin: 0 auto 35px auto;

    padding: 18px;

    border-radius: 18px;

    font-size: 22px;

    font-weight: 700;

    background:
    rgba(37,99,235,0.08);

}



.button-row {

    display: flex;

    justify-content: center;

    align-items: center;

    margin-bottom: 45px;

}



#importForm {

    width: 100%;

    display: flex;

    flex-direction: column;

    align-items: center;

}



/* IMPORT BUTTON */

.action-btn {

    width: 340px;

    height: 88px;

    border: none;

    border-radius: 24px;

    font-size: 30px;

    font-weight: 800;

    color: white;

    cursor: pointer;

    transition: 0.28s ease;

    box-shadow:
    0 12px 24px rgba(0,0,0,0.15);

}



.import-btn {

    background:
    linear-gradient(
        135deg,
        #2563eb,
        #0f766e
    );

}



.import-btn:hover {

    transform:
    translateY(-4px)
    scale(1.03);

    background:
    linear-gradient(
        135deg,
        #1d4ed8,
        #0d9488
    );

    box-shadow:
    0 16px 30px rgba(37,99,235,0.28);

}



/* BACK BUTTON */

.back-btn {

    width: 190px;

    height: 78px;

    border: none;

    border-radius: 22px;

    background:
    linear-gradient(
        135deg,
        #64748b,
        #475569
    );

    color: white;

    font-size: 26px;

    font-weight: 800;

    cursor: pointer;

    transition: 0.25s;

}



.back-btn:hover {

    transform:
    translateY(-3px);

    background:
    linear-gradient(
        135deg,
        #475569,
        #334155
    );

}



/* FILE NAME */

.file-name {

    margin-top: 24px;

    font-size: 20px;

    font-weight: 700;

    color: #2563eb;

    text-align: center;

}



/* HIDDEN FILE INPUT */

.hidden-input {

    display: none;

}



/* MOBILE */

@media screen and (max-width: 768px) {

    .import-container {

        padding: 35px 25px;

    }



    .import-container h2 {

        font-size: 38px;

    }



    .action-btn {

        width: 100%;

        max-width: 320px;

        font-size: 24px;

        height: 78px;

    }



    .back-btn {

        width: 100%;

        max-width: 220px;

        font-size: 22px;

        height: 70px;

    }



    .message-box {

        font-size: 18px;

    }

}

</style>

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

<section class="import-container">

<h2>
Import Raw Punch CSV
</h2>

<div class="message-box"

     id="messageBox"

     style="
     color:
     <?php echo $message_color; ?>;
     ">

<?php echo $message; ?>

</div>

<div class="button-row">

<form method="POST"

      enctype="multipart/form-data"

      id="importForm">

<input type="file"

       name="csv_file"

       id="csvFile"

       class="hidden-input"

       accept=".csv">

<input type="hidden"

       name="import_csv"

       value="1">

<button type="button"

        class="action-btn import-btn"

        id="importButton">

Import New Data

</button>

<div class="file-name"

     id="fileName">

</div>

</form>

</div>

<button class="back-btn"

onclick="
window.location.href='admin.php'
">

Back

</button>

</section>

</main>

<footer class="main-footer">

<p>
Ordnance Factory Badmal
</p>

</footer>

<script>

const importButton =

document.getElementById(
    "importButton"
);

const csvFile =

document.getElementById(
    "csvFile"
);

const importForm =

document.getElementById(
    "importForm"
);

const fileName =

document.getElementById(
    "fileName"
);



// OPEN FILE PICKER

importButton.addEventListener(

    "click",

    function () {

        csvFile.click();

    }

);



// AUTO SUBMIT AFTER FILE SELECT

csvFile.addEventListener(

    "change",

    function () {

        if (

            csvFile.files.length > 0

        ) {

            fileName.innerHTML =

            csvFile.files[0].name;



            importForm.submit();

        }

    }

);

</script>

</body>

</html>