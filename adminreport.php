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
Admin Report
</title>

<link rel="stylesheet"
      href="style.css">

<style>

.report-container {

    width: 90%;
    margin: 40px auto;
    background: #f5f5f5;
    padding: 40px;
    border-radius: 18px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);

}

.report-container h2 {

    text-align: center;
    color: #0b5394;
    margin-bottom: 50px;
    font-size: 48px;

}

.date-row {

    display: flex;
    gap: 40px;
    margin-bottom: 80px;

}

.date-group {

    flex: 1;

}

.date-group label {

    display: block;
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 12px;
    color: #0b5394;

}

.date-group input {

    width: 100%;
    height: 70px;
    font-size: 26px;
    border-radius: 14px;
    border: 2px solid #9fc5e8;
    padding: 0 20px;
    box-sizing: border-box;

}

.submit-btn {

    width: 170px;
    height: 75px;
    border: none;
    border-radius: 14px;
    background: #1e73be;
    color: white;
    font-size: 22px;
    font-weight: bold;
    cursor: pointer;
    margin-bottom: 20px;

}

.submit-btn:hover {

    background: #155a96;

}

.message {

    font-size: 22px;
    font-weight: bold;
    margin-bottom: 40px;
    color: green;

}

.download-title {

    font-size: 24px;
    font-weight: bold;
    margin-bottom: 35px;
    color: #0b5394;

}

.download-buttons {

    display: flex;
    gap: 70px;
    margin-bottom: 80px;

}

.download-buttons button {

    width: 300px;
    height: 75px;
    border: none;
    border-radius: 14px;
    font-size: 24px;
    font-weight: bold;
    color: white;
    cursor: pointer;

}

#pdfButton {

    background: #0b5394;

}

#excelButton {

    background: #2f944f;

}

.download-buttons button:disabled {

    opacity: 0.5;
    cursor: not-allowed;

}

.back-btn {

    width: 160px;
    height: 70px;
    border: none;
    border-radius: 14px;
    background: #6c757d;
    color: white;
    font-size: 22px;
    font-weight: bold;
    cursor: pointer;

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

<section class="report-container">

<h2>
Generate Full Report
</h2>

<div class="date-row">

<div class="date-group">

<label>
From Date
</label>

<input type="date"
       id="fromDate"
       max="<?php echo date('Y-m-d'); ?>">

</div>

<div class="date-group">

<label>
To Date
</label>

<input type="date"
       id="toDate"
       max="<?php echo date('Y-m-d'); ?>">

</div>

</div>

<button class="submit-btn"
        id="submitButton">

Submit

</button>

<div class="message"
     id="messageBox">

</div>

<div class="download-title">

Download Report:

</div>

<div class="download-buttons">

<button id="pdfButton"
        disabled>

PDF

</button>

<button id="excelButton"
        disabled>

Excel

</button>

</div>

<button class="back-btn"
onclick="window.location.href='admin.php'">

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

const fromDate =
document.getElementById("fromDate");

const toDate =
document.getElementById("toDate");

const submitButton =
document.getElementById("submitButton");

const pdfButton =
document.getElementById("pdfButton");

const excelButton =
document.getElementById("excelButton");

const messageBox =
document.getElementById("messageBox");

submitButton.addEventListener(
    "click",

    function () {

        if (
            fromDate.value === "" ||
            toDate.value === ""
        ) {

            messageBox.innerHTML =
            "Please select both dates.";

            messageBox.style.color =
            "red";

            pdfButton.disabled = true;

            excelButton.disabled = true;

            return;

        }

        if (
            fromDate.value > toDate.value
        ) {

            messageBox.innerHTML =
            "From Date cannot be greater than To Date.";

            messageBox.style.color =
            "red";

            pdfButton.disabled = true;

            excelButton.disabled = true;

            return;

        }
        const from =
new Date(fromDate.value);

const to =
new Date(toDate.value);
        const difference =
Math.ceil(
    (to - from) /
    (1000 * 60 * 60 * 24)
) + 1;

if (difference > 31) {

    messageBox.innerHTML =
    "Cannot generate report for more than 31 days.";

    messageBox.style.color =
    "red";

    pdfButton.disabled = true;

    excelButton.disabled = true;

    return;

}


messageBox.innerHTML =
"Report ready for download.";

messageBox.style.color =
"green";

pdfButton.disabled = false;

excelButton.disabled = false;

        

    }
);

pdfButton.addEventListener(
    "click",

    function () {

        if (
            fromDate.value === "" ||
            toDate.value === ""
        ) {

            return;

        }

        let url =

        "adminreportgen.php?fromDate="

        +

        fromDate.value

        +

        "&toDate="

        +

        toDate.value

        +

        "&type=pdf";

        window.location.href = url;

    }
);

excelButton.addEventListener(
    "click",

    function () {

        if (
            fromDate.value === "" ||
            toDate.value === ""
        ) {

            return;

        }

        let url =

        "adminexcelreport.php?fromDate="

        +

        fromDate.value

        +

        "&toDate="

        +

        toDate.value;

        window.open(url, "_blank");

        setTimeout(function () {

            fromDate.value = "";
            toDate.value = "";

            pdfButton.disabled = true;
            excelButton.disabled = true;

            messageBox.innerHTML = "";

        }, 500);

    }
);

</script>

</body>

</html>