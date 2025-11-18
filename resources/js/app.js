import './bootstrap';
import 'exif-js';


import Alpine from 'alpinejs';
import Swal from 'sweetalert2';

window.Alpine = Alpine;
window.Swal = Swal;


document.addEventListener('DOMContentLoaded', function () {
    document.body.addEventListener('click', function (e) {
        if (e.target.matches('[data-confirm-dialog]')) {
            e.preventDefault();

            const button = e.target;
            const form = button.closest('form');

            Swal.fire({
                title: button.dataset.swalTitle || 'Apakah Anda yakin?',
                text: button.dataset.swalText || "Tindakan ini tidak dapat dibatalkan!",
                icon: button.dataset.swalIcon || 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: button.dataset.swalConfirm || 'Ya, lakukan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    });
});

Alpine.start();
