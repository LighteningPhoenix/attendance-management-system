<?php

include 'db_connect.php';

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);

// CREATE FULL DATETIME

function createDateTime($date, $time)
{
    return strtotime($date . ' ' . $time);
}

// ATTENDANCE CONFIGURATION

$config = [

    // EARLY MORNING LIMIT

    'morning_limit' =>
    '08:00:00',



    // NIGHT SHIFT START

    'night_shift_start' =>
    '20:30:00',



    // FULL DAY MINIMUM HOURS

    'full_day_hours' =>
    8.5,



    // MINIMUM VALID SHIFT HOURS

    'minimum_shift_hours' =>
    1

];



// CALCULATE WORKING HOURS

function calculateWorkingHours(
    $in_datetime,
    $out_datetime
) {

    // HANDLE NIGHT SHIFT

    if (

        $out_datetime <
        $in_datetime

    ) {

        $out_datetime =
        strtotime('+1 day', $out_datetime);

    }



    $hours =
    (
        $out_datetime -
        $in_datetime
    ) / 3600;



    return round(
        $hours,
        2
    );

}

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

// START DATABASE TRANSACTION

mysqli_begin_transaction(
    $connection
);

try{
    foreach (

    $new_dates as $attendance_date

) {

    // FETCH ALL PUNCHES OF CURRENT DATE

    $all_punch_query =
    "
    SELECT

    employee_id,
    punch_time

    FROM raw_punch

    WHERE punch_date =
    '$attendance_date'

    ORDER BY employee_id ASC,
    punch_time ASC
    ";



    $all_punch_result =
    mysqli_query(
        $connection,
        $all_punch_query
    );



    // GROUP PUNCHES BY EMPLOYEE

    $date_punches = [];



    while (

        $row =
        mysqli_fetch_assoc(
            $all_punch_result
        )

    ) {

        $emp_id =
        $row['employee_id'];



        if (

            !isset(
                $date_punches[$emp_id]
            )

        ) {

            $date_punches[$emp_id] = [];

        }



        $date_punches[$emp_id][] =
        $row['punch_time'];

    }

    $previous_date =
    date(
        'Y-m-d',
        strtotime(
            $attendance_date . ' -1 day'
        )
    );

    $previous_last_punches = [];

    $previous_query = "
    SELECT
    employee_id,
    MAX(punch_time) AS last_punch
    FROM raw_punch
    WHERE punch_date = '$previous_date'
    GROUP BY employee_id
    ";

    $previous_result =
    mysqli_query(
        $connection,
        $previous_query
    );

    while (
        $row =
        mysqli_fetch_assoc(
            $previous_result
        )
    ) {

        $previous_last_punches[
            $row['employee_id']
        ] =
        $row['last_punch'];

    }

    $next_date =
    date(
        'Y-m-d',
        strtotime(
            $attendance_date . ' +1 day'
        )
    );


    $next_query = "
    SELECT
    employee_id,
    MIN(punch_time) AS first_punch
    FROM raw_punch
    WHERE punch_date = '$next_date'
    GROUP BY employee_id
    ";

    $next_result =
    mysqli_query(
        $connection,
        $next_query
    );

    while (
        $row =
        mysqli_fetch_assoc(
            $next_result
        )
    ) {

        $next_first_punches[
            $row['employee_id']
        ] =
        $row['first_punch'];

    }
    $upsert_stmt =
    mysqli_prepare(
        $connection,
        "
        INSERT INTO attendance_final (

            employee_id,
            attendance_date,
            in_time,
            out_time,
            working_hours,
            status

        )

        VALUES (?, ?, ?, ?, ?, ?)

        ON DUPLICATE KEY UPDATE

            in_time = VALUES(in_time),
            out_time = VALUES(out_time),
            working_hours = VALUES(working_hours),
            status = VALUES(status)
        "
    );

    // LOOP EMPLOYEES

    foreach (

        $employees as $employee_id

    ) {



        // GET EMPLOYEE PUNCHES FROM ARRAY

        $punches = [];



        if (

            isset(
                $date_punches[$employee_id]
            )

        ) {

            $punches =
            $date_punches[$employee_id];

        }


        // CHECK IF FIRST PUNCH
        // BELONGS TO PREVIOUS NIGHT SHIFT

        if (

            count($punches) > 0

        ) {

            $first_punch =
            $punches[0];



            $first_punch_datetime =
            createDateTime(
                $attendance_date,
                $first_punch
            );



            $morning_limit =
            createDateTime(
                $attendance_date,
                $config['morning_limit']
            );



            // EARLY MORNING PUNCH

            if (

                $first_punch_datetime <
                $morning_limit

            ) {

                // CHECK PREVIOUS DAY LAST PUNCH

                if (
                    isset(
                        $previous_last_punches[$employee_id]
                    )
                ) {

                    $previous_time =
                    $previous_last_punches[$employee_id];

                    $previous_datetime =
                    createDateTime(
                        $previous_date,
                        $previous_time
                    );

                    $night_shift_start =
                    createDateTime(
                        $previous_date,
                        $config['night_shift_start']
                    );

                    if (

                        $previous_datetime >=
                        $night_shift_start

                    ) {

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
            createDateTime(
                $attendance_date,
                $config['night_shift_start']
            );



            // NIGHT SHIFT CHECK

            if (

                $in_datetime >=
                $night_shift_start

            ) {


                if (

                    isset(
                        $next_first_punches[
                            $employee_id
                        ]
                    )

                ) {

                    $next_out_time =
                    $next_first_punches[
                        $employee_id
                    ];

                    if (

                        strtotime($next_out_time)
                        <=
                        strtotime(
                            $config['morning_limit']
                        )

                    ) {

                        $out_time =
                        $next_out_time;

                        $in_datetime =
                        createDateTime(
                            $attendance_date,
                            $in_time
                        );

                        $out_datetime =
                        createDateTime(
                            $next_date,
                            $out_time
                        );

                        $working_hours =
                        calculateWorkingHours(
                            $in_datetime,
                            $out_datetime
                        );

                        $status =
                            (
                                $working_hours >=
                                $config['full_day_hours']
                            )
                            ? 'PR'
                            : 'PP';

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
            $punches[$total_punch - 1];



            // CREATE FULL DATETIME

            $in_datetime =
            createDateTime(
                $attendance_date,
                $in_time
            );



            $out_datetime =
            createDateTime(
                $attendance_date,
                $out_time
            );



            // CALCULATE WORKING HOURS

            $working_hours =
            calculateWorkingHours(
                $in_datetime,
                $out_datetime
            );



            // INVALID SHORT DURATION

            if (

                $working_hours < $config['minimum_shift_hours']

            ) {

                $status = 'SP';

                $out_time = NULL;

                $working_hours = 0;

            }



            // FULL PRESENT

            else if (

                $working_hours >= $config['full_day_hours']

            ) {

                $status = 'PR';

            }



            // PARTIAL PRESENT

            else {

                $status = 'PP';

            }

        }



        // INSERT OR UPDATE ATTENDANCE

        $in_time_param =
            $in_time ?: NULL;

            $out_time_param =
            $out_time ?: NULL;

            mysqli_stmt_bind_param(
                $upsert_stmt,
                "ssssds",
                $employee_id,
                $attendance_date,
                $in_time_param,
                $out_time_param,
                $working_hours,
                $status
            );

            mysqli_stmt_execute(
                $upsert_stmt
            );

        

    }

}
mysqli_stmt_close(
            $upsert_stmt
        );




    // COMMIT TRANSACTION

    mysqli_commit(
        $connection
    );



    // CLEAR SESSION

    unset($_SESSION['new_dates']);



    echo
    "Attendance processing completed.";

}



catch (Exception $e) {



    // ROLLBACK CHANGES

    mysqli_rollback(
        $connection
    );



    echo
    "Error occurred: " .
    $e->getMessage();

}
?>

