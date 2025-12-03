(function($) {
    "use strict";

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner();

    // Initiate the wowjs
    new WOW().init();

    // Sticky Navbar
    $(window).scroll(function () {
        if ($(this).scrollTop() > 45) {
            $('.navbar').addClass('sticky-top shadow-sm');
        } else {
            $('.navbar').removeClass('sticky-top shadow-sm');
        }
    });

    // Dropdown on mouse hover
    const $dropdown = $(".dropdown");
    const $dropdownToggle = $(".dropdown-toggle");
    const $dropdownMenu = $(".dropdown-menu");
    const showClass = "show";

    $(window).on("load resize", function() {
        if (this.matchMedia("(min-width: 992px)").matches) {
            $dropdown.hover(
            function() {
                const $this = $(this);
                $this.addClass(showClass);
                $this.find($dropdownToggle).attr("aria-expanded", "true");
                $this.find($dropdownMenu).addClass(showClass);
            },
            function() {
                const $this = $(this);
                $this.removeClass(showClass);
                $this.find($dropdownToggle).attr("aria-expanded", "false");
                $this.find($dropdownMenu).removeClass(showClass);
            });
        } else {
            $dropdown.off("mouseenter mouseleave");
        }
    });

    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });

    // Testimonials carousel
    $(".testimonial-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1000,
        center: true,
        margin: 24,
        dots: true,
        loop: true,
        nav : false,
        responsive: {
            0:{
                items:1
            },
            768:{
                items:2
            },
            992:{
                items:3
            }
        }
    });

    // Vendor carousel
    $('.vendor-carousel').owlCarousel({
        loop: true,
        margin: 29,
        nav: false,
        autoplay: true,
        smartSpeed: 1000,
        responsive: {
            0:{
                items:2
            },
            576:{
                items:3
            },
            768:{
                items:4
            },
            992:{
                items:5
            },
            1200:{
                items:6
            }
        }
    });

    // Chart.js initialization with error checking
    function initializeCharts() {
        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js is not loaded. Skipping chart initialization.');
            return;
        }

        // Worldwide Sales Chart
        var worldwideSalesElement = $("#worldwide-sales");
        if (worldwideSalesElement.length > 0) {
            try {
                var ctx1 = worldwideSalesElement.get(0).getContext("2d");
                var myChart1 = new Chart(ctx1, {
                    type: "bar",
                    data: {
                        labels: ["2016", "2017", "2018", "2019", "2020", "2021", "2022"],
                        datasets: [{
                                label: "USA",
                                backgroundColor: "rgba(0, 156, 255, .7)",
                                data: [15, 30, 55, 65, 60, 80, 95]
                            },
                            {
                                label: "UK",
                                backgroundColor: "rgba(0, 156, 255, .5)",
                                data: [8, 35, 40, 60, 70, 55, 75]
                            },
                            {
                                label: "AU",
                                backgroundColor: "rgba(0, 156, 255, .3)",
                                data: [12, 25, 45, 55, 65, 70, 60]
                            }
                        ]
                    },
                    options: {
                        responsive: true
                    }
                });
            } catch (error) {
                console.error('Error initializing worldwide sales chart:', error);
            }
        }

        // Salse & Revenue Chart
        var salesRevenueElement = $("#salse-revenue");
        if (salesRevenueElement.length > 0) {
            try {
                var ctx2 = salesRevenueElement.get(0).getContext("2d");
                var myChart2 = new Chart(ctx2, {
                    type: "line",
                    data: {
                        labels: ["2016", "2017", "2018", "2019", "2020", "2021", "2022"],
                        datasets: [{
                                label: "Salse",
                                data: [15, 30, 25, 35, 30, 40, 60],
                                borderColor: "rgba(0, 156, 255, .7)",
                                backgroundColor: "rgba(0, 156, 255, .5)",
                                tension: 0.4
                            },
                            {
                                label: "Revenue",
                                data: [40, 60, 40, 90, 40, 80, 70],
                                borderColor: "rgba(0, 156, 255, .7)",
                                backgroundColor: "rgba(0, 156, 255, .3)",
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true
                    }
                });
            } catch (error) {
                console.error('Error initializing sales revenue chart:', error);
            }
        }

        // Single Line Chart
        var singleLineElement = $("#lineChart");
        if (singleLineElement.length > 0) {
            try {
                var ctx3 = singleLineElement.get(0).getContext("2d");
                var myChart3 = new Chart(ctx3, {
                    type: "line",
                    data: {
                        labels: [50, 60, 70, 80, 90, 100, 110, 120, 130, 140, 150],
                        datasets: [{
                            label: "Salse",
                            data: [7, 8, 8, 9, 9, 9, 10, 11, 14, 14, 15],
                            borderColor: "rgba(0, 156, 255, .7)",
                            backgroundColor: "rgba(0, 156, 255, .5)",
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true
                    }
                });
            } catch (error) {
                console.error('Error initializing single line chart:', error);
            }
        }

        // Single Bar Chart
        var singleBarElement = $("#barChart");
        if (singleBarElement.length > 0) {
            try {
                var ctx4 = singleBarElement.get(0).getContext("2d");
                var myChart4 = new Chart(ctx4, {
                    type: "bar",
                    data: {
                        labels: ["Italy", "France", "Spain", "USA", "Argentina"],
                        datasets: [{
                            backgroundColor: [
                                "rgba(0, 156, 255, .7)",
                                "rgba(0, 156, 255, .6)",
                                "rgba(0, 156, 255, .5)",
                                "rgba(0, 156, 255, .4)",
                                "rgba(0, 156, 255, .3)"
                            ],
                            data: [55, 49, 44, 24, 15]
                        }]
                    },
                    options: {
                        responsive: true
                    }
                });
            } catch (error) {
                console.error('Error initializing single bar chart:', error);
            }
        }

        // Pie Chart
        var pieChartElement = $("#pieChart");
        if (pieChartElement.length > 0) {
            try {
                var ctx5 = pieChartElement.get(0).getContext("2d");
                var myChart5 = new Chart(ctx5, {
                    type: "doughnut",
                    data: {
                        labels: ["Italy", "France", "Spain", "USA", "Argentina"],
                        datasets: [{
                            backgroundColor: [
                                "rgba(0, 156, 255, .7)",
                                "rgba(0, 156, 255, .6)",
                                "rgba(0, 156, 255, .5)",
                                "rgba(0, 156, 255, .4)",
                                "rgba(0, 156, 255, .3)"
                            ],
                            data: [55, 49, 44, 24, 15]
                        }]
                    },
                    options: {
                        responsive: true
                    }
                });
            } catch (error) {
                console.error('Error initializing pie chart:', error);
            }
        }

        // Doughnut Chart
        var doughnutChartElement = $("#doughnutChart");
        if (doughnutChartElement.length > 0) {
            try {
                var ctx6 = doughnutChartElement.get(0).getContext("2d");
                var myChart6 = new Chart(ctx6, {
                    type: "doughnut",
                    data: {
                        labels: ["Italy", "France", "Spain", "USA", "Argentina"],
                        datasets: [{
                            backgroundColor: [
                                "rgba(0, 156, 255, .7)",
                                "rgba(0, 156, 255, .6)",
                                "rgba(0, 156, 255, .5)",
                                "rgba(0, 156, 255, .4)",
                                "rgba(0, 156, 255, .3)"
                            ],
                            data: [55, 49, 44, 24, 15]
                        }]
                    },
                    options: {
                        responsive: true
                    }
                });
            } catch (error) {
                console.error('Error initializing doughnut chart:', error);
            }
        }
    }

    // Initialize charts when document is ready
    $(document).ready(function() {
        // Wait a bit for all scripts to load
        setTimeout(initializeCharts, 100);
    });

})(jQuery);
