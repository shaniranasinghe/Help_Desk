document.addEventListener("DOMContentLoaded", function () {
    const navbarLinks = document.querySelectorAll('.nav-links a');

    navbarLinks.forEach(link => {
        // Adding click event to toggle the active button
        link.addEventListener('click', function () {
            // Remove 'active' class from all buttons
            navbarLinks.forEach(item => item.classList.remove('active'));
            
            // Add 'active' class to the clicked button
            this.classList.add('active');
        });

        // Optional: Hover effect to make the button more interactive
        link.addEventListener('mouseover', () => {
            link.style.transition = 'all 0.3s ease';
            link.style.color = '#0078d4'; // Change text color on hover
            link.style.transform = 'scale(1.1)'; // Enlarge link slightly
        });

        link.addEventListener('mouseout', () => {
            link.style.transition = 'all 0.3s ease';
            link.style.color = ''; // Revert to default color
            link.style.transform = 'scale(1)'; // Revert scale
        });
    });
});
