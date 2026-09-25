/* =========================
   FAQ ACCORDION
========================= */

const faqQuestions = document.querySelectorAll(".faq-question");

faqQuestions.forEach(function (question) {

    question.addEventListener("click", function () {

        const currentItem = question.parentElement;

        // Close all other questions
        document.querySelectorAll(".faq-item").forEach(function (item) {

            if (item !== currentItem) {
                item.classList.remove("open");
            }

        });

        // Open/close clicked question
        currentItem.classList.toggle("open");

    });

});