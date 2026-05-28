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



        // GET ALL PUNCHES

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



        $punches = [];



        while (

            $punch =
            mysqli_fetch_assoc(
                $punch_result
            )

        ) {

            $punches[] =
            $punch['punch_time'];

        }



        // CHECK IF FIRST PUNCH
        // BELONGS TO PREVIOUS NIGHT SHIFT

        if (

            count($punches) > 0

        ) {

            $first_punch =
            $punches[0];



            $first_punch_datetime =
            strtotime(
                $attendance_date .
                ' ' .
                $first_punch
            );



            $morning_limit =
            strtotime(
                $attendance_date .
                ' 08:00:00'
            );



            // EARLY MORNING PUNCH

            if (

                $first_punch_datetime <
                $morning_limit

            ) {

                $previous_date =
                date(
                    'Y-m-d',
                    strtotime(
                        $attendance_date .
                        ' -1 day'
                    )
                );



                // CHECK PREVIOUS DAY LAST PUNCH

                $previous_query =
                "
                SELECT punch_time

                FROM raw_punch

                WHERE employee_id =
                '$employee_id'

                AND punch_date =
                '$previous_date'

                ORDER BY punch_time DESC

                LIMIT 1
                ";



                $previous_result =
                mysqli_query(
                    $connection,
                    $previous_query
                );



                if (

                    mysqli_num_rows(
                        $previous_result
                    ) > 0

                ) {

                    $previous_punch =
                    mysqli_fetch_assoc(
                        $previous_result
                    );



                    $previous_time =
                    $previous_punch['punch_time'];



                    $previous_datetime =
                    strtotime(
                        $previous_date .
                        ' ' .
                        $previous_time
                    );



                    $night_shift_start =
                    strtotime(
                        $previous_date .
                        ' 20:30:00'
                    );



                    // PREVIOUS DAY WAS NIGHT SHIFT

                    if (

                        $previous_datetime >=
                        $night_shift_start

                    ) {

                        // REMOVE FIRST PUNCH

                        array_shift(
                            $punches
                        );

                    }

                }

            }

        }



        $total_punch =
        count(
            $punches
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



        // SINGLE PUNCH

        else if (

            $total_punch == 1

        ) {

            $in_time =
            $punches[0];



            $in_datetime =
            strtotime(
                $attendance_date .
                ' ' .
                $in_time
            );



            $night_shift_start =
            strtotime(
                $attendance_date .
                ' 20:30:00'
            );



            // NIGHT SHIFT CHECK

            if (

                $in_datetime >=
                $night_shift_start

            ) {

                $next_date =
                date(
                    'Y-m-d',
                    strtotime(
                        $attendance_date .
                        ' +1 day'
                    )
                );



                $next_query =
                "
                SELECT punch_time

                FROM raw_punch

                WHERE employee_id =
                '$employee_id'

                AND punch_date =
                '$next_date'

                ORDER BY punch_time ASC

                LIMIT 1
                ";



                $next_result =
                mysqli_query(
                    $connection,
                    $next_query
                );



                if (

                    mysqli_num_rows(
                        $next_result
                    ) > 0

                ) {

                    $next_punch =
                    mysqli_fetch_assoc(
                        $next_result
                    );



                    $next_out_time =
                    $next_punch['punch_time'];



                    $next_datetime =
                    strtotime(
                        $next_date .
                        ' ' .
                        $next_out_time
                    );



                    $morning_limit =
                    strtotime(
                        $next_date .
                        ' 08:00:00'
                    );



                    if (

                        $next_datetime <=
                        $morning_limit

                    ) {

                        $out_time =
                        $next_out_time;



                        $in_seconds =
                        strtotime(
                            $attendance_date .
                            ' ' .
                            $in_time
                        );



                        $out_seconds =
                        strtotime(
                            $next_date .
                            ' ' .
                            $out_time
                        );



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



                        if (

                            $working_hours >= 8.5

                        ) {

                            $status = 'PR';

                        }

                        else {

                            $status = 'PP';

                        }

                    }

                    else {

                        $status = 'SP';

                    }

                }

                else {

                    $status = 'SP';

                }

            }

            else {

                $status = 'SP';

            }

        }



        // MULTIPLE PUNCHES

        else {

            $in_time =
            $punches[0];



            $out_time =
            $punches
            [$total_punch - 1];



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



            if (

                $working_hours < 1

            ) {

                $status = 'SP';

                $out_time = NULL;

                $working_hours = 0;

            }

            else if (

                $working_hours >= 8.5

            ) {

                $status = 'PR';

            }

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



        // UPDATE

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



        // INSERT

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



// CLEAR SESSION

unset($_SESSION['new_dates']);



echo
"Attendance processing completed.";

?>

