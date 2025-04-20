$(document).ready(function () {
    $('[data-bs-toggle="collapse"]').on('click', function () {
        var target = $(this).attr('href');
        $(target).collapse('toggle');
    });
    
    // Dropdown menu
    $('.dropdown-toggle').on('click', function(e) {
        e.preventDefault();
        $(this).next('.collapse').collapse('toggle');
    });

    // Active link highlighting
    const currentLocation = window.location.pathname;
    $('.nav-link').each(function() {
        const link = $(this).attr('href');
        if (currentLocation.includes(link)) {
            $(this).addClass('active');
            $(this).closest('.collapse').addClass('show');
        }
    });
});