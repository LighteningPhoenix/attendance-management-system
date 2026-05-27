<?php

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

ini_set('display_errors', 0);

ini_set('log_errors', 1);

include 'db_connect.php';

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

    -webkit-print-color-adjust: exact;

    print-color-adjust: exact;

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

    @page {

        size: A4 landscape;

        margin: 2mm;

    }

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
WHERE contractor_name = '$contractor_name'
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

    $contractor_count_query =
    "
    SELECT COUNT(*) AS total_rows
    FROM master
    WHERE contractor_name = '$current_contractor'
    ";

    $contractor_count_result =
    mysqli_query(
        $connection,
        $contractor_count_query
    );

    $contractor_count =
    mysqli_fetch_assoc(
        $contractor_count_result
    );

    $contractor_rowspan =
        $contractor_count['total_rows'] + 6;

    $show_contractor = true;

    $category_query =
    "
    SELECT DISTINCT category
    FROM master
    WHERE contractor_name = '$current_contractor'
    ORDER BY FIELD(
        category,
        'MALI',
        'LABOUR',
        'SWEEPER'
    )
    ";

    $category_result =
    mysqli_query(
        $connection,
        $category_query
    );

    while (
        $category_row =
        mysqli_fetch_assoc(
            $category_result
        )
    ) {

        $category =
            $category_row['category'];

        $category_count_query =
        "
        SELECT COUNT(*) AS total
        FROM master
        WHERE contractor_name = '$current_contractor'
        AND category = '$category'
        ";

        $category_count_result =
        mysqli_query(
            $connection,
            $category_count_query
        );

        $category_count =
        mysqli_fetch_assoc(
            $category_count_result
        );

        $category_rowspan =
            $category_count['total'] + 1;

        $employee_query =
        "
        SELECT *
        FROM master
        WHERE contractor_name = '$current_contractor'
        AND category = '$category'
        ORDER BY employee_name ASC
        ";

        $employee_result =
        mysqli_query(
            $connection,
            $employee_query
        );

        $sl_no = 1;

        $subtotal_pr = 0;
        $subtotal_ab = 0;
        $subtotal_sp = 0;
        $subtotal_pp = 0;
        $subtotal_prc = 0;

        $datewise_total = [];

        foreach ($date_array as $date) {

            $datewise_total[$date] = 0;

        }

        while (
            $employee =
            mysqli_fetch_assoc(
                $employee_result
            )
        ) {

            $employee_id =
                $employee['employee_id'];

            echo '<tr>';

            if ($show_contractor == true) {

                echo
                '<td rowspan="' .
                $contractor_rowspan .
                '" class="left">
                ' .
                $current_contractor .
                '
                </td>';

                $show_contractor = false;

            }

            if ($sl_no == 1) {

                echo
                '<td rowspan="' .
                $category_rowspan .
                '">
                ' .
                $category .
                '
                </td>';

            }

            echo
            '<td>' .
            $employee_id .
            '</td>';

            echo
            '<td class="left">' .
            $employee['employee_name'] .
            '</td>';

            echo
            '<td>' .
            $sl_no .
            '</td>';

            $pr = 0;
            $ab = 0;
            $sp = 0;
            $pp = 0;
            $prc = 0;
            $wff = false;

            foreach (
                $date_array as $date
            ) {

                $attendance_query =
                "
                SELECT status
                FROM attendance_final
                WHERE employee_id = '$employee_id'
                AND attendance_date = '$date'
                ";

                $attendance_result =
                mysqli_query(
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
    $current_day =
date(
    'D',
    strtotime($date)
);

if (
    $current_day == 'Sun'
) {

    $wff = false;

}

if (

    $current_day == 'Sun'

    &&

    (
        $status == 'PR'
        ||
        $status == 'PRC'
    )

) {

    $wff = true;

}
}

else {

    $status = "";

}

                if ($status == 'PR') {

                    $pr++;

                    $datewise_total[$date]++;

                    $grand_daily_totals[$date]++;

                }

                else if ($status == 'PRC') {

                    $prc++;

                    $datewise_total[$date]++;

                    $grand_daily_totals[$date]++;

                }

                else if ($status == 'AB') {

    if ($wff) {

        $status = 'WFF';

        $wff = false;

    }

    else {

        $ab++;

    }

}

                else if ($status == 'SP') {

                    $sp++;

                }

                else if ($status == 'PP') {

                    $pp++;

                }

                echo
                '<td>' .
                $status .
                '</td>';

            }

            echo '<td>' . $pr . '</td>';
            echo '<td>' . $ab . '</td>';
            echo '<td>' . $sp . '</td>';
            echo '<td>' . $pp . '</td>';
            echo '<td>' . $prc . '</td>';

            echo '</tr>';

            $subtotal_pr += $pr;
            $subtotal_ab += $ab;
            $subtotal_sp += $sp;
            $subtotal_pp += $pp;
            $subtotal_prc += $prc;

            $grand_pr += $pr;
            $grand_ab += $ab;
            $grand_sp += $sp;
            $grand_pp += $pp;
            $grand_prc += $prc;

            $sl_no++;

        }

        echo '<tr class="subtotal">';

        echo '
        <td colspan="3">
        SUB TOTAL
        </td>
        ';

        foreach ($date_array as $date) {

            echo
            '<td>' .
            $datewise_total[$date] .
            '</td>';

        }

        echo '<td>' . $subtotal_pr . '</td>';
        echo '<td>' . $subtotal_ab . '</td>';
        echo '<td>' . $subtotal_sp . '</td>';
        echo '<td>' . $subtotal_pp . '</td>';
        echo '<td>' . $subtotal_prc . '</td>';

        echo '</tr>';

    }

}

echo '<tr class="total">';

echo '
<td colspan="4">
TOTAL
</td>
';

foreach ($date_array as $date) {

    echo
    '<td>' .
    $grand_daily_totals[$date] .
    '</td>';

}

echo '<td>' . $grand_pr . '</td>';
echo '<td>' . $grand_ab . '</td>';
echo '<td>' . $grand_sp . '</td>';
echo '<td>' . $grand_pp . '</td>';
echo '<td>' . $grand_prc . '</td>';

echo '</tr>';

?>

</table>

<div class="footer">

<div>
AB : NO PUNCH ABSENT
</div>

<div>
SP : SINGLE PUNCH
</div>

<div>
PP : PARTIALLY PRESENT
</div>

<div>
PR : PRESENT
</div>

<div>
PRC : CORRECTED PRESENT
</div>

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

<button
onclick="window.location.href='user.php'">

Back

</button>

</div>

<script>

window.onload = function () {

    window.print();

};

window.onafterprint = function () {

    window.close();

};

</script>

</body>

</html>