<?php

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

ini_set('display_errors', 0);

ini_set('log_errors', 1);

include 'db_connect.php';

header("Content-Type: application/vnd.ms-excel");

header(
    "Content-Disposition: attachment; filename=Admin_Attendance_Report.xls"
);

$contractor_name =
    $_GET['supplyOverhead'];

$from_date =
    $_GET['fromDate'];

$to_date =
    $_GET['toDate'];

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>
Admin Attendance Excel Report
</title>

<style>

body {

    font-family: Arial, sans-serif;

    font-size: 12px;

}

.header-title {

    text-align: center;

    font-size: 20px;

    font-weight: bold;

    margin-top: 3px;

    margin-bottom: 2px;

}

.header-subtitle {

    text-align: center;

    font-size: 12px;

    font-weight: bold;

    margin-bottom: 5px;

}

.date-row {

    width: 100%;

    margin-bottom: 4px;

    font-size: 12px;

    font-weight: bold;

}

.date-row td {

    border: none;

}

.report-table {

    width: 100%;

    border-collapse: collapse;

}

.report-table th,
.report-table td {

    border: 1px solid black;

    padding: 4px;

    text-align: center;

    font-size: 11px;

    white-space: nowrap;

}

.report-table th {

    background: #d9d9d9;

    font-weight: bold;

}

.left {

    text-align: left;

    padding-left: 3px;

}

.subtotal {

    background: #e6e6e6;

    font-weight: bold;

}

.total {

    background: #cccccc;

    font-weight: bold;

}

</style>

</head>

<body>

<div class="header-title">
Attendance Report
</div>

<div class="header-title" style="font-size:16px;">
Ordnance Factory Badmal
</div>

<div class="header-subtitle">
Periodic Monthly Master
</div>

<table class="date-row">

<tr>

<td>
From Date :
<?php echo $from_date; ?>
</td>

<td style="text-align:right;">
To Date :
<?php echo $to_date; ?>
</td>

</tr>

</table>

<table class="report-table">

<tr>

<th rowspan="2">
FIRM'S NAME & NO.<br>
SCOPE OF WORK
</th>

<th rowspan="2">
CATEGORY OF<br>
CONTRACT WORKER
</th>

<th rowspan="2">
CONTRACT<br>
WORKER ID
</th>

<th rowspan="2">
NAME
</th>

<th rowspan="2">
SL NO
</th>

<?php

$date_array = [];

$start = strtotime($from_date);

$end = strtotime($to_date);

while ($start <= $end) {

    $date_array[] =
        date('Y-m-d', $start);

    echo
    '<th>' .
    date('d', $start) .
    '</th>';

    $start =
    strtotime(
        '+1 day',
        $start
    );

}

?>

<th rowspan="2">PR</th>
<th rowspan="2">AB</th>
<th rowspan="2">SP</th>
<th rowspan="2">PP</th>
<th rowspan="2">PRC</th>

</tr>

<tr>

<?php

$start = strtotime($from_date);

$end = strtotime($to_date);

while ($start <= $end) {

    echo
    '<th>' .
    date('D', $start) .
    '</th>';

    $start =
    strtotime(
        '+1 day',
        $start
    );

}

?>

</tr>

<?php

$grand_pr = 0;
$grand_ab = 0;
$grand_sp = 0;
$grand_pp = 0;
$grand_prc = 0;

$grand_daily_totals = [];

foreach ($date_array as $date) {

    $grand_daily_totals[$date] = 0;

}

$contractor_query =
"
SELECT DISTINCT contractor_name
FROM master
ORDER BY contractor_name ASC
";

$contractor_result =
mysqli_query(
    $connection,
    $contractor_query
);

while (
    $contractor =
    mysqli_fetch_assoc($contractor_result)
) {

    $current_contractor =
        $contractor['contractor_name'];

    $category_query =
    "
    SELECT DISTINCT category
    FROM master
    WHERE contractor_name = '$current_contractor'
    ORDER BY category ASC
    ";

    $category_result =
    mysqli_query(
        $connection,
        $category_query
    );

    while (
        $category =
        mysqli_fetch_assoc($category_result)
    ) {

        $current_category =
            $category['category'];

        $employee_query =
        "
        SELECT *
        FROM master
        WHERE contractor_name = '$current_contractor'
        AND category = '$current_category'
        ORDER BY employee_name ASC
        ";

        $employee_result =
        mysqli_query(
            $connection,
            $employee_query
        );

        $employee_count =
        mysqli_num_rows(
            $employee_result
        );

        if ($employee_count == 0) {

            continue;

        }

        $sl_no = 1;

        $subtotal_pr = 0;
        $subtotal_ab = 0;
        $subtotal_sp = 0;
        $subtotal_pp = 0;
        $subtotal_prc = 0;

        $daily_totals = [];

        foreach ($date_array as $date) {

            $daily_totals[$date] = 0;

        }

        while (
            $employee =
            mysqli_fetch_assoc($employee_result)
        ) {

            echo "<tr>";

            echo
            "<td>" .
            $current_contractor .
            "</td>";

            echo
            "<td>" .
            $current_category .
            "</td>";

            echo
            "<td>" .
            $employee['employee_id'] .
            "</td>";

            echo
            "<td class='left'>" .
            $employee['employee_name'] .
            "</td>";

            echo
            "<td>" .
            $sl_no .
            "</td>";

            $emp_pr = 0;
            $emp_ab = 0;
            $emp_sp = 0;
            $emp_pp = 0;
            $emp_prc = 0;

            foreach ($date_array as $date) {

                $attendance_query =
                "
                SELECT status
                FROM attendance_final
                WHERE employee_id = '" .
                $employee['employee_id'] .
                "'
                AND attendance_date = '$date'
                ";

                $attendance_result =
                mysqli_query(
                    $connection,
                    $attendance_query
                );

                if (
                    mysqli_num_rows(
                        $attendance_result
                    ) > 0
                ) {

                    $attendance =
                    mysqli_fetch_assoc(
                        $attendance_result
                    );

                    $status =
                        $attendance['status'];

                }

                else {

                    $status = '';

                }

                echo
                "<td>$status</td>";

                if ($status == 'PR') {

                    $emp_pr++;
                    $daily_totals[$date]++;
                    $grand_daily_totals[$date]++;

                }

                else if ($status == 'AB') {

                    $emp_ab++;

                }

                else if ($status == 'SP') {

                    $emp_sp++;

                }

                else if ($status == 'PP') {

                    $emp_pp++;

                }

                else if ($status == 'PRC') {

                    $emp_prc++;
                    $daily_totals[$date]++;
                    $grand_daily_totals[$date]++;

                }

            }

            echo "<td>$emp_pr</td>";
            echo "<td>$emp_ab</td>";
            echo "<td>$emp_sp</td>";
            echo "<td>$emp_pp</td>";
            echo "<td>$emp_prc</td>";

            echo "</tr>";

            $subtotal_pr += $emp_pr;
            $subtotal_ab += $emp_ab;
            $subtotal_sp += $emp_sp;
            $subtotal_pp += $emp_pp;
            $subtotal_prc += $emp_prc;

            $grand_pr += $emp_pr;
            $grand_ab += $emp_ab;
            $grand_sp += $emp_sp;
            $grand_pp += $emp_pp;
            $grand_prc += $emp_prc;

            $sl_no++;

        }

        echo "<tr class='subtotal'>";

        echo "<td colspan='5'>SUB TOTAL</td>";

        foreach ($date_array as $date) {

            echo
            "<td>" .
            $daily_totals[$date] .
            "</td>";

        }

        echo "<td>$subtotal_pr</td>";
        echo "<td>$subtotal_ab</td>";
        echo "<td>$subtotal_sp</td>";
        echo "<td>$subtotal_pp</td>";
        echo "<td>$subtotal_prc</td>";

        echo "</tr>";

    }

}

echo "<tr class='total'>";

echo "<td colspan='5'>GRAND TOTAL</td>";

foreach ($date_array as $date) {

    echo
    "<td>" .
    $grand_daily_totals[$date] .
    "</td>";

}

echo "<td>$grand_pr</td>";
echo "<td>$grand_ab</td>";
echo "<td>$grand_sp</td>";
echo "<td>$grand_pp</td>";
echo "<td>$grand_prc</td>";

echo "</tr>";

?>

</table>

</body>

</html>