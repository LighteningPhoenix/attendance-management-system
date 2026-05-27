<?php
session_start();

if (

    !isset($_SESSION['admin'])

) {

    header(
        "Location: index.html"
    );

    exit();

}

include 'db_connect.php';

$employee_found = false;

$success_message = "";
$error_message = "";

$employee_id = "";
$employee_name = "";
$phone_number = "";
$category = "";
$dob = "";
$address = "";

if (
    isset($_POST['searchEmployee'])
) {

    $employee_id =
    trim($_POST['employee_id']);

    $search_query =
    "
    SELECT *
    FROM master
    WHERE employee_id =
    '$employee_id'
    ";

    $search_result =
    mysqli_query(
        $connection,
        $search_query
    );

    if (
        mysqli_num_rows(
            $search_result
        ) > 0
    ) {

        $employee =
        mysqli_fetch_assoc(
            $search_result
        );

        $employee_found = true;

        $employee_name =
        $employee['employee_name'];

        $phone_number =
        $employee['phone_number'];

        $category =
        $employee['category'];

        $dob =
        $employee['dob'];

        $address =
        $employee['address'];

    }

    else {

        $error_message =
        "Employee ID not found.";

    }

}

if (
    isset($_POST['updateEmployee'])
) {

    $employee_id =
    trim($_POST['employee_id']);

    $employee_name =
    trim($_POST['employee_name']);

    $phone_number =
    trim($_POST['phone_number']);

    $category =
    trim($_POST['category']);
    $dob =
    trim($_POST['dob']);
    $address =
    trim($_POST['address']);


    if (
       $employee_name == "" ||
$phone_number == "" ||
$category == "" ||
$dob == "" ||
$address == ""
    ) {

        $error_message =
        "All fields are required.";

        $employee_found = true;

    }

    else {

        $update_query =
        "
        UPDATE master
        SET

        employee_name =
        '$employee_name',

        category =
        '$category',

        phone_number =
        '$phone_number',

        dob =
        '$dob',

        address =
        '$address'


        WHERE employee_id =
        '$employee_id'
        ";

        if (
            mysqli_query(
                $connection,
                $update_query
            )
        ) {

            $success_message =
            "Employee details updated successfully.";

            header(
            "Refresh:2; url=editmanually.php"
            );

            $employee_found = true;

        }

        else {

            $error_message =
            "Update failed.";

            $employee_found = true;

        }

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
Edit Manually
</title>

<link rel="stylesheet"
      href="style.css">

<style>

.edit-container {

    width: 90%;
    max-width: 900px;

    margin: 40px auto;

    background: #f5f5f5;

    padding: 40px;

    border-radius: 20px;

    box-shadow: 0 8px 20px rgba(0,0,0,0.15);

}

.edit-container h2 {

    text-align: center;

    color: #0b5394;

    margin-bottom: 40px;

    font-size: 42px;

}

.form-group {

    margin-bottom: 30px;

}

.form-group label {

    display: block;

    font-size: 22px;

    font-weight: bold;

    margin-bottom: 10px;

    color: #0b5394;

}

.form-group input {

    width: 100%;

    height: 65px;

    font-size: 22px;

    border-radius: 12px;

    border: 2px solid #9fc5e8;

    padding: 0 20px;

    box-sizing: border-box;
}

.form-group textarea {

    width: 100%;

    font-size: 22px;

    border-radius: 12px;

    border: 2px solid #9fc5e8;

    padding: 15px 20px;

    box-sizing: border-box;

    resize: vertical;

    min-height: 120px;

    font-family: inherit;

}

.button-row {

    display: flex;

    gap: 30px;

    margin-top: 40px;

}

.button-row button {

    width: 260px;

    height: 70px;

    border: none;

    border-radius: 14px;

    font-size: 22px;

    font-weight: bold;

    color: white;

    cursor: pointer;

    white-space: nowrap;

}

.search-btn {

    background: #1e73be;

}

.update-btn {

    background: #2f944f;
    white-space: nowrap;
}

.back-btn {

    background: #6c757d;

}

.search-btn:disabled {

    opacity: 0.5;

    cursor: not-allowed;

}

.message {

    font-size: 22px;

    font-weight: bold;

    margin-bottom: 30px;

}

.success {

    color: green;

}

.error {

    color: red;

}

.warning-message {

    color: red;

    font-size: 18px;

    font-weight: bold;

    margin-top: 10px;

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

<section class="edit-container">

<h2>
Edit Employee Details
</h2>

<?php

if ($success_message != "") {

    echo
    "<div class='message success'>
    $success_message
    </div>";

}

if ($error_message != "") {

    echo
    "<div class='message error'>
    $error_message
    </div>";

}

?>

<form method="POST">

<div class="form-group">

<label>
Employee ID
</label>

<input type="text"
       name="employee_id"
       id="employee_id"

       value="<?php echo $employee_id; ?>"

       pattern="[0-9]{6}"
       minlength="6"
       maxlength="6"

       required>

</div>

<div class="button-row">

<button type="submit"
        name="searchEmployee"
        class="search-btn"
        id="searchButton"
        disabled>

Search

</button>

<button type="button"
        class="back-btn"

onclick="window.location.href='admin.php'">

Back

</button>

</div>

<?php

if ($employee_found) {

?>

<hr style="margin:50px 0;">

<div class="form-group">

<label>
Employee Name
</label>

<input type="text"
       name="employee_name"
       id="employee_name"

       value="<?php echo $employee_name; ?>"

       required>

</div>

<div class="form-group">

<label>
Phone Number
</label>

<input type="text"
       name="phone_number"
       id="phone_number"

       value="<?php echo $phone_number; ?>"

       pattern="[0-9]{10}"
       minlength="10"
       maxlength="10"

       required>

<div class="warning-message"
     id="phoneWarning">

</div>

<div class="form-group">

<label>
Date of Birth
</label>

<input type="date"
       name="dob"
       id="dob"

       value="<?php echo $dob; ?>"

       max="<?php echo date('Y-m-d'); ?>"

       required>

</div>

<div class="form-group">

<label>
Category
</label>

<input type="text"
       name="category"
       id="category"

       value="<?php echo $category; ?>"

       required>

</div>

<div class="form-group">

<label>
Address
</label>

<textarea
       name="address"
       id="address"
       rows="4"
       required><?php echo $address; ?></textarea>

</div>

</div>

<div class="button-row">

<button type="button"
        class="update-btn"
        id="updateButton"
        disabled
        style="opacity:0.5; cursor:not-allowed;">

Update Employee

</button>

</div>

<?php

}

?>

<div id="confirmPopup"
     style="
     display:none;
     position:fixed;
     top:0;
     left:0;
     width:100%;
     height:100%;
     background:rgba(0,0,0,0.5);
     justify-content:center;
     align-items:center;
     z-index:9999;
     ">

    <div style="
         background:white;
         padding:40px;
         border-radius:16px;
         width:500px;
         text-align:center;
         ">

        <h2 style="color:#0b5394;">
        Confirm Update
        </h2>

        <p style="
           font-size:22px;
           margin-top:25px;
           margin-bottom:35px;
           ">

        Are you sure you want to update this employee details?

        </p>

        <div style="
             display:flex;
             justify-content:center;
             gap:30px;
             ">

            <button type="button"

            onclick="
            closePopup();
            "

            style="
            width:150px;
            height:60px;
            border:none;
            border-radius:12px;
            background:#dc3545;
            color:white;
            font-size:20px;
            font-weight:bold;
            cursor:pointer;
            ">

            Cancel

            </button>

            <button type="submit"
                    name="updateEmployee"

            style="
            width:150px;
            height:60px;
            border:none;
            border-radius:12px;
            background:#2f944f;
            color:white;
            font-size:20px;
            font-weight:bold;
            cursor:pointer;
            ">

            Yes 

            </button>

        </div>

    </div>

</div>

</form>

</section>

</main>

<footer class="main-footer">

<p>
Ordnance Factory Badmal
</p>

</footer>

<script>

const employeeId =
document.getElementById(
    "employee_id"
);

const searchButton =
document.getElementById(
    "searchButton"
);

employeeId.addEventListener(
    "input",

    function () {

        if (
            /^[0-9]{6}$/.test(
                employeeId.value.trim()
            )
        ) {

            searchButton.disabled =
            false;

        }

        else {

            searchButton.disabled =
            true;

        }

    }
);

const updateButton =
document.getElementById(
    "updateButton"
);

if (updateButton) {

    updateButton.addEventListener(
        "click",

        function () {

            if (
                updateButton.disabled
            ) {
                return;
            }

            document
            .getElementById(
                "confirmPopup"
            )
            .style.display =
            "flex";

        }
    );

}

const phoneInput =
document.getElementById(
    "phone_number"
);

const phoneWarning =
document.getElementById(
    "phoneWarning"
);

const employeeNameInput =
document.getElementById(
    "employee_name"
);

const dobInput =
document.getElementById(
    "dob"
);

const categoryInput =
document.getElementById(
    "category"
);

const addressInput =
document.getElementById(
    "address"
);

const originalValues = {

    employee_name:
    employeeNameInput ?
    employeeNameInput.value.trim() : "",

    phone_number:
    phoneInput ?
    phoneInput.value.trim() : "",

    dob:
    dobInput ?
    dobInput.value.trim() : "",

    category:
    categoryInput ?
    categoryInput.value.trim() : "",

    address:
    addressInput ?
    addressInput.value.trim() : ""

};

function validatePhoneNumber() {

    if (!phoneInput) {
        return;
    }

    const phone =
    phoneInput.value.trim();

    const phoneValid =
    /^[0-9]{10}$/.test(phone);

    if (!phoneValid) {

        phoneWarning.innerHTML =
        "Phone number must contain exactly 10 digits.";

    }

    else {

        phoneWarning.innerHTML =
        "";

    }

    const changed =

        employeeNameInput.value.trim() !==
        originalValues.employee_name ||

        phoneInput.value.trim() !==
        originalValues.phone_number ||

        dobInput.value.trim() !==
        originalValues.dob ||

        categoryInput.value.trim() !==
        originalValues.category ||

        addressInput.value.trim() !==
        originalValues.address;

    if (phoneValid && changed) {

        updateButton.disabled =
        false;

        updateButton.style.opacity =
        "1";

        updateButton.style.cursor =
        "pointer";

    }

    else {

        updateButton.disabled =
        true;

        updateButton.style.opacity =
        "0.5";

        updateButton.style.cursor =
        "not-allowed";

    }

}

if (phoneInput) {

    phoneInput.addEventListener(
        "input",
        validatePhoneNumber
    );

    employeeNameInput.addEventListener(
        "input",
        validatePhoneNumber
    );

    dobInput.addEventListener(
        "input",
        validatePhoneNumber
    );

    categoryInput.addEventListener(
        "input",
        validatePhoneNumber
    );

    addressInput.addEventListener(
        "input",
        validatePhoneNumber
    );

    validatePhoneNumber();

}

function closePopup() {

    document
    .getElementById(
        "confirmPopup"
    )
    .style.display =
    "none";

}

</script>

</body>

</html>