/**
 * Scripts for Inicio Page
 */
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const logoContainer = document.querySelector('.logo');
    const mainContent = document.querySelector('.main-content');

    // Ensure we have elements
    if (!sidebar || !logoContainer) return;

    // Link logic is handled by the anchor tag in the HTML now
    if (sidebar && mainContent) {
        // Close sidebar when clicking on main content (if sidebar is active)
        mainContent.addEventListener('click', () => {
            if (sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
    }

    // Add hover effect to cards using JS (optional, CSS often enough but can add tilt)
    const cards = document.querySelectorAll('.menu-card');
    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            // Calculate tilt
            // const centerX = rect.width / 2;
            // const centerY = rect.height / 2;
            // const tiltX = (y - centerY) / 20;
            // const tiltY = (centerX - x) / 20;

            // card.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) scale(1.02)`;
            // This conflicts with CSS hover effects sometimes, keep it simple with CSS
        });

        card.addEventListener('mouseleave', () => {
            // card.style.transform = '';
        });
    });
});
