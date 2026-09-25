/* ================= THEME ================= */

document.addEventListener("DOMContentLoaded", function () {

    const themeButton = document.getElementById("themeToggle");

    // Get saved theme
    const savedTheme = localStorage.getItem("theme");

    // Apply saved theme
    if (savedTheme === "dark") {

        document.body.classList.add("dark-theme");

        if (themeButton) {
            themeButton.textContent = "☀️ Light";
        }

    } else {

        document.body.classList.remove("dark-theme");

        if (themeButton) {
            themeButton.textContent = "🌙 Dark";
        }

    }


    // Theme button
    if (themeButton) {

        themeButton.addEventListener("click", function () {

            document.body.classList.toggle("dark-theme");

            if (document.body.classList.contains("dark-theme")) {

                localStorage.setItem("theme", "dark");

                themeButton.textContent = "☀️ Light";

            } else {

                localStorage.setItem("theme", "light");

                themeButton.textContent = "🌙 Dark";

            }

        });

    }

});


/* ================= LOGIN CHECK ================= */

function checkLogin(event, page) {

    event.preventDefault();

    const loggedIn =
        localStorage.getItem("studenthubLoggedIn") === "true";

    if (loggedIn) {

        window.location.href = page;

    } else {

        const popup =
            document.getElementById("loginPopup");

        if (popup) {

            popup.classList.add("show");

        } else {

            alert("Please login first!");

        }

    }

}


/* ================= CLOSE LOGIN POPUP ================= */

function closeLoginPopup() {

    const popup =
        document.getElementById("loginPopup");

    if (popup) {

        popup.classList.remove("show");

    }

}