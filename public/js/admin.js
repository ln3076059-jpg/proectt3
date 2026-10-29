// Admin Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar on mobile
    const sidebarToggler = document.getElementById('sidebarToggle');
    if (sidebarToggler) {
        sidebarToggler.addEventListener('click', function(e) {
            e.preventDefault();
            document.body.classList.toggle('sidebar-toggled');
            document.querySelector('.sidebar').classList.toggle('toggled');
            
            if (document.querySelector('.sidebar .collapse')) {
                document.querySelector('.sidebar .collapse').classList.remove('show');
            }
        });
    }

    // Close any open menu accordions when window is resized below 768px
    window.addEventListener('resize', function() {
        if (window.innerWidth < 768) {
            document.querySelectorAll('.sidebar .collapse').forEach(element => {
                element.classList.remove('show');
            });
        }
        
        // Toggle the side navigation when window is resized below 480px
        if (window.innerWidth < 480 && !document.querySelector('.sidebar').classList.contains('toggled')) {
            document.body.classList.add('sidebar-toggled');
            document.querySelector('.sidebar').classList.add('toggled');
            document.querySelector('.sidebar .collapse').classList.remove('show');
        }
    });

    // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
    document.querySelector('body.fixed-nav .sidebar').addEventListener('mousewheel', function(e) {
        if (this.scrollTop === 0 && e.deltaY < 0) {
            e.preventDefault();
            this.scrollTop = 1;
        } else if (this.scrollTop + this.offsetHeight >= this.scrollHeight && e.deltaY > 0) {
            e.preventDefault();
            this.scrollTop -= 1;
        }
    });

    // Scroll to top button appear
    document.addEventListener('scroll', function() {
        const scrollToTop = document.querySelector('.scroll-to-top');
        if (scrollToTop) {
            if (document.documentElement.scrollTop > 100) {
                scrollToTop.classList.add('active');
            } else {
                scrollToTop.classList.remove('active');
            }
        }
    });

    // Smooth scrolling using jQuery easing
    document.querySelectorAll('a.js-scroll-trigger[href*="#"]:not([href="#"])').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            if (this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 70,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // Toggle dropdown menus on hover for desktop
    if (window.innerWidth > 992) {
        document.querySelectorAll('.navbar .dropdown').forEach(element => {
            element.addEventListener('mouseenter', function() {
                this.querySelector('.dropdown-menu').classList.add('show');
            });
            
            element.addEventListener('mouseleave', function() {
                this.querySelector('.dropdown-menu').classList.remove('show');
            });
        });
    }

    // Activate tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Activate popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Initialize DataTables if available
    if (typeof $.fn.DataTable === 'function') {
        $('.datatable').DataTable({
            responsive: true,
            pageLength: 10,
            lengthChange: false,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Tìm kiếm...",
                paginate: {
                    previous: "Trước",
                    next: "Sau"
                },
                info: "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
                infoEmpty: "Không có dữ liệu",
                infoFiltered: "(được lọc từ _MAX_ mục)",
                zeroRecords: "Không tìm thấy kết quả"
            },
            dom: '<"top"f>rt<"bottom"ip><"clear">',
            initComplete: function() {
                $('.dataTables_filter input').addClass('form-control');
                $('.dataTables_paginate .paginate_button').addClass('btn btn-sm btn-primary');
            }
        });
    }
});

// Flash message auto hide
document.addEventListener('DOMContentLoaded', function() {
    const flashMessages = document.querySelectorAll('.alert');
    
    flashMessages.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Active menu item highlighting
const currentLocation = location.href;
const menuItems = document.querySelectorAll('.sidebar .nav-link');
const menuLength = menuItems.length;

for (let i = 0; i < menuLength; i++) {
    if (menuItems[i].href === currentLocation) {
        menuItems[i].classList.add('active');
    }
}

// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.querySelector(`[onclick="togglePassword('${inputId}')"] i`);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
