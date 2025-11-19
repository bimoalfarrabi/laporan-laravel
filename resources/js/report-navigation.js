// resources/js/report-navigation.js

// Function to apply EXIF rotation to images
function applyRotation(img) {
    // Ensure the image is loaded before trying to read EXIF data
    if (!img.complete) {
        img.addEventListener('load', () => processRotation(img), { once: true });
    } else {
        processRotation(img);
    }
}

function processRotation(img) {
    try {
        if (typeof EXIF === 'undefined') {
            console.error('EXIF.js is not loaded. Make sure it is included in your app.js bundle.');
            return;
        }
        EXIF.getData(img, function() {
            const orientation = EXIF.getTag(this, 'Orientation');
            let rotation = 0;
            switch (orientation) {
                case 3:
                    rotation = 180;
                    break;
                case 6:
                    rotation = 90;
                    break;
                case 8:
                    rotation = 270;
                    break;
            }
            if (rotation !== 0) {
                // Apply rotation to the image itself, as it's using object-fit
                img.style.transform = `rotate(${rotation}deg)`;
            }
        });
    } catch (e) {
        console.error('Could not apply rotation:', e);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const reportDetailWrapper = document.getElementById('report-detail-wrapper');

    // Initial application of rotation when the page first loads
    if (reportDetailWrapper && typeof EXIF !== 'undefined') {
        const initialImages = reportDetailWrapper.querySelectorAll('.report-image');
        initialImages.forEach(applyRotation);
    }

    if (reportDetailWrapper) {
        reportDetailWrapper.addEventListener('click', function (e) {
            // Check if a navigation button was clicked
            const navButton = e.target.closest('.nav-report-btn');
            if (navButton && navButton.tagName === 'A') {
                e.preventDefault(); // Prevent default link behavior

                const reportId = navButton.dataset.reportId;
                if (!reportId) {
                    console.warn('Navigation button clicked without data-report-id attribute.');
                    return;
                }

                const url = `/reports/${reportId}`; // Assuming route 'reports.show' for AJAX
                
                // Show a loading indicator (optional, but good for UX)
                reportDetailWrapper.style.opacity = '0.5';

                // Make an AJAX request
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest', // Identify as AJAX request
                        'Accept': 'text/html' // Expect HTML back
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    // Find the existing report-detail-container within the current wrapper
                    const existingDetailContainer = reportDetailWrapper.querySelector('#report-detail-container');

                    if (existingDetailContainer) {
                        // Create a temporary div to parse the incoming HTML
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = html;

                        // Find the new report-detail-container in the parsed HTML
                        const newDetailContainer = tempDiv.querySelector('#report-detail-container');

                        if (newDetailContainer) {
                            existingDetailContainer.innerHTML = newDetailContainer.innerHTML;
                            
                            // Update the browser URL without reloading
                            history.pushState(null, '', url);

                            // Optional: Scroll to top of the report details
                            reportDetailWrapper.scrollIntoView({ behavior: 'smooth' });

                            // Dispatch an event so other scripts (like EXIF) can re-initialize for new content
                            document.dispatchEvent(new CustomEvent('reportContentUpdated'));
                        } else {
                            console.error('New report details container not found in AJAX response.');
                        }
                    } else {
                        console.error('Existing report details container not found for update.');
                    }
                })
                .catch(error => {
                    console.error('Error fetching report details:', error);
                    alert('Gagal memuat laporan. Silakan coba lagi.');
                })
                .finally(() => {
                    reportDetailWrapper.style.opacity = '1'; // Hide loading indicator
                });
            }
        });

        // Re-initialize scripts when report content is updated
        document.addEventListener('reportContentUpdated', function() {
            // Re-run EXIF rotation if EXIF.js is loaded
            if (typeof EXIF !== 'undefined') {
                const images = reportDetailWrapper.querySelectorAll('.report-image');
                images.forEach(applyRotation); // assuming applyRotation is a global function or accessible
            }
            // Re-initialize Alpine if necessary for the new content (x-data directives)
            if (typeof Alpine !== 'undefined') {
                Alpine.initTree(reportDetailWrapper);
            }
        });
    }
});
