// =========================
// USER PAGE ELEMENTS
// =========================

const reportForm =
document.getElementById(
    "reportForm"
);

const supplyOverhead =
document.getElementById(
    "supplyOverhead"
);

const fromDate =
document.getElementById(
    "fromDate"
);

const toDate =
document.getElementById(
    "toDate"
);

const submitButton =
document.getElementById(
    "submitButton"
);

const pdfButton =
document.getElementById(
    "pdfButton"
);

const excelButton =
document.getElementById(
    "excelButton"
);

const topMessageBox =
document.getElementById(
    "topMessageBox"
);

const bottomMessageBox =
document.getElementById(
    "bottomMessageBox"
);



// =========================
// USER PAGE LOGIC
// =========================

if (reportForm) {

    // SET MAX DATE

    let today =

    new Date()
    .toISOString()
    .split("T")[0];

    if (fromDate) {

        fromDate.max = today;

    }

    if (toDate) {

        toDate.max = today;

    }



    // ENABLE SUBMIT BUTTON

    function checkFields() {

    // RESET MESSAGES

    topMessageBox.textContent =
        "";

    bottomMessageBox.textContent =
        "";



    if (

        supplyOverhead &&
        fromDate &&
        toDate &&

        supplyOverhead.value.trim() !== "" &&
        fromDate.value !== "" &&
        toDate.value !== ""

    ) {

        // DATE VALIDATION
if (

    fromDate.value >
    toDate.value

) {

    topMessageBox.textContent =

        "From Date cannot be Greater than To Date";

    topMessageBox.style.color =
        "red";

    submitButton.disabled =
        true;

}

else {

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

        topMessageBox.textContent =
        "Cannot generate report for more than 31 days";

        topMessageBox.style.color =
        "red";

        submitButton.disabled =
        true;
        pdfButton.disabled = true;

                excelButton.disabled = true;

    }

    else {

        submitButton.disabled =
        false;

    }

}
    }

    else {

        submitButton.disabled =
            true;

    }
    

}


    if (supplyOverhead) {

        supplyOverhead.addEventListener(
            "change",
            checkFields
        );

    }

    if (fromDate) {

        fromDate.addEventListener(
            "change",
            checkFields
        );

    }

    if (toDate) {

        toDate.addEventListener(
            "change",
            checkFields
        );

    }



    // REPORT BUTTON LOGIC

    if (

        pdfButton &&
        excelButton &&
        submitButton

    ) {

        // SUBMIT BUTTON

  submitButton.addEventListener(

    "click",

    function () {

        bottomMessageBox.textContent =
        "Report ready for download.";

        bottomMessageBox.style.color =
        "green";

        pdfButton.disabled = false;

        excelButton.disabled = false;

    }

);


        // PDF BUTTON

        pdfButton.addEventListener(

            "click",

            function () {

                reportForm.action =
                    "reportgen.php";

                reportForm.method =
                    "GET";

                reportForm.submit();

            }

        );



        // EXCEL BUTTON

        excelButton.addEventListener(

            "click",

            function () {

                reportForm.action =
                    "excelreport.php";

                reportForm.method =
                    "GET";

                reportForm.submit();

            }

        );

    }

}



// =========================
// INDEX PAGE ELEMENTS
// =========================

const adminButton =
document.getElementById(
    "adminButton"
);

const adminBackButton =
document.getElementById(
    "adminBackButton"
);

const homePage =
document.getElementById(
    "homePage"
);

const adminLoginPage =
document.getElementById(
    "adminLoginPage"
);



// =========================
// INDEX PAGE LOGIC
// =========================

if (

    adminButton &&
    adminBackButton &&
    homePage &&
    adminLoginPage

) {

    // SHOW ADMIN LOGIN
adminButton.addEventListener(

    "click",

    function () {

        // CLEAR OLD ERROR MESSAGE

        if (loginErrorMessage) {

            loginErrorMessage.textContent =
                "";

        }

        homePage.style.display =
            "none";

        adminLoginPage.style.display =
            "block";

    }

);



    // BACK TO HOME

    adminBackButton.addEventListener(

    "click",

    function () {

        sessionStorage.removeItem(
            "adminOpen"
        );

        adminLoginPage.style.display =
            "none";

        homePage.style.display =
            "block";

    }

);
}
// =========================
// ADMIN LOGIN BUTTON ENABLE
// =========================

const adminForm =

document.getElementById(
    "adminForm"
);

const adminIdInput =

document.querySelector(
    'input[name="adminId"]'
);

const passwordInput =

document.querySelector(
    'input[name="password"]'
);

const adminLoginButton =

adminForm
? adminForm.querySelector(
    'button[type="submit"]'
)
: null;



if (

    adminForm &&
    adminIdInput &&
    passwordInput &&
    adminLoginButton

) {

    // DISABLE INITIALLY

    adminLoginButton.disabled = true;



    function checkAdminFields() {

        if (

            adminIdInput.value.trim() !== "" &&
            passwordInput.value.trim() !== ""

        ) {

            adminLoginButton.disabled =
                false;

        }

        else {

            adminLoginButton.disabled =
                true;

        }

    }



    adminIdInput.addEventListener(

        "input",

        checkAdminFields

    );



    passwordInput.addEventListener(

        "input",

        checkAdminFields

    );

}
// =========================
// KEEP ADMIN PAGE STATE
// =========================

if (adminButton) {

    adminButton.addEventListener(

        "click",

        function () {

            sessionStorage.setItem(
                "adminOpen",
                "true"
            );

        }

    );

}



if (

    sessionStorage.getItem(
        "adminOpen"
    ) === "true"

) {

    if (

        homePage &&
        adminLoginPage

    ) {

        homePage.style.display =
            "none";

        adminLoginPage.style.display =
            "block";

    }

}
// =========================
// LOGIN ERROR MESSAGE
// =========================

const loginErrorMessage =

document.getElementById(
    "loginErrorMessage"
);



const urlParams =

new URLSearchParams(
    window.location.search
);



if (

    urlParams.get("error") === "1"

) {

    if (loginErrorMessage) {

        loginErrorMessage.textContent =

            "ID or PASSWORD wrong, check carefully!";

    }



    // REMOVE ERROR FROM URL

    window.history.replaceState(

        {},

        document.title,

        window.location.pathname

    );

}
// =========================
// PASSWORD SHOW/HIDE
// =========================

const adminPassword =

document.getElementById(
    "adminPassword"
);

const togglePassword =

document.getElementById(
    "togglePassword"
);

const eyeIcon =

document.getElementById(
    "eyeIcon"
);



if (

    adminPassword &&
    togglePassword &&
    eyeIcon

) {

    togglePassword.addEventListener(

        "click",

        function () {

            if (

                adminPassword.type ===
                "password"

            ) {

                adminPassword.type =
                    "text";

                eyeIcon.src =
                    "eye-slash.png";

            }

            else {

                adminPassword.type =
                    "password";

                eyeIcon.src =
                    "eye.png";

            }

        }

    );

}