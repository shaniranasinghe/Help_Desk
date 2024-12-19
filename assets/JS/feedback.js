// Fetch feedback and display dynamically
window.addEventListener("load", function () {
    fetch("get_feedback.php")
        .then((response) => response.json())
        .then((data) => {
            const feedbackList = document.getElementById("feedbackList");
            feedbackList.innerHTML = "";
            data.forEach((feedback) => {
                const feedbackItem = document.createElement("div");
                feedbackItem.className = "feedback-item";
                feedbackItem.innerHTML = `
                    <p><strong>Design:</strong> ${feedback.design}/5</p>
                    <p><strong>Navigation:</strong> ${feedback.navigation}/5</p>
                    <p><strong>Usability:</strong> ${feedback.usability}/5</p>
                    <p>${feedback.feedback}</p>
                `;
                feedbackList.appendChild(feedbackItem);
            });
        });
});
