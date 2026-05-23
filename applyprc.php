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


include "db_connect.php";



$message = "";
$messageColor = "";

$employeeData = null;



// =========================
// STEP 1 : SEARCH EMPLOYEE
// =========================

if (

    isset($_POST["searchEmployee"])

) {

    $employee_id =

        mysqli_real_escape_string(
            $connection,
            $_POST["employee_id"]
        );



    $attendance_date =

        mysqli_real_escape_string(
            $connection,
            $_POST["attendance_date"]
        );



    $sql =

        "SELECT *
         FROM attendance_final
         WHERE employee_id = '$employee_id'
         AND attendance_date = '$attendance_date'
         LIMIT 1";



    $result =

        mysqli_query(
            $connection,
            $sql
        );



    if (

        mysqli_num_rows($result) > 0

    ) {

        $employeeData =

            mysqli_fetch_assoc(
                $result
            );

    }

    else {

        $message =
            "Employee ID or Date Not Found!";

        $messageColor =
            "red";

    }

}



// =========================
// STEP 2 : APPLY PRC
// =========================

if (

    isset($_POST["confirmPRC"])

) {

    $employee_id =

        mysqli_real_escape_string(
            $connection,
            $_POST["employee_id"]
        );



    $attendance_date =

        mysqli_real_escape_string(
            $connection,
            $_POST["attendance_date"]
        );



    $updateQuery =

        "UPDATE attendance_final
         SET status = 'PRC'
         WHERE employee_id = '$employee_id'
         AND attendance_date = '$attendance_date'";



    mysqli_query(
        $connection,
        $updateQuery
    );



    header(
        "Location: applyprc.php?success=1"
    );

    exit();

}



// =========================
// SUCCESS MESSAGE
// =========================

if (

    isset($_GET["success"])

) {

    $message =
        "PRC Applied Successfully!";

    $messageColor =
        "green";

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Apply PRC
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
            Apply PRC
        </h2>



        <!-- MESSAGE BOX -->

        <?php

        if ($message != "") {

            echo "

            <p style='
                color: $messageColor;
                font-size: 20px;
                font-weight: bold;
                margin-bottom: 25px;
                text-align: center;
            '>

                $message

            </p>

            ";

        }

        ?>



        <!-- SEARCH FORM -->

        <form method="POST"
              id="prcForm">

            <label>
                Employee ID
            </label>

            <input type="text"
                   name="employee_id"
                   id="employeeIdInput"
                   required>



            <br><br>



            <label>
                Attendance Date
            </label>

            <input type="date"
                   name="attendance_date"
                   id="attendanceDateInput"
                   max="<?php echo date('Y-m-d'); ?>"
                   required>



            <br><br>



            <button type="submit"
                    name="searchEmployee"
                    id="applyPrcButton"
                    disabled>

                Apply PRC

            </button>



            <button type="button"
                    onclick="window.location.href='admin.php'">

                Back

            </button>

        </form>



        <!-- POPUP CONFIRMATION -->

        <?php

        if ($employeeData != null) {

        ?>



        <div id="popupOverlay"
             style="
             position: fixed;
             top: 0;
             left: 0;
             width: 100%;
             height: 100%;
             background: rgba(0,0,0,0.5);
             display: flex;
             justify-content: center;
             align-items: center;
             z-index: 9999;
             ">

            <div style="
                 background: white;
                 padding: 40px;
                 border-radius: 15px;
                 width: 500px;
                 text-align: center;
                 box-shadow: 0px 0px 20px rgba(0,0,0,0.3);
                 ">

                <h3 style="
                    color: #0b5394;
                    margin-bottom: 25px;
                    ">

                    Confirm PRC Change

                </h3>



                <p style="
                   font-size: 18px;
                   line-height: 1.8;
                   ">

                    Are you sure you want to
                    change the status of
                    Employee ID

                    <b>

                        <?php

                        echo $employeeData["employee_id"];

                        ?>

                    </b>

                    on

                    <b>

                        <?php

                        echo $employeeData["attendance_date"];

                        ?>

                    </b>

                    from

                    <b>

                        <?php

                        echo $employeeData["status"];

                        ?>

                    </b>

                    to

                    <b>
                        PRC
                    </b>

                    ?

                </p>



                <br>



                <!-- YES BUTTON -->

                <form method="POST"
                      style="display:inline;">

                    <input type="hidden"
                           name="employee_id"

                           value="<?php

                           echo $employeeData["employee_id"];

                           ?>">



                    <input type="hidden"
                           name="attendance_date"

                           value="<?php

                           echo $employeeData["attendance_date"];

                           ?>">



                    <button type="submit"
                            name="confirmPRC">

                        Yes

                    </button>

                </form>



                <!-- CANCEL BUTTON -->

                <button onclick="
                        window.location.href='applyprc.php'
                        ">

                    Cancel

                </button>

            </div>

        </div>

        <?php

        }

        ?>

    </section>

</main>



<footer class="main-footer">

    <p>
        Ordnance Factory Badmal
    </p>

</footer>



<script>

const employeeIdInput =

    document.getElementById(
        "employeeIdInput"
    );



const attendanceDateInput =

    document.getElementById(
        "attendanceDateInput"
    );



const applyPrcButton =

    document.getElementById(
        "applyPrcButton"
    );



function checkPRCForm() {

    if (

        employeeIdInput.value.trim() !== ""

        &&

        attendanceDateInput.value !== ""

    ) {

        applyPrcButton.disabled =
            false;

    }

    else {

        applyPrcButton.disabled =
            true;

    }

}



employeeIdInput.addEventListener(

    "input",

    checkPRCForm

);



attendanceDateInput.addEventListener(

    "input",

    checkPRCForm

);

</script>

</body>

</html>