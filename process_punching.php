<?php

include 'db_connect.php';



// CHECK NEW DATES

if (

    !isset($_SESSION['new_dates'])

    ||

    empty($_SESSION['new_dates'])

) {

    echo
    "No new dates found.";

    return;

}



// GET NEW DATES ONLY

$new_dates =
$_SESSION['new_dates'];



// GET ALL EMPLOYEES

$employee_query =
"
SELECT

employee_id

FROM master

ORDER BY
employee_id ASC
";

$employee_result =
mysqli_query(
    $connection,
    $employee_query
);



// STORE EMPLOYEES

$employees = [];

while (

    $employee =
    mysqli_fetch_assoc(
        $employee_result
    )

) {

    $employees[] =
    $employee['employee_id'];

}



// LOOP ONLY NEWLY IMPORTED DATES

foreach (

    $new_dates as $attendance_date

) {



    // LOOP EMPLOYEES

    foreach (

        $employees as $employee_id

    ) {



        // GET PUNCHES

        $punch_query =
        "
        SELECT punch_time

        FROM raw_punch

        WHERE employee_id =
        '$employee_id'

        AND punch_date =
        '$attendance_date'

        ORDER BY punch_time ASC
        ";

        $punch_result =
        mysqli_query(
            $connection,
            $punch_query
        );



        $total_punch =
        mysqli_num_rows(
            $punch_result
        );



        // DEFAULT VALUES

        $in_time = NULL;

        $out_time = NULL;

        $working_hours = 0;

        $status = 'AB';



        // AB

        if (

            $total_punch == 0

        ) {

            $status = 'AB';

        }



        // SP

        else if (

            $total_punch == 1

        ) {

            $single_punch =
            mysqli_fetch_assoc(
                $punch_result
            );

            $in_time =
            $single_punch['punch_time'];

            $status = 'SP';

        }



        // MULTIPLE PUNCHES

        else {

            $first_punch =
            mysqli_fetch_assoc(
                $punch_result
            );

            $in_time =
            $first_punch['punch_time'];



            mysqli_data_seek(
                $punch_result,
                $total_punch - 1
            );

            $last_punch =
            mysqli_fetch_assoc(
                $punch_result
            );

            $out_time =
            $last_punch['punch_time'];



            // WORKING HOURS

            $in_seconds =
            strtotime($in_time);

            $out_seconds =
            strtotime($out_time);

            $working_hours =
            (
                $out_seconds -
                $in_seconds
            ) / 3600;

            $working_hours =
            round(
                $working_hours,
                2
            );



            // DOUBLE PUNCH ERROR

            if (

                $working_hours < 1

            ) {

                $status = 'SP';

                $out_time = NULL;

                $working_hours = 0;

            }



            // PR

            else if (

                $working_hours >= 8.5

            ) {

                $status = 'PR';

            }



            // PP

            else {

                $status = 'PP';

            }

        }



        // CHECK EXISTING ATTENDANCE

        $attendance_check =
        "
        SELECT id

        FROM attendance_final

        WHERE

        employee_id =
        '$employee_id'

        AND attendance_date =
        '$attendance_date'
        ";

        $attendance_result =
        mysqli_query(
            $connection,
            $attendance_check
        );



        // UPDATE EXISTING

        if (

            mysqli_num_rows(
                $attendance_result
            ) > 0

        ) {

            $update_query =
            "
            UPDATE attendance_final

            SET

            in_time =
            " .
            (
                $in_time
                ? "'$in_time'"
                : "NULL"
            ) .
            ",

            out_time =
            " .
            (
                $out_time
                ? "'$out_time'"
                : "NULL"
            ) .
            ",

            working_hours =
            '$working_hours',

            status =
            '$status'

            WHERE

            employee_id =
            '$employee_id'

            AND attendance_date =
            '$attendance_date'
            ";

            mysqli_query(
                $connection,
                $update_query
            );

        }



        // INSERT NEW

        else {

            $insert_query =
            "
            INSERT INTO attendance_final (

                employee_id,
                attendance_date,
                in_time,
                out_time,
                working_hours,
                status

            )

            VALUES (

                '$employee_id',
                '$attendance_date',

                " .
                (
                    $in_time
                    ? "'$in_time'"
                    : "NULL"
                ) .
                ",

                " .
                (
                    $out_time
                    ? "'$out_time'"
                    : "NULL"
                ) .
                ",

                '$working_hours',
                '$status'

            )
            ";

            mysqli_query(
                $connection,
                $insert_query
            );

        }

    }

}



// CLEAR SESSION DATES

unset($_SESSION['new_dates']);



echo
"Attendance processing completed.";

?>