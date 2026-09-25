(function () {
    const role = localStorage.getItem("userRole");
    const page = window.location.pathname.split("/").pop();

    const rolePages = {
        student: {
            dashboard: "studentdashboard.html",
            assignments: "student-assignments.html",
            attendance: "student-attendance.html",
            notices: "student-notices.html",
            events: "student-events.html",
            profile: "student-profile.html"
        },
        teacher: {
            dashboard: "teacher-dashboard.html",
            assignments: "teacher-assignments.html",
            attendance: "teacher-attendance.html",
            notices: "teacher-notices.html",
            events: "teacher-events.html"
        },
        admin: {
            dashboard: "admin-dashboard.html",
            assignments: "admin-assignments.html",
            attendance: "admin-attendance.html",
            notices: "admin-notices.html",
            events: "admin-events.html"
        }
    };

    const pageType = {
        "studentdashboard.html": "dashboard",
        "teacher-dashboard.html": "dashboard",
        "admin-dashboard.html": "dashboard",
        "student-assignments.html": "assignments",
        "teacher-assignments.html": "assignments",
        "admin-assignments.html": "assignments",
        "student-attendance.html": "attendance",
        "teacher-attendance.html": "attendance",
        "admin-attendance.html": "attendance",
        "student-notices.html": "notices",
        "teacher-notices.html": "notices",
        "admin-notices.html": "notices",
        "student-events.html": "events",
        "teacher-events.html": "events",
        "admin-events.html": "events",
        "student-profile.html": "profile"
    };

    const type = pageType[page];

    if (!type) return;

    if (!role || !rolePages[role]) {
        window.location.replace("login.html");
        return;
    }

    const correctPage = rolePages[role][type];

    if (correctPage && page !== correctPage) {
        window.location.replace(correctPage);
    }
})();

function logoutUser() {
    localStorage.removeItem("userRole");
    localStorage.removeItem("userName");
    localStorage.removeItem("studenthubLoggedIn");
    window.location.href = "login.html";
}
