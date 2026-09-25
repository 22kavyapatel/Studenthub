const attendanceData = [
    {
        subject: "Computer Networks",
        present: 18,
        absent: 2,
        total: 20
    },

    {
        subject: "Web Development",
        present: 19,
        absent: 1,
        total: 20
    },

    {
        subject: "Database Systems",
        present: 17,
        absent: 3,
        total: 20
    },

    {
        subject: "Data Structures",
        present: 18,
        absent: 2,
        total: 20
    }
];


const container = document.getElementById("attendance-container");


attendanceData.forEach(function(data) {

    const percentage = Math.round(
        (data.present / data.total) * 100
    );

    let status = "Good";

    if (percentage < 75) {
        status = "Low";
    } 
    else if (percentage < 85) {
        status = "Average";
    }


    const card = document.createElement("div");

    card.className = "attendance-card";

    card.innerHTML = `
        <div class="attendance-top">

            <div>
                <h3>${data.subject}</h3>

                <div class="attendance-details">

                    <div>
                        <span>Present</span>
                        <strong class="present">${data.present}</strong>
                    </div>

                    <div>
                        <span>Absent</span>
                        <strong class="absent">${data.absent}</strong>
                    </div>

                    <div>
                        <span>Total</span>
                        <strong>${data.total}</strong>
                    </div>

                    <div>
                        <span>Percentage</span>
                        <strong class="percentage">
                            ${percentage}%
                        </strong>
                    </div>

                </div>
            </div>


            <div class="attendance-status">

                <div class="progress-bar">
                    <div
                        class="progress"
                        style="width:${percentage}%">
                    </div>
                </div>

                <p>✓ ${status}</p>

            </div>

        </div>
    `;

    container.appendChild(card);
});