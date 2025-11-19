// resources/js/dashboard-pagination.js

document.addEventListener('DOMContentLoaded', function () {
    const approvedReportsSection = document.getElementById('approved-reports-section');

    if (approvedReportsSection) {
        approvedReportsSection.addEventListener('click', function (e) {
            // Check if a pagination link (an <a> tag inside a <nav> that contains pagination links) was clicked
            if (e.target.tagName === 'A' && e.target.closest('nav') && e.target.href) {
                e.preventDefault(); // Prevent default link behavior

                const url = e.target.href;

                // Make an AJAX request
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest' // Identify as AJAX request
                    }
                })
                .then(response => {
                    // Laravel's default pagination for Blade views will return a full HTML page
                    // We need to parse this HTML to extract only the approvedReportsSection content.
                    // A more robust solution for larger applications would be to have a dedicated
                    // API endpoint returning JSON or a partial view.
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
        });
    }
});
