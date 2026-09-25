
let allFAQs = [];
let selectedCategory = "All";


// Fetch JSON
fetch("../data/faqs.json")
    .then(response => response.json())
    .then(data => {

        allFAQs = data;

        displayFAQs();

    })
    .catch(error => {

        console.log("Error loading FAQ:", error);

        document.getElementById("faqContainer").innerHTML =
            "<p>Unable to load FAQs.</p>";

    });


// Display FAQs
function displayFAQs() {

    const container = document.getElementById("faqContainer");

    const searchText =
        document.getElementById("searchInput").value.toLowerCase();

    container.innerHTML = "";

    let foundFAQs = 0;


    allFAQs.forEach((faq) => {

        const question =
            faq.question.toLowerCase();

        const answer =
            faq.answer.toLowerCase();


        // Category filter
        let categoryMatch =
            selectedCategory === "All" ||
            faq.category === selectedCategory;


        // Search filter
        let searchMatch =
            question.includes(searchText) ||
            answer.includes(searchText);


        if (categoryMatch && searchMatch) {

            foundFAQs++;


            const faqBox =
                document.createElement("div");

            faqBox.className = "faq-item";


            faqBox.innerHTML = `
                
                <button class="faq-question">

                    <span class="question-text">
                        ${faq.question}
                    </span>

                    <span class="faq-icon">
                        +
                    </span>

                </button>


                <div class="faq-answer">

                    <span class="faq-category">
                        ${faq.category}
                    </span>

                    <p>
                        ${faq.answer}
                    </p>

                </div>

            `;


            container.appendChild(faqBox);


            // Open / Close
            const questionButton =
                faqBox.querySelector(".faq-question");

            questionButton.addEventListener("click", function () {

                const isOpen =
                    faqBox.classList.contains("open");


                // Close all FAQs
                document
                    .querySelectorAll(".faq-item")
                    .forEach(item => {

                        item.classList.remove("open");

                    });


                // Open selected FAQ
                if (!isOpen) {

                    faqBox.classList.add("open");

                }

            });

        }

    });


    // No results
    const noResult =
        document.getElementById("noResult");


    if (foundFAQs === 0) {

        noResult.style.display = "block";

    } else {

        noResult.style.display = "none";

    }

}


// Search
document
    .getElementById("searchInput")
    .addEventListener("input", function () {

        displayFAQs();

    });


// Category buttons
document
    .querySelectorAll(".category-btn")
    .forEach(button => {

        button.addEventListener("click", function () {


            document
                .querySelectorAll(".category-btn")
                .forEach(btn => {

                    btn.classList.remove("active");

                });


            this.classList.add("active");


            selectedCategory =
                this.getAttribute("data-category");


            displayFAQs();

        });

    });

