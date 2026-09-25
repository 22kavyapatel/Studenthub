// ==========================================
// PRACTICAL 6 - EVENTS
// Fetch API + Search + Filter + Sort + Pagination
// Role Based Student / Teacher / Admin
// ==========================================


// All events
let events = [];

// Current page
let currentPage = 1;

// Events per page
const recordsPerPage = 6;


// Get logged-in role
let userRole = localStorage.getItem("userRole");

// If no role is stored
if (!userRole) {
    userRole = "student";
}


// Convert role to proper display
let displayRole = userRole.charAt(0).toUpperCase() + userRole.slice(1);

document.getElementById("userRole").textContent = displayRole;


// DOM elements
const eventsGrid = document.getElementById("eventsGrid");
const searchInput = document.getElementById("searchInput");
const categoryFilter = document.getElementById("categoryFilter");
const sortSelect = document.getElementById("sortSelect");
const pagination = document.getElementById("pagination");
const loading = document.getElementById("loading");
const errorMessage = document.getElementById("errorMessage");


// ==========================================
// FETCH EVENTS
// ==========================================

async function loadEvents() {

    try {

        loading.style.display = "block";
        errorMessage.style.display = "none";

        const response = await fetch("../data/events.json");

        if (!response.ok) {
            throw new Error("Unable to load events.");
        }

        events = await response.json();

        loading.style.display = "none";

        createCategoryFilter();

        displayEvents();

    } catch (error) {

        loading.style.display = "none";

        errorMessage.textContent =
            "Error loading events: " + error.message;

        errorMessage.style.display = "block";
    }
}


// ==========================================
// CREATE CATEGORY FILTER
// ==========================================

function createCategoryFilter() {

    const categories = [];

    events.forEach(function(event) {

        if (!categories.includes(event.category)) {
            categories.push(event.category);
        }

    });

    categories.sort();

    categories.forEach(function(category) {

        const option = document.createElement("option");

        option.value = category;
        option.textContent = category;

        categoryFilter.appendChild(option);

    });
}


// ==========================================
// GET FILTERED EVENTS
// ==========================================

function getFilteredEvents() {

    let result = [...events];

    // Search
    const searchText =
        searchInput.value.toLowerCase().trim();

    if (searchText !== "") {

        result = result.filter(function(event) {

            return (
                event.title.toLowerCase().includes(searchText) ||
                event.description.toLowerCase().includes(searchText) ||
                event.organizer.toLowerCase().includes(searchText)
            );

        });

    }


    // Category
    const selectedCategory =
        categoryFilter.value;

    if (selectedCategory !== "all") {

        result = result.filter(function(event) {

            return event.category === selectedCategory;

        });

    }


    // Sort
    const sortValue = sortSelect.value;

    if (sortValue === "dateAsc") {

        result.sort(function(a, b) {

            return new Date(a.date) - new Date(b.date);

        });

    }

    else if (sortValue === "dateDesc") {

        result.sort(function(a, b) {

            return new Date(b.date) - new Date(a.date);

        });

    }

    else if (sortValue === "titleAsc") {

        result.sort(function(a, b) {

            return a.title.localeCompare(b.title);

        });

    }

    else if (sortValue === "titleDesc") {

        result.sort(function(a, b) {

            return b.title.localeCompare(a.title);

        });

    }


    return result;
}


// ==========================================
// DISPLAY EVENTS
// ==========================================

function displayEvents() {

    const filteredEvents = getFilteredEvents();

    eventsGrid.innerHTML = "";


    if (filteredEvents.length === 0) {

        eventsGrid.innerHTML = `
            <div class="empty-message">
                <h3>No events found</h3>
                <p>Try another search or filter.</p>
            </div>
        `;

        pagination.innerHTML = "";

        return;
    }


    // Total pages
    const totalPages =
        Math.ceil(filteredEvents.length / recordsPerPage);


    // Check current page
    if (currentPage > totalPages) {
        currentPage = totalPages;
    }


    // Starting index
    const startIndex =
        (currentPage - 1) * recordsPerPage;


    // Ending index
    const endIndex =
        startIndex + recordsPerPage;


    // Pagination using slice()
    const pageEvents =
        filteredEvents.slice(startIndex, endIndex);


    // Create cards
    pageEvents.forEach(function(event) {

        const card =
            createEventCard(event);

        eventsGrid.appendChild(card);

    });


    createPagination(totalPages);
}


// ==========================================
// CREATE EVENT CARD
// ==========================================

function createEventCard(event) {

    const card =
        document.createElement("div");

    card.className = "event-card";


    let buttons = "";


    // ======================================
    // STUDENT
    // ======================================

    if (userRole === "student") {

        const participated =
            hasStudentParticipated(event.id);


        if (participated) {

            buttons += `
                <button
                    class="event-btn participated-btn"
                    disabled>
                    ✓ Participated
                </button>
            `;

        } else {

            buttons += `
                <button
                    class="event-btn participate-btn"
                    onclick="participateEvent(${event.id})">
                    Participate
                </button>
            `;

        }

    }


    // ======================================
    // TEACHER
    // ======================================

    else if (userRole === "teacher") {

        buttons += `
            <button
                class="event-btn details-btn"
                onclick="viewEvent(${event.id})">
                View Details
            </button>

            <button
                class="event-btn edit-btn"
                onclick="editEvent(${event.id})">
                Edit
            </button>
        `;

    }


    // ======================================
    // ADMIN
    // ======================================

    else if (userRole === "admin") {

        buttons += `
            <button
                class="event-btn details-btn"
                onclick="viewEvent(${event.id})">
                View Details
            </button>

            <button
                class="event-btn edit-btn"
                onclick="editEvent(${event.id})">
                Edit
            </button>

            <button
                class="event-btn delete-btn"
                onclick="deleteEvent(${event.id})">
                Delete
            </button>
        `;

    }


    card.innerHTML = `

        <span class="event-category">
            ${event.category}
        </span>

        <h3>
            ${event.title}
        </h3>

        <div class="event-info">
            📅 ${formatDate(event.date)}
        </div>

        <div class="event-info">
            🕐 ${event.time}
        </div>

        <div class="event-info">
            📍 ${event.venue}
        </div>

        <div class="event-info">
            👤 ${event.organizer}
        </div>

        <div class="event-info">
            👥 Capacity: ${event.capacity}
        </div>

        <p class="event-description">
            ${event.description}
        </p>

        <div>
            ${buttons}
        </div>
    `;


    return card;
}


// ==========================================
// FORMAT DATE
// ==========================================

function formatDate(dateString) {

    const date = new Date(dateString);

    return date.toLocaleDateString("en-IN", {
        day: "2-digit",
        month: "short",
        year: "numeric"
    });
}


// ==========================================
// STUDENT PARTICIPATION
// ==========================================

function participateEvent(eventId) {

    if (userRole !== "student") {
        return;
    }


    let participatedEvents =
        JSON.parse(
            localStorage.getItem("participatedEvents")
        );


    if (!participatedEvents) {
        participatedEvents = [];
    }


    if (!participatedEvents.includes(eventId)) {

        participatedEvents.push(eventId);


        localStorage.setItem(
            "participatedEvents",
            JSON.stringify(participatedEvents)
        );


        alert("You have successfully participated in this event!");

        displayEvents();
    }
}


// ==========================================
// CHECK PARTICIPATION
// ==========================================

function hasStudentParticipated(eventId) {

    const participatedEvents =
        JSON.parse(
            localStorage.getItem("participatedEvents")
        ) || [];


    return participatedEvents.includes(eventId);
}


// ==========================================
// VIEW EVENT
// ==========================================

function viewEvent(eventId) {

    const event =
        events.find(function(item) {
            return item.id === eventId;
        });


    if (!event) {
        return;
    }


    alert(
        "Event: " + event.title +
        "\n\n" +
        "Date: " + formatDate(event.date) +
        "\n" +
        "Time: " + event.time +
        "\n" +
        "Venue: " + event.venue +
        "\n" +
        "Organizer: " + event.organizer +
        "\n\n" +
        event.description
    );
}


// ==========================================
// TEACHER / ADMIN EDIT
// ==========================================

function editEvent(eventId) {

    if (userRole !== "teacher" && userRole !== "admin") {
        return;
    }


    const event =
        events.find(function(item) {
            return item.id === eventId;
        });


    if (!event) {
        return;
    }


    const newTitle =
        prompt("Enter new event title:", event.title);


    if (newTitle === null || newTitle.trim() === "") {
        return;
    }


    event.title = newTitle.trim();


    saveEventsLocally();

    displayEvents();

    alert("Event updated successfully.");
}


// ==========================================
// ADMIN DELETE
// ==========================================

function deleteEvent(eventId) {

    if (userRole !== "admin") {
        return;
    }


    const confirmDelete =
        confirm("Are you sure you want to delete this event?");


    if (!confirmDelete) {
        return;
    }


    events =
        events.filter(function(event) {
            return event.id !== eventId;
        });


    saveEventsLocally();

    displayEvents();

    alert("Event deleted successfully.");
}


// ==========================================
// SAVE LOCAL COPY
// ==========================================

function saveEventsLocally() {

    localStorage.setItem(
        "studentHubEvents",
        JSON.stringify(events)
    );
}


// ==========================================
// PAGINATION
// ==========================================

function createPagination(totalPages) {

    pagination.innerHTML = "";


    // Previous
    const previousButton =
        document.createElement("button");

    previousButton.textContent = "Previous";

    previousButton.disabled =
        currentPage === 1;


    previousButton.onclick = function() {

        if (currentPage > 1) {

            currentPage--;

            displayEvents();
        }
    };


    pagination.appendChild(previousButton);


    // Page numbers
    for (
        let i = 1;
        i <= totalPages;
        i++
    ) {

        const pageButton =
            document.createElement("button");

        pageButton.textContent = i;


        if (i === currentPage) {
            pageButton.classList.add("active");
        }


        pageButton.onclick = function() {

            currentPage = i;

            displayEvents();
        };


        pagination.appendChild(pageButton);
    }


    // Next
    const nextButton =
        document.createElement("button");

    nextButton.textContent = "Next";

    nextButton.disabled =
        currentPage === totalPages;


    nextButton.onclick = function() {

        if (currentPage < totalPages) {

            currentPage++;

            displayEvents();
        }
    };


    pagination.appendChild(nextButton);
}


// ==========================================
// SEARCH
// ==========================================

searchInput.addEventListener(
    "input",
    function() {

        currentPage = 1;

        displayEvents();

    }
);


// ==========================================
// FILTER
// ==========================================

categoryFilter.addEventListener(
    "change",
    function() {

        currentPage = 1;

        displayEvents();

    }
);


// ==========================================
// SORT
// ==========================================

sortSelect.addEventListener(
    "change",
    function() {

        currentPage = 1;

        displayEvents();

    }
);


// ==========================================
// LOAD EVENTS
// ==========================================

loadEvents();