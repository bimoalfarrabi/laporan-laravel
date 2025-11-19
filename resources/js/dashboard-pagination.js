// resources/js/dashboard-pagination.js

document.addEventListener('DOMContentLoaded', function () {
    const approvedReportsSection = document.getElementById('approved-reports-section');

    if (approvedReportsSection) {
        approvedReportsSection.addEventListener('click', function (e) {
            // Check if an <a> tag with an href was clicked within the section
            if (e.target.tagName === 'A' && e.target.href) {
                // Further check to ensure it's a pagination link
                // Laravel's default Tailwind pagination component wraps links in a <nav>
                // So, we check if the clicked <a> tag is inside a <nav> element.
                const navElement = e.target.closest('nav');
                if (navElement) {
                    e.preventDefault(); // Prevent default link behavior

                    const url = e.target.href;

                    // Make an AJAX request
                    fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest' // Identify as AJAX request
                        }
                    })
                    .then(response => {
                        return response.text();
                    })
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newApprovedReportsSection = doc.getElementById('approved-reports-section');

                        if (newApprovedReportsSection) {
                            approvedReportsSection.innerHTML = newApprovedReportsSection.innerHTML;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching approved reports:', error);
                    });
                }
            }
        });
    }
});
