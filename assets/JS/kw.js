// Adding subtle hover animation to category links
document.addEventListener("DOMContentLoaded", () => {
    const categoryLinks = document.querySelectorAll(".category ul li a");
    categoryLinks.forEach((link) => {
        link.addEventListener("mouseenter", () => {
            link.style.fontWeight = "bold";
        });
        link.addEventListener("mouseleave", () => {
            link.style.fontWeight = "normal";
        });
    });
});
