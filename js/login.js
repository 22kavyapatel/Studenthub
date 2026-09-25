document.getElementById("loginForm").addEventListener("submit", function (event) {
    event.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;
    const role = document.getElementById("role").value;
    const message = document.getElementById("loginMessage");

    if (email === "" || password === "") {
        message.textContent = "Please enter email and password.";
        message.style.color = "red";
        return;
    }

    if (role === "") {
        message.textContent = "Please select your role.";
        message.style.color = "red";
        return;
    }

    const names = {
        student: "John",
        teacher: "Teacher",
        admin: "Admin"
    };

    localStorage.setItem("userRole", role);
    localStorage.setItem("userName", names[role]);
    localStorage.setItem("studenthubLoggedIn", "true");

    if (document.getElementById("remember").checked) {
        localStorage.setItem("rememberLogin", "true");
    } else {
        localStorage.removeItem("rememberLogin");
    }

    message.textContent = "Login successful! Redirecting...";
    message.style.color = "green";

    setTimeout(function () {
        if (role === "student") {
            window.location.href = "studentdashboard.html";
        } else if (role === "teacher") {
            window.location.href = "teacher-dashboard.html";
        } else if (role === "admin") {
            window.location.href = "admin-dashboard.html";
        }
    }, 400);
});

function togglePassword() {
    const password = document.getElementById("password");
    password.type = password.type === "password" ? "text" : "password";
}
