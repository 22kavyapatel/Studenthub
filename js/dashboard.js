// =================================
// GET USER ROLE
// =================================

let userRole = localStorage.getItem("userRole");


// If no role is stored
if (!userRole) {
    userRole = "student";

    localStorage.setItem(
        "userRole",
        userRole
    );
}


// =================================
// GET USER NAME
// =================================

let userName =
    localStorage.getItem("userName");

if (!userName) {
    userName = "User";
}


// =================================
// DISPLAY USER INFORMATION
// =================================

document.getElementById("userRole").textContent =
    userRole.toUpperCase();


document.getElementById("welcomeName").textContent =
    userName;


// =================================
// DASHBOARD DATA
// =================================

const dashboardCards = {

    student: [

        {
            icon: "📚",
            title: "Assignments",
            description: "View and submit assignments.",
            link: "assignments.html"
        },

        {
            icon: "🎉",
            title: "Events",
            description: "Participate in college events.",
            link: "events.html"
        },

        {
            icon: "📢",
            title: "Notices",
            description: "View latest college notices.",
            link: "notices.html"
        },

        {
            icon: "👤",
            title: "My Profile",
            description: "View your profile.",
            link: "profile.html"
        }

    ],


    teacher: [

        {
            icon: "📚",
            title: "Assignments",
            description: "Create and manage assignments.",
            link: "assignments.html"
        },

        {
            icon: "🎉",
            title: "Events",
            description: "Manage college events.",
            link: "events.html"
        },

        {
            icon: "📢",
            title: "Notices",
            description: "Manage college notices.",
            link: "notices.html"
        },

        {
            icon: "📋",
            title: "Attendance",
            description: "Manage student attendance.",
            link: "teacher-attendance.html"
        }

    ],


    admin: [

        {
            icon: "📚",
            title: "Assignments",
            description: "Manage all assignments.",
            link: "assignments.html"
        },

        {
            icon: "🎉",
            title: "Events",
            description: "Manage all college events.",
            link: "events.html"
        },

        {
            icon: "📢",
            title: "Notices",
            description: "Manage college notices.",
            link: "notices.html"
        },

        {
            icon: "👥",
            title: "Students",
            description: "Manage student information.",
            link: "students.html"
        }

    ]

};


// =================================
// CHECK ROLE
// =================================

if (!dashboardCards[userRole]) {

    userRole = "student";

    localStorage.setItem(
        "userRole",
        userRole
    );

}


// =================================
// DISPLAY DASHBOARD CARDS
// =================================

function displayDashboardCards() {

    const container =
        document.getElementById("dashboardCards");


    container.innerHTML = "";


    const cards =
        dashboardCards[userRole];


    cards.forEach(function (card) {

        container.innerHTML += `

            <a
                href="${card.link}"
                class="dashboard-card">

                <div class="dashboard-card-icon">
                    ${card.icon}
                </div>

                <h3>
                    ${card.title}
                </h3>

                <p>
                    ${card.description}
                </p>

            </a>

        `;

    });

}


// =================================
// ROLE CONTENT
// =================================

function displayRoleContent() {

    const container =
        document.getElementById("roleContent");


    if (userRole === "student") {

        container.innerHTML = `

            <div class="role-content-box">

                <h3>
                    Student Dashboard
                </h3>

                <p>
                    Welcome to your StudentHub dashboard.
                    You can manage your academic activities
                    from here.
                </p>

                <ul class="role-list">

                    <li>
                        View and submit assignments
                    </li>

                    <li>
                        Participate in college events
                    </li>

                    <li>
                        Check college notices
                    </li>

                    <li>
                        View your profile
                    </li>

                </ul>

            </div>

        `;

    }


    else if (userRole === "teacher") {

        container.innerHTML = `

            <div class="role-content-box">

                <h3>
                    Teacher Dashboard
                </h3>

                <p>
                    Manage your academic activities
                    from the Teacher Dashboard.
                </p>

                <ul class="role-list">

                    <li>
                        Create and manage assignments
                    </li>

                    <li>
                        Manage college events
                    </li>

                    <li>
                        Publish and manage notices
                    </li>

                    <li>
                        Manage student attendance
                    </li>

                </ul>

            </div>

        `;

    }


    else if (userRole === "admin") {

        container.innerHTML = `

            <div class="role-content-box">

                <h3>
                    Admin Dashboard
                </h3>

                <p>
                    Manage the StudentHub system
                    from the Admin Dashboard.
                </p>

                <ul class="role-list">

                    <li>
                        Manage assignments
                    </li>

                    <li>
                        Manage college events
                    </li>

                    <li>
                        Manage notices
                    </li>

                    <li>
                        Manage student information
                    </li>

                </ul>

            </div>

        `;

    }

}


// =================================
// LOGOUT
// =================================

function logout() {

    localStorage.removeItem("userRole");

    localStorage.removeItem("userName");

    window.location.href =
        "login.html";

}


// =================================
// START DASHBOARD
// =================================

displayDashboardCards();

displayRoleContent();