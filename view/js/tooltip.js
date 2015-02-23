$(document).ready(function () {
    // Tooltip only Text
    $('a').each(function () {
        if ($(this).attr("title") && $(this).attr("class") != 'screenshot') {
            $(this).attr("data-title", $(this).attr("title"));
            $(this).removeAttr("title");

            if (!$(this).attr("data-placement")) {
                $(this).attr("data-placement", 'bottom');
            }
            if (!$(this).attr("data-trigger")) {
                $(this).attr("data-trigger", 'hover');
            }
            
            $(this).attr("data-animation", 'pop');
            $(this).addClass("newTooltip");
        }
    });

    var settings = {
        placement: 'auto',
        trigger: 'hover',
        multi: true,
        style: '',
        delay: 300,
        padding: true,
        arrow: true,
        delay: {
            show: null,
            hide: 300
        },
    };
    $('.newTooltip').webuiPopover(settings);

});