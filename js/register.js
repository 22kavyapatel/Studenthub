/* =========================
   ELEMENTS
========================= */

const form = document.getElementById("registrationForm");

const nameInput = document.getElementById("name");
const emailInput = document.getElementById("email");
const mobileInput = document.getElementById("mobile");

const courseInput = document.getElementById("course");
const yearInput = document.getElementById("year");

const departmentInput = document.getElementById("department");
const qualificationInput = document.getElementById("qualification");

const passwordInput = document.getElementById("password");
const confirmPasswordInput = document.getElementById("confirmPassword");

const termsInput = document.getElementById("terms");

const studentFields = document.getElementById("studentFields");
const teacherFields = document.getElementById("teacherFields");


/* =========================
   REGEX
========================= */

const nameRegex = /^[A-Za-z ]{2,50}$/;

const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const mobileRegex = /^[6-9][0-9]{9}$/;


/* =========================
   GET ROLE
========================= */

function getRole() {
    const selected = document.querySelector(
        'input[name="role"]:checked'
    );

    return selected ? selected.value : "student";
}


/* =========================
   CHANGE ROLE
========================= */

document
    .querySelectorAll('input[name="role"]')
    .forEach(function (radio) {

        radio.addEventListener("change", function () {

            if (getRole() === "student") {

                studentFields.classList.remove("hidden");
                teacherFields.classList.add("hidden");

            } else {

                studentFields.classList.add("hidden");
                teacherFields.classList.remove("hidden");

            }

            clearRoleErrors();
        });
    });


/* =========================
   CLEAR ROLE ERRORS
========================= */

function clearRoleErrors() {

    document.getElementById("courseError").textContent = "";
    document.getElementById("yearError").textContent = "";
    document.getElementById("departmentError").textContent = "";
    document.getElementById("qualificationError").textContent = "";
}


/* =========================
   ERROR
========================= */

function showError(input, errorId, message) {

    input.classList.remove("valid");
    input.classList.add("invalid");

    document.getElementById(errorId).textContent = message;
}


/* =========================
   SUCCESS
========================= */

function showSuccess(input, errorId) {

    input.classList.remove("invalid");
    input.classList.add("valid");

    document.getElementById(errorId).textContent = "";
}


/* =========================
   NAME
========================= */

function validateName() {

    const value = nameInput.value.trim();

    if (value === "") {

        showError(
            nameInput,
            "nameError",
            "Please enter your full name."
        );

        return false;
    }

    if (!nameRegex.test(value)) {

        showError(
            nameInput,
            "nameError",
            "Name should contain only letters and spaces."
        );

        return false;
    }

    showSuccess(nameInput, "nameError");

    return true;
}


/* =========================
   EMAIL
========================= */

function validateEmail() {

    const value = emailInput.value.trim();

    if (value === "") {

        showError(
            emailInput,
            "emailError",
            "Please enter your email."
        );

        return false;
    }

    if (!emailRegex.test(value)) {

        showError(
            emailInput,
            "emailError",
            "Please enter a valid email address."
        );

        return false;
    }

    showSuccess(emailInput, "emailError");

    return true;
}


/* =========================
   MOBILE
========================= */

function validateMobile() {

    const value = mobileInput.value.trim();

    if (value === "") {

        showError(
            mobileInput,
            "mobileError",
            "Please enter your mobile number."
        );

        return false;
    }

    if (!mobileRegex.test(value)) {

        showError(
            mobileInput,
            "mobileError",
            "Enter a valid 10-digit mobile number."
        );

        return false;
    }

    showSuccess(mobileInput, "mobileError");

    return true;
}


/* =========================
   STUDENT VALIDATION
========================= */

function validateStudentFields() {

    let valid = true;

    if (courseInput.value === "") {

        document.getElementById("courseError").textContent =
            "Please select your course.";

        valid = false;

    } else {

        document.getElementById("courseError").textContent = "";

    }


    if (yearInput.value === "") {

        document.getElementById("yearError").textContent =
            "Please select your year.";

        valid = false;

    } else {

        document.getElementById("yearError").textContent = "";

    }

    return valid;
}


/* =========================
   TEACHER VALIDATION
========================= */

function validateTeacherFields() {

    let valid = true;

    if (departmentInput.value === "") {

        document.getElementById("departmentError").textContent =
            "Please select your department.";

        valid = false;

    } else {

        document.getElementById("departmentError").textContent = "";

    }


    if (qualificationInput.value === "") {

        document.getElementById("qualificationError").textContent =
            "Please select your qualification.";

        valid = false;

    } else {

        document.getElementById("qualificationError").textContent = "";

    }

    return valid;
}


/* =========================
   GENDER
========================= */

function validateGender() {

    const gender = document.querySelector(
        'input[name="gender"]:checked'
    );

    const error = document.getElementById("genderError");

    if (!gender) {

        error.textContent = "Please select your gender.";

        return false;
    }

    error.textContent = "";

    return true;
}


/* =========================
   PASSWORD STRENGTH
========================= */

function getPasswordStrength(password) {

    let score = 0;

    if (password.length >= 8) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    return score;
}


/* =========================
   PASSWORD
========================= */

function validatePassword() {

    const password = passwordInput.value;

    const strength = getPasswordStrength(password);

    const strengthText =
        document.getElementById("passwordStrength");

    const strengthFill =
        document.getElementById("strengthFill");


    if (password === "") {

        showError(
            passwordInput,
            "passwordError",
            "Please enter a password."
        );

        strengthText.textContent =
            "Password strength: Not entered";

        strengthFill.style.width = "0%";

        return false;
    }


    if (password.length < 8) {

        showError(
            passwordInput,
            "passwordError",
            "Password must contain at least 8 characters."
        );

    } else if (strength < 4) {

        showError(
            passwordInput,
            "passwordError",
            "Use uppercase, lowercase, number and special character."
        );

    } else {

        showSuccess(
            passwordInput,
            "passwordError"
        );
    }


    if (strength <= 2) {

        strengthText.textContent =
            "Password strength: Weak";

        strengthFill.style.width = "35%";

    } else if (strength <= 4) {

        strengthText.textContent =
            "Password strength: Medium";

        strengthFill.style.width = "70%";

    } else {

        strengthText.textContent =
            "Password strength: Strong";

        strengthFill.style.width = "100%";
    }


    return (
        password.length >= 8 &&
        strength >= 4
    );
}


/* =========================
   CONFIRM PASSWORD
========================= */

function validateConfirmPassword() {

    const password = passwordInput.value;
    const confirm = confirmPasswordInput.value;


    if (confirm === "") {

        showError(
            confirmPasswordInput,
            "confirmPasswordError",
            "Please confirm your password."
        );

        return false;
    }


    if (password !== confirm) {

        showError(
            confirmPasswordInput,
            "confirmPasswordError",
            "Passwords do not match."
        );

        return false;
    }


    showSuccess(
        confirmPasswordInput,
        "confirmPasswordError"
    );

    return true;
}


/* =========================
   TERMS
========================= */

function validateTerms() {

    const error =
        document.getElementById("termsError");


    if (!termsInput.checked) {

        error.textContent =
            "You must accept the Terms & Conditions.";

        return false;
    }


    error.textContent = "";

    return true;
}


/* =========================
   REAL-TIME VALIDATION
========================= */

nameInput.addEventListener(
    "input",
    validateName
);

emailInput.addEventListener(
    "input",
    validateEmail
);

mobileInput.addEventListener(
    "input",
    validateMobile
);

courseInput.addEventListener(
    "change",
    validateStudentFields
);

yearInput.addEventListener(
    "change",
    validateStudentFields
);

departmentInput.addEventListener(
    "change",
    validateTeacherFields
);

qualificationInput.addEventListener(
    "change",
    validateTeacherFields
);

passwordInput.addEventListener(
    "input",
    function () {

        validatePassword();

        if (confirmPasswordInput.value !== "") {

            validateConfirmPassword();
        }
    }
);

confirmPasswordInput.addEventListener(
    "input",
    validateConfirmPassword
);

termsInput.addEventListener(
    "change",
    validateTerms
);


document
    .querySelectorAll('input[name="gender"]')
    .forEach(function (radio) {

        radio.addEventListener(
            "change",
            validateGender
        );
    });


/* =========================
   SUBMIT
========================= */

form.addEventListener(
    "submit",
    function (event) {

        event.preventDefault();


        const role = getRole();

        const validName = validateName();
        const validEmail = validateEmail();
        const validMobile = validateMobile();
        const validGender = validateGender();
        const validPassword = validatePassword();
        const validConfirm = validateConfirmPassword();
        const validTerms = validateTerms();

        let validRoleFields;


        if (role === "student") {

            validRoleFields =
                validateStudentFields();

        } else {

            validRoleFields =
                validateTeacherFields();
        }


        /* =========================
           ALL VALID
        ========================= */

        if (
            validName &&
            validEmail &&
            validMobile &&
            validGender &&
            validPassword &&
            validConfirm &&
            validTerms &&
            validRoleFields
        ) {

            /*
             * IMPORTANT:
             * Send the form to PHP
             */

            form.submit();

        } else {

            alert(
                "Please correct the errors in the form."
            );
        }
    }
);