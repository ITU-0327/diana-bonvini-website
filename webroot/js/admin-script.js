/**
 * Admin Dashboard JavaScript
 * Handles interactions and behaviors for the admin interface
 */

$(document).ready(function () {
    // Toggle sidebar on menu button click
    $('.toggle-menu').on('click', function () {
        $('.sidebar').toggleClass('collapsed');
        $('.main-content').toggleClass('expanded');
    });

    // Automatically collapse sidebar on smaller screens
    function checkScreenSize()
    {
        if (window.innerWidth < 992) {
            $('.sidebar').addClass('collapsed');
            $('.main-content').addClass('expanded');
        } else {
            $('.sidebar').removeClass('collapsed');
            $('.main-content').removeClass('expanded');
        }
    }

    // Check screen size on initial load
    checkScreenSize();

    // Check screen size on window resize
    $(window).resize(function () {
        checkScreenSize();
    });

    // Handle expandable sidebar submenus (if needed in the future)
    $('.nav-item.has-submenu > a').on('click', function (e) {
        e.preventDefault();
        $(this).parent().toggleClass('submenu-open');
        $(this).siblings('.submenu').slideToggle();
    });

    // Initialize tooltips
    if (typeof $.fn.tooltip !== 'undefined') {
        $('[data-toggle="tooltip"]').tooltip();
    }

    // Initialize popovers
    if (typeof $.fn.popover !== 'undefined') {
        $('[data-toggle="popover"]').popover({
            trigger: 'focus'
        });
    }

    // Handle flash messages auto-dismissal
    setTimeout(function () {
        $('.flash-message').fadeOut(500, function () {
            $(this).remove();
        });
    }, 5000); // Auto dismiss after 5 seconds

    // Confirm deletion dialogs
    $('.confirm-delete').on('click', function (e) {
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            e.preventDefault();
        }
    });

    // Handle form validation (if not using CakePHP's built-in validation)
    $('.needs-validation').on('submit', function (e) {
        if (this.checkValidity() === false) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });

    // Custom file input label update
    $('.custom-file-input').on('change', function () {
        var fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });

    // Initialize DataTables if available and needed
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.datatable').DataTable({
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search..."
            }
        });
    }

    // Function to handle AJAX form submissions
    function handleAjaxForm()
    {
        $('.ajax-form').on('submit', function (e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            var method = form.attr('method') || 'POST';
            var formData = new FormData(this);

            $.ajax({
                url: url,
                type: method,
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    form.find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                },
                success: function (response) {
                    if (response.success) {
                        // Show success message
                        showNotification('success', response.message || 'Action completed successfully');

                        // Redirect if provided
                        if (response.redirect) {
                            setTimeout(function () {
                                window.location.href = response.redirect;
                            }, 1000);
                        } else {
                            // Reset form
                            form[0].reset();
                            form.find('button[type="submit"]').prop('disabled', false).html('Submit');
                        }
                    } else {
                        // Show error message
                        showNotification('error', response.message || 'An error occurred');
                        form.find('button[type="submit"]').prop('disabled', false).html('Submit');
                    }
                },
                error: function (xhr) {
                    var errorMessage = 'An error occurred. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showNotification('error', errorMessage);
                    form.find('button[type="submit"]').prop('disabled', false).html('Submit');
                }
            });
        });
    }

    // Initialize AJAX form handling if needed
    handleAjaxForm();

    // Function to display notifications
    function showNotification(type, message)
    {
        var icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
        var bgClass = type === 'success' ? 'bg-success' : 'bg-danger';

        var notification = $('<div class="notification ' + bgClass + '">' +
            '<i class="fas fa-' + icon + '"></i>' +
            '<span>' + message + '</span>' +
            '<button type="button" class="close" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span>' +
            '</button>' +
            '</div>');

        $('.notification-container').append(notification);

        setTimeout(function () {
            notification.addClass('show');
        }, 100);

        setTimeout(function () {
            notification.removeClass('show');
            setTimeout(function () {
                notification.remove();
            }, 300);
        }, 5000);

        notification.find('.close').on('click', function () {
            notification.removeClass('show');
            setTimeout(function () {
                notification.remove();
            }, 300);
        });
    }
});
