// Show or hide the password.
function togglePassword() {
    const password = document.getElementById("password");

    password.type =
        password.type === "password" ? "text" : "password";
}

// Display login status messages.
document.addEventListener("DOMContentLoaded", function () {
    const message = document.getElementById("loginMessage");
    const params = new URLSearchParams(window.location.search);

    if (!message) {
        return;
    }

    if (params.get("error") === "invalid") {
        message.textContent =
            "Invalid email, password, or selected role.";
        message.style.color = "red";
    } else if (params.get("error") === "timeout") {
        message.textContent =
            "Your session expired. Please log in again.";
        message.style.color = "red";
    } else if (params.get("logout") === "success") {
        message.textContent =
            "You have logged out successfully.";
        message.style.color = "green";
    }
});