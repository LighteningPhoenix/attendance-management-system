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


error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

ini_set('display_errors', 0);

ini_set('log_errors', 1);

include 'db_connect.php';

$from_date = $_GET['fromDate'];
$to_date   = $_GET['toDate'];

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>
Attendance Report
</title>

<style>

@page {

    size: A4 landscape;
    margin: 2mm;

}

body {

    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    font-size: 8px;

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
    font-size: 9px;
    font-weight: bold;

}

.date-row td {

    border: none;

}

.report-table {

    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;

}

.report-table th,
.report-table td {

    border: 1px solid black;
    padding: 1px;
    text-align: center;
    font-size: 7px;
    line-height: 1.1;
    height: 16px;
    word-break: break-word;

}

.report-table th {

    background: #d9d9d9;
    font-weight: bold;

}

.subtotal {

    background: #e6e6e6;
    font-weight: bold;

}

.total {

    background: #cccccc;
    font-weight: bold;

}

.footer {

    margin-top: 6px;
    font-size: 7px;
    line-height: 1.3;

}

.signature {

    margin-top: 12px;
    width: 100%;

}

.signature td {

    border: none;
    font-size: 8px;
    font-weight: bold;

}

.back-btn {

    margin-top: 8px;

}

@media print {

    .back-btn {

        display: none;

    }

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
$end   = strtotime($to_date);

while ($start <= $end) {

    $date_array[] = date('Y-m-d', $start);

    echo "<th>" . date('d', $start) . "</th>";

    $start = strtotime('+1 day', $start);

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
$end   = strtotime($to_date);

while ($start <= $end) {

    echo "<th>" . date('D', $start) . "</th>";

    $start = strtotime('+1 day', $start);

}

?>

</tr>

<?php

$grand_pr = 0;
$grand_ab = 0;
$grand_sp = 0;
$grand_pp = 0;
$grand_prc = 0;

$contractor_query = "
SELECT DISTINCT contractor_name
FROM master
ORDER BY contractor_name
";

$contractor_result = mysqli_query(
    $connection,
    $contractor_query
);

while ($contractor = mysqli_fetch_assoc($contractor_result)) {

    $current_contractor = $contractor['contractor_name'];

    $contractor_count_query = "
    SELECT COUNT(*) AS total
    FROM master
    WHERE contractor_name =
    '$current_contractor'
    ";

    $contractor_count_result = mysqli_query(
        $connection,
        $contractor_count_query
    );

    $contractor_total =
    mysqli_fetch_assoc(
        $contractor_count_result
    )['total'];

    $show_contractor = true;

    $category_query = "
    SELECT DISTINCT category
    FROM master
    WHERE contractor_name =
    '$current_contractor'
    ORDER BY FIELD(
        category,
        'MALI',
        'LABOUR',
        'SWEEPER'
    )
    ";

    $category_result = mysqli_query(
        $connection,
        $category_query
    );

    while ($category = mysqli_fetch_assoc($category_result)) {

        $current_category = $category['category'];

        $category_count_query = "
        SELECT COUNT(*) AS total
        FROM master
        WHERE contractor_name =
        '$current_contractor'
        AND category =
        '$current_category'
        ";

        $category_count_result = mysqli_query(
            $connection,
            $category_count_query
        );

        $category_total =
        mysqli_fetch_assoc(
            $category_count_result
        )['total'];

        $worker_query = "
        SELECT *
        FROM master
        WHERE contractor_name =
        '$current_contractor'
        AND category =
        '$current_category'
        ";

        $worker_result = mysqli_query(
            $connection,
            $worker_query
        );

        $show_category = true;

        $sl_no = 1;

        $subtotal_pr = 0;
        $subtotal_ab = 0;
        $subtotal_sp = 0;
        $subtotal_pp = 0;
        $subtotal_prc = 0;

        while ($worker = mysqli_fetch_assoc($worker_result)) {

            echo "<tr>";

            if ($show_contractor) {

                echo
                "<td rowspan='" .
                ($contractor_total + 3) .
                "'>"
                .
                $current_contractor
                .
                "</td>";

                $show_contractor = false;

            }

            if ($show_category) {

                echo
                "<td rowspan='" .
                ($category_total + 1) .
                "'>"
                .
                $current_category
                .
                "</td>";

                $show_category = false;

            }

            echo "<td>" . $worker['employee_id'] . "</td>";

            echo "<td>" . $worker['employee_name'] . "</td>";

            echo "<td>" . $sl_no++ . "</td>";

            $pr  = 0;
            $ab  = 0;
            $sp  = 0;
            $pp  = 0;
            $prc = 0;

            foreach ($date_array as $date) {

                $attendance_query = "
                SELECT status
                FROM attendance_final
                WHERE employee_id =
                '{$worker['employee_id']}'
                AND attendance_date =
                '$date'
                ";

                $attendance_result = mysqli_query(
                    $connection,
                    $attendance_query
                );

                if (mysqli_num_rows($attendance_result) > 0) {

                    $attendance =
                    mysqli_fetch_assoc(
                        $attendance_result
                    );

                    $status =
                    $attendance['status'];

                }

                else {

                    $status = "";

                }

                echo "<td>$status</td>";

                if ($status == "PR") {

                    $pr++;

                }

                if ($status == "PRC") {

                    $prc++;

                }

                if ($status == "AB") {

                    $ab++;

                }

                if ($status == "SP") {

                    $sp++;

                }

                if ($status == "PP") {

                    $pp++;

                }

            }

            echo "<td>$pr</td>";
            echo "<td>$ab</td>";
            echo "<td>$sp</td>";
            echo "<td>$pp</td>";
            echo "<td>$prc</td>";

            echo "</tr>";

            $subtotal_pr += $pr;
            $subtotal_ab += $ab;
            $subtotal_sp += $sp;
            $subtotal_pp += $pp;
            $subtotal_prc += $prc;

        }

      $subtotal_colspan = 3;

echo "<tr class='subtotal'>";

echo "<td colspan='$subtotal_colspan'>SUB TOTAL</td>";

foreach ($date_array as $date) {

    $day_total = 0;

    $day_query = "
    SELECT COUNT(*) AS total
    FROM attendance_final af
    INNER JOIN master m
    ON af.employee_id = m.employee_id
    WHERE m.contractor_name = '$current_contractor'
    AND m.category = '$current_category'
    AND af.attendance_date = '$date'
    AND af.status IN ('PR')
    ";

    $day_result = mysqli_query(
        $connection,
        $day_query
    );

    $day_data = mysqli_fetch_assoc(
        $day_result
    );

    $day_total = $day_data['total'];

    echo "<td>$day_total</td>";

}

echo "<td>$subtotal_pr</td>";
echo "<td>$subtotal_ab</td>";
echo "<td>$subtotal_sp</td>";
echo "<td>$subtotal_pp</td>";
echo "<td>$subtotal_prc</td>";

echo "</tr>";

        $grand_pr += $subtotal_pr;
        $grand_ab += $subtotal_ab;
        $grand_sp += $subtotal_sp;
        $grand_pp += $subtotal_pp;
        $grand_prc += $subtotal_prc;

    }

}

echo "<tr class='total'>";

echo "<td colspan='5'>TOTAL</td>";

foreach ($date_array as $date) {

    $grand_day_total = 0;

    $grand_query = "
    SELECT COUNT(*) AS total
    FROM attendance_final
    WHERE attendance_date = '$date'
    AND status IN ('PR')
    ";

    $grand_result = mysqli_query(
        $connection,
        $grand_query
    );

    $grand_data = mysqli_fetch_assoc(
        $grand_result
    );

    $grand_day_total = $grand_data['total'];

    echo "<td>$grand_day_total</td>";

}

echo "<td>$grand_pr</td>";
echo "<td>$grand_ab</td>";
echo "<td>$grand_sp</td>";
echo "<td>$grand_pp</td>";
echo "<td>$grand_prc</td>";

echo "</tr>";

?>

</table>

<div class="footer">

AB : NO PUNCH ABSENT<br>
SP : SINGLE PUNCH<br>
PP : PARTIALLY PRESENT<br>
PR : PRESENT<br>
PRC : CORRECTED PRESENT

</div>

<table class="signature">

<tr>

<td>
DIO/OFFICER<br>
of USER Section
</td>

<td style="text-align:right;">
DIO/OFFICER of USER Section
</td>

</tr>

</table>

<div class="back-btn">

<button onclick="window.location.href='adminreport.php'">
Back
</button>

</div>

<script>

window.print();

</script>

</body>

</html>