
<?php
require_once __DIR__ . "/../php/auth_check.php";
requireRole("admin");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management | StudentHub</title>
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .ajax-container {
            max-width: 1100px;
            margin: 35px auto;
            padding: 20px;
        }

        .ajax-card {
            background: white;
            padding: 24px;
            margin-bottom: 24px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.1);
        }

        .ajax-form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }

        .ajax-form label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        .ajax-form input,
        .ajax-form select {
            box-sizing: border-box;
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .ajax-buttons {
            grid-column: 1 / -1;
            display: flex;
            gap: 10px;
        }

        .ajax-buttons button,
        .ajax-table button {
            padding: 9px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .ajax-table-wrap {
            overflow-x: auto;
        }

        .ajax-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ajax-table th,
        .ajax-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .ajax-table th {
            background: #eef2ff;
        }

        .edit-btn {
            background: #2563eb;
            color: white;
        }

        .delete-btn {
            background: #dc2626;
            color: white;
        }

        .primary-btn {
            background: #2563eb;
            color: white;
        }

        .cancel-btn {
            background: #64748b;
            color: white;
        }

        #feedback {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
            display: none;
        }

        #feedback.success {
            display: block;
            background: #dcfce7;
            color: #166534;
        }

        #feedback.error {
            display: block;
            background: #fee2e2;
            color: #991b1b;
        }

        #loading {
            margin-bottom: 12px;
            font-weight: bold;
        }

        @media (max-width: 650px) {
            .ajax-form {
                grid-template-columns: 1fr;
            }

            .ajax-container {
                padding: 12px;
            }
        }
    </style>
</head>

<body>
<div class="ajax-container">

    <h1>Student Management</h1>
    <p>Manage student records using PHP JSON API and Fetch API.</p>

    <p><a href="admin-dashboard.php">← Back to Admin Dashboard</a></p>

    <div id="feedback" role="status" aria-live="polite"></div>

    <section class="ajax-card">
        <h2 id="formTitle">Add Student</h2>

        <form id="studentForm" class="ajax-form">
            <input type="hidden" id="student_id" name="student_id">

            <div>
                <label for="name">Student Name</label>
                <input type="text" id="name" maxlength="150" required>
            </div>

            <div>
                <label for="email">Email</label>
                <input type="email" id="email" maxlength="254" required>
            </div>

            <div>
                <label for="mobile">Mobile Number</label>
                <input type="tel" id="mobile" pattern="[0-9]{10}"
                       maxlength="10" minlength="10" required>
            </div>

            <div>
                <label for="course">Course</label>
                <input type="text" id="course" maxlength="100" required>
            </div>

            <div>
                <label for="year">Year</label>
                <select id="year" required>
                    <option value="">Select Year</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
            </div>

            <div class="ajax-buttons">
                <button type="submit" class="primary-btn" id="saveBtn">
                    Add Student
                </button>

                <button type="button" class="cancel-btn"
                        id="cancelBtn" hidden>
                    Cancel Edit
                </button>
            </div>
        </form>
    </section>

    <section class="ajax-card">
        <h2>Student Records</h2>

        <div id="loading" aria-live="polite">Loading students...</div>

        <div class="ajax-table-wrap">
            <table class="ajax-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody id="studentTable">
                    <tr>
                        <td colspan="7">Loading students...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
const apiURL = '../php/students_api.php';

const form = document.getElementById('studentForm');
const table = document.getElementById('studentTable');
const feedback = document.getElementById('feedback');
const loading = document.getElementById('loading');

let students = [];
let editing = false;

function showMessage(message, type) {
    feedback.textContent = message;
    feedback.className = type;
}

async function requestAPI(options = {}) {
    const response = await fetch(apiURL, options);
    const result = await response.json();

    if (!response.ok || !result.success) {
        throw new Error(result.message || 'Request failed.');
    }

    return result;
}

function addCell(row, value) {
    const cell = document.createElement('td');
    cell.textContent = value ?? '';
    row.appendChild(cell);
}

function renderStudents() {
    table.replaceChildren();

    if (students.length === 0) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');

        cell.colSpan = 7;
        cell.textContent = 'No students found.';
        row.appendChild(cell);
        table.appendChild(row);
        return;
    }

    students.forEach(student => {
        const row = document.createElement('tr');

        addCell(row, student.student_id);
        addCell(row, student.name);
        addCell(row, student.email);
        addCell(row, student.mobile);
        addCell(row, student.course);
        addCell(row, student.year);

        const actions = document.createElement('td');

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.className = 'edit-btn';
        editButton.textContent = 'Edit';
        editButton.addEventListener('click', () => editStudent(student));

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className = 'delete-btn';
        deleteButton.textContent = 'Delete';
        deleteButton.style.marginLeft = '6px';
        deleteButton.addEventListener('click', () => deleteStudent(student.student_id));

        actions.append(editButton, deleteButton);
        row.appendChild(actions);
        table.appendChild(row);
    });
}

async function loadStudents() {
    loading.textContent = 'Loading students...';

    try {
        const result = await requestAPI();
        students = result.data || [];
        renderStudents();
        loading.textContent = 'Student records loaded.';
    } catch (error) {
        loading.textContent = 'Could not load student records.';
        showMessage(error.message, 'error');
    }
}

function editStudent(student) {
    editing = true;

    document.getElementById('student_id').value = student.student_id;
    document.getElementById('name').value = student.name;
    document.getElementById('email').value = student.email;
    document.getElementById('mobile').value = student.mobile;
    document.getElementById('course').value = student.course;
    document.getElementById('year').value = student.year;

    document.getElementById('formTitle').textContent = 'Update Student';
    document.getElementById('saveBtn').textContent = 'Update Student';
    document.getElementById('cancelBtn').hidden = false;

    form.scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    form.reset();
    document.getElementById('student_id').value = '';
    editing = false;

    document.getElementById('formTitle').textContent = 'Add Student';
    document.getElementById('saveBtn').textContent = 'Add Student';
    document.getElementById('cancelBtn').hidden = true;
}

document.getElementById('cancelBtn').addEventListener('click', resetForm);

form.addEventListener('submit', async function(event) {
    event.preventDefault();

    if (!form.reportValidity()) {
        return;
    }

    const data = {
        action: editing ? 'update' : 'add',
        name: document.getElementById('name').value.trim(),
        email: document.getElementById('email').value.trim(),
        mobile: document.getElementById('mobile').value.trim(),
        course: document.getElementById('course').value.trim(),
        year: document.getElementById('year').value
    };

    if (editing) {
        data.student_id = document.getElementById('student_id').value;
    }

    const saveButton = document.getElementById('saveBtn');
    saveButton.disabled = true;
    saveButton.textContent = 'Saving...';

    try {
        const result = await requestAPI({
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        showMessage(result.message, 'success');
        resetForm();
        await loadStudents();
    } catch (error) {
        showMessage(error.message, 'error');
    } finally {
        saveButton.disabled = false;
        saveButton.textContent = editing ? 'Update Student' : 'Add Student';
    }
});

async function deleteStudent(id) {
    if (!confirm('Are you sure you want to delete this student?')) {
        return;
    }

    try {
        const result = await requestAPI({
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'delete',
                student_id: id
            })
        });

        showMessage(result.message, 'success');

        if (editing && document.getElementById('student_id').value == id) {
            resetForm();
        }

        await loadStudents();
    } catch (error) {
        showMessage(error.message, 'error');
    }
}

loadStudents();
</script>
</body>
</html>
