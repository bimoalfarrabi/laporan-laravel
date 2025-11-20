// resources/js/dashboard-pagination.js

document.addEventListener('DOMContentLoaded', function () {
    const dashboardContent = document.getElementById('dashboard-content');

    if (dashboardContent) {
        dashboardContent.addEventListener('click', function (e) {
            // Use event delegation to find a click on a pagination link
            const paginationLink = e.target.closest('#approved-reports-section nav a');

            if (paginationLink && paginationLink.href) {
                e.preventDefault(); // Prevent default link behavior

                const url = paginationLink.href;
                const approvedReportsSection = document.getElementById('approved-reports-section');

                // Add a loading indicator for better UX
                if (approvedReportsSection) {
                    approvedReportsSection.style.opacity = '0.5';
                }

                // Make an AJAX request
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest' // Identify as AJAX request
                    }
                })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newReportsSection = doc.getElementById('approved-reports-section');

                    if (newReportsSection && approvedReportsSection) {
                        // Replace the old section with the new one
                        approvedReportsSection.parentNode.replaceChild(newReportsSection, approvedReportsSection);
                    } else if (approvedReportsSection) {
                        // If replacement fails, at least restore opacity
                        approvedReportsSection.style.opacity = '1';
                    }
                })
                .catch(error => {
                    console.error('Error fetching approved reports:', error);
                    if (approvedReportsSection) {
                        approvedReportsSection.style.opacity = '1'; // Restore on error
                    }
                });
            }
        });
    }
});
