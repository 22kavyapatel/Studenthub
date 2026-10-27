
// StudentHub Events Page
// Loads events from Admin Event Management through the PHP API.

'use strict';

let events = [];
let currentPage = 1;
const recordsPerPage = 6;

const userRole = (localStorage.getItem('userRole') || 'student').toLowerCase();

const roleElement = document.getElementById('userRole');
const eventsGrid = document.getElementById('eventsGrid');
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const sortSelect = document.getElementById('sortSelect');
const pagination = document.getElementById('pagination');
const loading = document.getElementById('loading');
const errorMessage = document.getElementById('errorMessage');

// Display current user role.
if (roleElement) {
    roleElement.textContent =
        userRole.charAt(0).toUpperCase() + userRole.slice(1);
}

// Load events from the database.
async function loadEvents() {
    if (loading) loading.style.display = 'block';
    if (errorMessage) errorMessage.style.display = 'none';

    try {
        const response = await fetch('../php/get_events.php?t=' + Date.now(), {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            cache: 'no-store'
        });

        if (!response.ok) {
            throw new Error('HTTP error: ' + response.status);
        }

        const payload = await response.json();

        if (
            !payload ||
            payload.success !== true ||
            !Array.isArray(payload.data)
        ) {
            throw new Error('Invalid response from the events API.');
        }

        // Use database events, not data/events.json.
        events = payload.data.map(function (row) {
            return {
                id: Number(row.id),
                title: String(row.title || 'Untitled event'),
                description: String(row.description || ''),
                date: String(row.date || ''),
                time: String(row.time || ''),
                location: String(row.location || ''),
                category: String(row.category || 'General'),
                status: String(row.status || ''),
                poster_url: String(row.poster_url || ''),
                organizer: String(row.organizer || 'StudentHub'),
                capacity: row.capacity
            };
        });

        createCategoryFilter();
        currentPage = 1;
        displayEvents();

        console.log(
            'Events loaded from database:',
            events.map(function (event) {
                return event.title;
            })
        );

    } catch (error) {
        console.error('Failed to load events:', error);

        events = [];

        if (eventsGrid) eventsGrid.replaceChildren();
        if (pagination) pagination.replaceChildren();

        if (errorMessage) {
            errorMessage.textContent =
                'Unable to load events. Check XAMPP and the get_events.php file.';
            errorMessage.style.display = 'block';
        }
    } finally {
        if (loading) loading.style.display = 'none';
    }
}

// Create category options from database events.
function createCategoryFilter() {
    if (!categoryFilter) return;

    const previousValue = categoryFilter.value || 'all';

    categoryFilter.replaceChildren();

    const allOption = document.createElement('option');
    allOption.value = 'all';
    allOption.textContent = 'All Categories';
    categoryFilter.appendChild(allOption);

    const categories = [
        ...new Set(
            events
                .map(function (event) {
                    return event.category;
                })
                .filter(Boolean)
        )
    ].sort(function (a, b) {
        return a.localeCompare(b);
    });

    categories.forEach(function (category) {
        const option = document.createElement('option');
        option.value = category;
        option.textContent = category;
        categoryFilter.appendChild(option);
    });

    if (
        [...categoryFilter.options].some(function (option) {
            return option.value === previousValue;
        })
    ) {
        categoryFilter.value = previousValue;
    }
}

// Search, filter, and sort events.
function getFilteredEvents() {
    let result = events.slice();

    const query = (
        searchInput ? searchInput.value : ''
    ).trim().toLowerCase();

    if (query) {
        result = result.filter(function (event) {
            return [
                event.title,
                event.description,
                event.location,
                event.category,
                event.organizer
            ].some(function (value) {
                return String(value || '')
                    .toLowerCase()
                    .includes(query);
            });
        });
    }

    const selectedCategory = categoryFilter
        ? categoryFilter.value
        : 'all';

    if (selectedCategory !== 'all') {
        result = result.filter(function (event) {
            return event.category === selectedCategory;
        });
    }

    const sort = sortSelect ? sortSelect.value : 'dateAsc';

    if (sort === 'dateAsc') {
        result.sort(function (a, b) {
            return dateValue(a.date) - dateValue(b.date);
        });
    } else if (sort === 'dateDesc') {
        result.sort(function (a, b) {
            return dateValue(b.date) - dateValue(a.date);
        });
    } else if (sort === 'titleAsc') {
        result.sort(function (a, b) {
            return a.title.localeCompare(b.title);
        });
    } else if (sort === 'titleDesc') {
        result.sort(function (a, b) {
            return b.title.localeCompare(a.title);
        });
    }

    return result;
}

// Convert a date into a sortable number.
function dateValue(value) {
    const timestamp = Date.parse(value);
    return Number.isNaN(timestamp) ? 0 : timestamp;
}

// Display events on the page.
function displayEvents() {
    if (!eventsGrid) return;

    const filtered = getFilteredEvents();

    eventsGrid.replaceChildren();

    if (filtered.length === 0) {
        const empty = document.createElement('div');
        empty.className = 'empty-message';

        const heading = document.createElement('h3');
        heading.textContent = events.length
            ? 'No events found'
            : 'No events available';

        const paragraph = document.createElement('p');
        paragraph.textContent = events.length
            ? 'Try another search or category.'
            : 'Events added by the administrator will appear here.';

        empty.append(heading, paragraph);
        eventsGrid.appendChild(empty);

        if (pagination) pagination.replaceChildren();
        return;
    }

    const totalPages = Math.ceil(filtered.length / recordsPerPage);

    currentPage = Math.min(
        Math.max(currentPage, 1),
        totalPages
    );

    const start = (currentPage - 1) * recordsPerPage;
    const pageEvents = filtered.slice(
        start,
        start + recordsPerPage
    );

    pageEvents.forEach(function (event) {
        eventsGrid.appendChild(createEventCard(event));
    });

    createPagination(totalPages);
}

// Create an individual event card.
function createEventCard(event) {
    const card = document.createElement('article');
    card.className = 'event-card';

    if (event.poster_url) {
        const image = document.createElement('img');

        image.src = event.poster_url;
        image.alt = event.title + ' poster';
        image.loading = 'lazy';
        image.className = 'event-poster';

        image.onerror = function () {
            image.remove();
        };

        card.appendChild(image);
    }

    const category = document.createElement('span');
    category.className = 'event-category';
    category.textContent = event.category;

    const title = document.createElement('h3');
    title.textContent = event.title;

    const date = document.createElement('div');
    date.className = 'event-info';
    date.textContent = '📅 ' + formatDate(event.date);

    if (event.time) {
        const time = document.createElement('div');
        time.className = 'event-info';
        time.textContent = '🕐 ' + event.time;
        card.appendChild(category);
        card.appendChild(title);
        card.appendChild(date);
        card.appendChild(time);
    } else {
        card.append(category, title, date);
    }

    const location = document.createElement('div');
    location.className = 'event-info';
    location.textContent =
        '📍 ' + (event.location || 'Location to be announced');

    const organizer = document.createElement('div');
    organizer.className = 'event-info';
    organizer.textContent = '👤 ' + event.organizer;

    const status = document.createElement('div');
    status.className = 'event-info';
    status.textContent = 'Status: ' + (event.status || 'Open');

    const description = document.createElement('p');
    description.className = 'event-description';
    description.textContent = event.description;

    card.append(location, organizer, status, description);

    if (event.capacity !== null && event.capacity !== undefined &&
        event.capacity !== '') {
        const capacity = document.createElement('div');
        capacity.className = 'event-info';
        capacity.textContent = '👥 Capacity: ' + event.capacity;
        card.appendChild(capacity);
    }

    if (userRole === 'student') {
        const button = document.createElement('button');

        button.type = 'button';
        button.className = 'event-btn participate-btn';

        const participated = hasStudentParticipated(event.id);

        button.textContent = participated
            ? '✓ Participated'
            : 'Participate';

        button.disabled = participated;

        button.addEventListener('click', function () {
            participateEvent(event.id);
        });

        card.appendChild(button);
    }

    return card;
}

// Format event date.
function formatDate(dateString) {
    if (!dateString) return 'Date to be announced';

    const date = new Date(
        dateString.length === 10
            ? dateString + 'T00:00:00'
            : dateString
    );

    if (Number.isNaN(date.getTime())) {
        return dateString;
    }

    return date.toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}

// Read participated event IDs from localStorage.
function getParticipatedEvents() {
    try {
        const value = JSON.parse(
            localStorage.getItem('participatedEvents') || '[]'
        );

        return Array.isArray(value)
            ? value.map(Number)
            : [];
    } catch (error) {
        return [];
    }
}

// Check whether the student has participated.
function hasStudentParticipated(eventId) {
    return getParticipatedEvents().includes(Number(eventId));
}

// Mark an event as participated in this browser.
function participateEvent(eventId) {
    if (userRole !== 'student') return;

    const participated = getParticipatedEvents();

    if (!participated.includes(Number(eventId))) {
        participated.push(Number(eventId));

        localStorage.setItem(
            'participatedEvents',
            JSON.stringify(participated)
        );

        alert(
            'Marked as participated in this browser. ' +
            'This is not a server-side registration.'
        );

        displayEvents();
    }
}

// Create pagination buttons.
function createPagination(totalPages) {
    if (!pagination) return;

    pagination.replaceChildren();

    if (totalPages <= 1) return;

    function addButton(label, page, disabled, active) {
        const button = document.createElement('button');

        button.type = 'button';
        button.textContent = label;
        button.disabled = disabled;

        if (active) {
            button.classList.add('active');
        }

        button.addEventListener('click', function () {
            currentPage = page;
            displayEvents();
        });

        pagination.appendChild(button);
    }

    addButton(
        'Previous',
        currentPage - 1,
        currentPage === 1,
        false
    );

    for (let page = 1; page <= totalPages; page++) {
        addButton(
            String(page),
            page,
            false,
            page === currentPage
        );
    }

    addButton(
        'Next',
        currentPage + 1,
        currentPage === totalPages,
        false
    );
}

// Event listeners for controls.
if (searchInput) {
    searchInput.addEventListener('input', function () {
        currentPage = 1;
        displayEvents();
    });
}

if (categoryFilter) {
    categoryFilter.addEventListener('change', function () {
        currentPage = 1;
        displayEvents();
    });
}

if (sortSelect) {
    sortSelect.addEventListener('change', function () {
        currentPage = 1;
        displayEvents();
    });
}

// Start loading events.
loadEvents();
