// ===============================
// DEFAULT STUDENT DATA
// ===============================

const defaultProfile = {

    name: "Kavya Patel",

    email: "kavya@example.com",

    phone: "+91 98765 43210",

    dob: "2005-01-15",

    gender: "Female",

    address: "Gujarat, India",

    studentId: "STU-001",

    program: "B.Tech Information Technology",

    department: "Information Technology",

    semester: "3",

    gpa: "3.8"

};


// ===============================
// GET PROFILE
// ===============================

let profile =
    JSON.parse(localStorage.getItem("studentProfile"))
    || defaultProfile;


// ===============================
// DISPLAY PROFILE
// ===============================

function displayProfile() {

    document.getElementById("profileName").textContent =
        profile.name;

    document.getElementById("welcomeName").textContent =
        profile.name;

    document.getElementById("profileEmail").textContent =
        profile.email;

    document.getElementById("profilePhone").textContent =
        profile.phone;

    document.getElementById("profileDob").textContent =
        formatDate(profile.dob);

    document.getElementById("profileGender").textContent =
        profile.gender;

    document.getElementById("profileAddress").textContent =
        profile.address;

    document.getElementById("semester").textContent =
        profile.semester;

}


// ===============================
// DATE FORMAT
// ===============================

function formatDate(date) {

    if (!date) {
        return "Not provided";
    }

    const d = new Date(date);

    return d.toLocaleDateString("en-IN", {
        day: "2-digit",
        month: "long",
        year: "numeric"
    });

}


// ===============================
// EDIT PROFILE
// ===============================

const editBtn =
    document.getElementById("editBtn");

const editCard =
    document.getElementById("editCard");

const cancelBtn =
    document.getElementById("cancelBtn");


editBtn.addEventListener("click", function () {

    editCard.classList.add("show");

    document.getElementById("nameInput").value =
        profile.name;

    document.getElementById("emailInput").value =
        profile.email;

    document.getElementById("phoneInput").value =
        profile.phone;

    document.getElementById("dobInput").value =
        profile.dob;

    document.getElementById("genderInput").value =
        profile.gender;

    document.getElementById("semesterInput").value =
        profile.semester;

    document.getElementById("addressInput").value =
        profile.address;

    editCard.scrollIntoView({
        behavior: "smooth"
    });

});


// ===============================
// CANCEL
// ===============================

cancelBtn.addEventListener("click", function () {

    editCard.classList.remove("show");

});


// ===============================
// SAVE PROFILE
// ===============================

const profileForm =
    document.getElementById("profileForm");


profileForm.addEventListener("submit", function (e) {

    e.preventDefault();


    profile.name =
        document.getElementById("nameInput").value;

    profile.email =
        document.getElementById("emailInput").value;

    profile.phone =
        document.getElementById("phoneInput").value;

    profile.dob =
        document.getElementById("dobInput").value;

    profile.gender =
        document.getElementById("genderInput").value;

    profile.semester =
        document.getElementById("semesterInput").value;

    profile.address =
        document.getElementById("addressInput").value;


    // Save to localStorage

    localStorage.setItem(
        "studentProfile",
        JSON.stringify(profile)
    );


    // Update page

    displayProfile();


    editCard.classList.remove("show");


    alert("Profile updated successfully!");

});


// ===============================
// LOGOUT
// ===============================

document
    .getElementById("logoutBtn")
    .addEventListener("click", function (e) {

        const confirmLogout =
            confirm("Are you sure you want to logout?");

        if (!confirmLogout) {
            e.preventDefault();
        }

    });


// ===============================
// LOAD PROFILE
// ===============================

displayProfile();