let students = [];

let currentPage = 1;

const recordsPerPage = 5;


/* -----------------------------
   FETCH STUDENTS
----------------------------- */

async function loadStudents() {

    const loading =
        document.getElementById("loading");

    const error =
        document.getElementById("error");


    try {

        const response =
            await fetch("../data/students.json");


        if (!response.ok) {

            throw new Error(
                "Unable to load students.json"
            );

        }


        students =
            await response.json();


        loading.style.display = "none";


        createCourseOptions();

        displayStudents();

    }

    catch (err) {

        loading.style.display = "none";

        error.style.display = "block";

        error.textContent =
            "Error: " + err.message;

    }

}


/* -----------------------------
   COURSE OPTIONS
----------------------------- */

function createCourseOptions() {

    const select =
        document.getElementById("course");


    const courses =
        [...new Set(
            students.map(function(student) {
                return student.course;
            })
        )];


    courses.forEach(function(course) {

        const option =
            document.createElement("option");

        option.value = course;

        option.textContent = course;

        select.appendChild(option);

    });

}


/* -----------------------------
   FILTER + SEARCH + SORT
----------------------------- */

function getFilteredStudents() {

    const search =
        document
        .getElementById("search")
        .value
        .toLowerCase();


    const course =
        document
        .getElementById("course")
        .value;


    const year =
        document
        .getElementById("year")
        .value;


    const sort =
        document
        .getElementById("sort")
        .value;


    let result =
        students.filter(function(student) {

            const matchesSearch =
                student.name
                .toLowerCase()
                .includes(search) ||

                student.rollNo
                .toLowerCase()
                .includes(search);


            const matchesCourse =
                course === "all" ||
                student.course === course;


            const matchesYear =
                year === "all" ||
                student.year.toString() === year;


            return matchesSearch &&
                   matchesCourse &&
                   matchesYear;

        });


    result.sort(function(a, b) {

        if (sort === "nameAsc") {

            return a.name.localeCompare(b.name);

        }

        if (sort === "nameDesc") {

            return b.name.localeCompare(a.name);

        }

        if (sort === "rollAsc") {

            return a.rollNo.localeCompare(b.rollNo);

        }

    });


    return result;

}


/* -----------------------------
   DISPLAY
----------------------------- */

function displayStudents() {

    const container =
        document.getElementById("students");


    const result =
        getFilteredStudents();


    const start =
        (currentPage - 1) *
        recordsPerPage;


    const pageData =
        result.slice(
            start,
            start + recordsPerPage
        );


    container.innerHTML = "";


    if (pageData.length === 0) {

        container.innerHTML = `
            <div class="empty">
                <h3>No students found</h3>
                <p>
                    Try changing the search or filters.
                </p>
            </div>
        `;

        createPagination(0);

        return;

    }


    pageData.forEach(function(student) {

        container.innerHTML += `

            <div class="card">

                <span class="badge">
                    ${student.course}
                </span>

                <h3>
                    ${student.name}
                </h3>

                <p>
                    <strong>Roll No:</strong>
                    ${student.rollNo}
                </p>

                <p>
                    <strong>Year:</strong>
                    ${student.year}
                </p>

                <p>
                    <strong>Email:</strong>
                    ${student.email}
                </p>

            </div>

        `;

    });


    createPagination(result.length);

}


/* -----------------------------
   PAGINATION
----------------------------- */

function createPagination(totalRecords) {

    const pagination =
        document.getElementById("pagination");


    pagination.innerHTML = "";


    const totalPages =
        Math.ceil(
            totalRecords / recordsPerPage
        );


    if (totalPages <= 1) {
        return;
    }


    const previous =
        document.createElement("button");

    previous.textContent = "Previous";

    previous.disabled =
        currentPage === 1;


    previous.onclick = function() {

        currentPage--;

        displayStudents();

    };


    pagination.appendChild(previous);


    for (
        let i = 1;
        i <= totalPages;
        i++
    ) {

        const button =
            document.createElement("button");

        button.textContent = i;


        if (i === currentPage) {

            button.classList.add("active");

        }


        button.onclick = function() {

            currentPage = i;

            displayStudents();

        };


        pagination.appendChild(button);

    }


    const next =
        document.createElement("button");

    next.textContent = "Next";

    next.disabled =
        currentPage === totalPages;


    next.onclick = function() {

        currentPage++;

        displayStudents();

    };


    pagination.appendChild(next);

}


/* -----------------------------
   EVENTS
----------------------------- */

document
    .getElementById("search")
    .addEventListener(
        "input",
        function() {

            currentPage = 1;

            displayStudents();

        }
    );


document
    .getElementById("course")
    .addEventListener(
        "change",
        function() {

            currentPage = 1;

            displayStudents();

        }
    );


document
    .getElementById("year")
    .addEventListener(
        "change",
        function() {

            currentPage = 1;

            displayStudents();

        }
    );


document
    .getElementById("sort")
    .addEventListener(
        "change",
        function() {

            currentPage = 1;

            displayStudents();

        }
    );


loadStudents();