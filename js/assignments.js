let assignments = [];

let currentPage = 1;

const recordsPerPage = 6;


// ===============================
// GET USER ROLE
// ===============================

let userRole = localStorage.getItem("userRole");

if (!userRole) {
    userRole = "student";
}

document.getElementById("userRole").textContent =
    userRole.toUpperCase();


// ===============================
// LOAD ASSIGNMENTS
// ===============================

async function loadAssignments() {

    const loading = document.getElementById("loading");
    const errorMessage = document.getElementById("errorMessage");

    try {

        loading.style.display = "block";
        errorMessage.textContent = "";

        const response =
            await fetch("../data/assignments.json");

        if (!response.ok) {
            throw new Error("Unable to load assignments");
        }

        assignments = await response.json();

        createSubjectFilter();

        loading.style.display = "none";

        displayAssignments();

        showTeacherControls();

    } catch (error) {

        loading.style.display = "none";

        errorMessage.textContent =
            "Error loading assignments.";

        console.log(error);

    }

}


// ===============================
// SUBJECT FILTER
// ===============================

function createSubjectFilter() {

    const filter =
        document.getElementById("subjectFilter");

    const subjects = [];

    assignments.forEach(function (assignment) {

        if (!subjects.includes(assignment.subject)) {
            subjects.push(assignment.subject);
        }

    });

    subjects.sort();

    subjects.forEach(function (subject) {

        const option =
            document.createElement("option");

        option.value = subject;
        option.textContent = subject;

        filter.appendChild(option);

    });

}


// ===============================
// FILTER + SEARCH + SORT
// ===============================

function getFilteredAssignments() {

    const search =
        document.getElementById("searchInput")
            .value
            .toLowerCase();

    const subject =
        document.getElementById("subjectFilter").value;

    const sort =
        document.getElementById("sortSelect").value;


    let result = assignments.filter(function (assignment) {

        const matchesSearch =
            assignment.title
                .toLowerCase()
                .includes(search)
            ||
            assignment.description
                .toLowerCase()
                .includes(search)
            ||
            assignment.teacher
                .toLowerCase()
                .includes(search);


        const matchesSubject =
            subject === "all"
            ||
            assignment.subject === subject;


        return matchesSearch && matchesSubject;

    });


    // SORT

    if (sort === "dateAsc") {

        result.sort(function (a, b) {

            return new Date(a.dueDate) -
                   new Date(b.dueDate);

        });

    }


    else if (sort === "dateDesc") {

        result.sort(function (a, b) {

            return new Date(b.dueDate) -
                   new Date(a.dueDate);

        });

    }


    else if (sort === "titleAsc") {

        result.sort(function (a, b) {

            return a.title.localeCompare(b.title);

        });

    }


    else if (sort === "titleDesc") {

        result.sort(function (a, b) {

            return b.title.localeCompare(a.title);

        });

    }


    return result;

}


// ===============================
// DISPLAY ASSIGNMENTS
// ===============================

function displayAssignments() {

    const grid =
        document.getElementById("assignmentsGrid");

    const filteredAssignments =
        getFilteredAssignments();


    const totalPages =
        Math.ceil(
            filteredAssignments.length /
            recordsPerPage
        );


    if (currentPage > totalPages && totalPages > 0) {
        currentPage = totalPages;
    }


    const start =
        (currentPage - 1) *
        recordsPerPage;


    const end =
        start + recordsPerPage;


    const currentAssignments =
        filteredAssignments.slice(start, end);


    grid.innerHTML = "";


    if (currentAssignments.length === 0) {

        grid.innerHTML = `
            <div class="empty-message">
                <h3>No assignments found</h3>
                <p>Try another search or subject.</p>
            </div>
        `;

        document.getElementById("pagination").innerHTML = "";

        return;
    }


    currentAssignments.forEach(function (assignment) {

        grid.innerHTML +=
            createAssignmentCard(assignment);

    });


    createPagination(totalPages);

}


// ===============================
// CREATE ASSIGNMENT CARD
// ===============================

function createAssignmentCard(assignment) {

    let buttons = "";


    // STUDENT

    if (userRole === "student") {

        const submitted =
            hasStudentSubmitted(assignment.id);


        if (submitted) {

            buttons += `
                <button
                    class="assignment-btn submitted-btn"
                    disabled>
                    Submitted
                </button>
            `;

        } else {

            buttons += `
                <button
                    class="assignment-btn submit-btn"
                    onclick="submitAssignment(${assignment.id})">
                    Submit Assignment
                </button>
            `;

        }

    }


    // TEACHER

    else if (userRole === "teacher") {

        buttons += `
            <button
                class="assignment-btn view-btn"
                onclick="viewAssignment(${assignment.id})">
                View
            </button>

            <button
                class="assignment-btn edit-btn"
                onclick="editAssignment(${assignment.id})">
                Edit
            </button>
        `;

    }


    // ADMIN

    else if (userRole === "admin") {

        buttons += `
            <button
                class="assignment-btn view-btn"
                onclick="viewAssignment(${assignment.id})">
                View
            </button>

            <button
                class="assignment-btn edit-btn"
                onclick="editAssignment(${assignment.id})">
                Edit
            </button>

            <button
                class="assignment-btn delete-btn"
                onclick="deleteAssignment(${assignment.id})">
                Delete
            </button>
        `;

    }


    return `
        <div class="assignment-card">

            <span class="assignment-subject">
                ${assignment.subject}
            </span>

            <h3>
                ${assignment.title}
            </h3>

            <p class="assignment-description">
                ${assignment.description}
            </p>

            <p class="assignment-info">
                <strong>Due Date:</strong>
                ${formatDate(assignment.dueDate)}
            </p>

            <p class="assignment-info">
                <strong>Teacher:</strong>
                ${assignment.teacher}
            </p>

            <p class="assignment-info">
                <strong>Status:</strong>
                ${assignment.status}
            </p>

            <div>
                ${buttons}
            </div>

        </div>
    `;

}


// ===============================
// FORMAT DATE
// ===============================

function formatDate(date) {

    const d = new Date(date);

    return d.toLocaleDateString(
        "en-IN",
        {
            day: "2-digit",
            month: "short",
            year: "numeric"
        }
    );

}


// ===============================
// STUDENT SUBMIT
// ===============================

function submitAssignment(assignmentId) {

    let submitted =
        JSON.parse(
            localStorage.getItem("submittedAssignments")
        ) || [];


    if (!submitted.includes(assignmentId)) {

        submitted.push(assignmentId);

        localStorage.setItem(
            "submittedAssignments",
            JSON.stringify(submitted)
        );

        alert("Assignment submitted successfully!");

        displayAssignments();

    }

}


// ===============================
// CHECK SUBMISSION
// ===============================

function hasStudentSubmitted(assignmentId) {

    const submitted =
        JSON.parse(
            localStorage.getItem("submittedAssignments")
        ) || [];


    return submitted.includes(assignmentId);

}


// ===============================
// VIEW ASSIGNMENT
// ===============================

function viewAssignment(assignmentId) {

    const assignment =
        assignments.find(
            function (item) {
                return item.id === assignmentId;
            }
        );


    if (!assignment) {
        return;
    }


    alert(
        "Assignment: " + assignment.title +
        "\n\nSubject: " + assignment.subject +
        "\n\nDescription: " + assignment.description +
        "\n\nDue Date: " + formatDate(assignment.dueDate) +
        "\n\nTeacher: " + assignment.teacher
    );

}


// ===============================
// CREATE ASSIGNMENT - TEACHER
// ===============================

function showTeacherControls() {

    const controls =
        document.getElementById("teacherControls");


    if (userRole === "teacher") {

        controls.innerHTML = `
            <button
                class="create-btn"
                onclick="createAssignment()">
                + Create Assignment
            </button>
        `;

    } else {

        controls.innerHTML = "";

    }

}


// ===============================
// CREATE NEW ASSIGNMENT
// ===============================

function createAssignment() {

    const title =
        prompt("Enter assignment title:");

    if (!title) {
        return;
    }


    const subject =
        prompt("Enter subject:");

    if (!subject) {
        return;
    }


    const description =
        prompt("Enter assignment description:");

    if (!description) {
        return;
    }


    const dueDate =
        prompt("Enter due date (YYYY-MM-DD):");

    if (!dueDate) {
        return;
    }


    const newAssignment = {

        id: Date.now(),

        title: title,

        subject: subject,

        description: description,

        dueDate: dueDate,

        teacher: "Current Teacher",

        status: "Open"

    };


    assignments.push(newAssignment);


    saveAssignmentsLocally();

    createSubjectFilter();

    displayAssignments();

    alert("Assignment created successfully!");

}


// ===============================
// EDIT ASSIGNMENT
// ===============================

function editAssignment(assignmentId) {

    const assignment =
        assignments.find(
            function (item) {
                return item.id === assignmentId;
            }
        );


    if (!assignment) {
        return;
    }


    const newTitle =
        prompt(
            "Enter new title:",
            assignment.title
        );


    if (!newTitle) {
        return;
    }


    assignment.title = newTitle;


    saveAssignmentsLocally();

    displayAssignments();

    alert("Assignment updated successfully!");

}


// ===============================
// DELETE ASSIGNMENT
// ===============================

function deleteAssignment(assignmentId) {

    const confirmDelete =
        confirm(
            "Are you sure you want to delete this assignment?"
        );


    if (!confirmDelete) {
        return;
    }


    assignments =
        assignments.filter(
            function (assignment) {
                return assignment.id !== assignmentId;
            }
        );


    saveAssignmentsLocally();

    displayAssignments();

    alert("Assignment deleted successfully!");

}


// ===============================
// SAVE LOCAL DATA
// ===============================

function saveAssignmentsLocally() {

    localStorage.setItem(
        "studentHubAssignments",
        JSON.stringify(assignments)
    );

}


// ===============================
// PAGINATION
// ===============================

function createPagination(totalPages) {

    const pagination =
        document.getElementById("pagination");


    pagination.innerHTML = "";


    if (totalPages <= 1) {
        return;
    }


    // PREVIOUS

    if (currentPage > 1) {

        pagination.innerHTML += `
            <button
                onclick="changePage(${currentPage - 1})">
                Previous
            </button>
        `;

    }


    // PAGE NUMBERS

    for (
        let i = 1;
        i <= totalPages;
        i++
    ) {

        pagination.innerHTML += `
            <button
                onclick="changePage(${i})">
                ${i}
            </button>
        `;

    }


    // NEXT

    if (currentPage < totalPages) {

        pagination.innerHTML += `
            <button
                onclick="changePage(${currentPage + 1})">
                Next
            </button>
        `;

    }

}


// ===============================
// CHANGE PAGE
// ===============================

function changePage(page) {

    currentPage = page;

    displayAssignments();

}


// ===============================
// EVENT LISTENERS
// ===============================

document
    .getElementById("searchInput")
    .addEventListener(
        "input",
        function () {

            currentPage = 1;

            displayAssignments();

        }
    );


document
    .getElementById("subjectFilter")
    .addEventListener(
        "change",
        function () {

            currentPage = 1;

            displayAssignments();

        }
    );


document
    .getElementById("sortSelect")
    .addEventListener(
        "change",
        function () {

            currentPage = 1;

            displayAssignments();

        }
    );


// ===============================
// START
// ===============================

loadAssignments();