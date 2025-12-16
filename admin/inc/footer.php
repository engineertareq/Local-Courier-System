<footer class="d-footer">
  <div class="row align-items-center justify-content-between">
    <div class="col-auto">
      <p class="mb-0">© 2024 WowDash. All Rights Reserved.</p>
    </div>
    <div class="col-auto">
      <p class="mb-0">Made by <span class="text-primary-600">wowtheme7</span></p>
    </div>
  </div>
</footer>
</main>
  <!-- jQuery library js -->
  <script src="assets/js/lib/jquery-3.7.1.min.js"></script>
  <!-- Bootstrap js -->
  <script src="assets/js/lib/bootstrap.bundle.min.js"></script>
  <!-- Apex Chart js -->
  <script src="assets/js/lib/apexcharts.min.js"></script>
  <!-- Data Table js -->
  <script src="assets/js/lib/dataTables.min.js"></script>
  <!-- Iconify Font js -->
  <script src="assets/js/lib/iconify-icon.min.js"></script>
  <!-- jQuery UI js -->
  <script src="assets/js/lib/jquery-ui.min.js"></script>
  <!-- Vector Map js -->
  <script src="assets/js/lib/jquery-jvectormap-2.0.5.min.js"></script>
  <script src="assets/js/lib/jquery-jvectormap-world-mill-en.js"></script>
  <!-- Popup js -->
  <script src="assets/js/lib/magnifc-popup.min.js"></script>
  <!-- Slick Slider js -->
  <script src="assets/js/lib/slick.min.js"></script>
  <!-- prism js -->
  <script src="assets/js/lib/prism.js"></script>
  <!-- file upload js -->
  <script src="assets/js/lib/file-upload.js"></script>
  <!-- audioplayer -->
  <script src="assets/js/lib/audioplayer.js"></script>
  
  <!-- main js -->
  <script src="assets/js/app.js"></script>

<script>
    // ============================ Sales figure Chart Start ==========================
    var options = {
        series: [{
            name: 'Truck Cargo',
            data: [44, 55, 41, 67, 22, 43, 21, 49, 44, 55, 41, 67]
        }, {
            name: 'Ship Cargo',
            data: [13, 23, 20, 8, 13, 27, 33, 12, 13, 23, 20, 8]
        }, {
            name: 'Car Box',
            data: [11, 17, 15, 15, 21, 14, 15, 13, 11, 17, 15, 15]
        }],
        chart: {
            type: 'bar',
            height: 350,
            stacked: true,
            stackType: '100%',
            toolbar: {
                show: false
            },
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: false,
                columnWidth: '23%',
                endingShape: 'rounded',
            }
        },
        dataLabels: {
            enabled: false
        },
        responsive: [{
            breakpoint: 480,
            options: {
                legend: {
                    position: 'bottom',
                    offsetX: -10,
                    offsetY: 0
                }
            }
        }],
        xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        },
        colors: ['#78D3F8', '#4F70FF', '#FF9F29'], 
        fill: {
            opacity: 1
        },
        legend: {
            show: false,
            position: 'right',
            offsetX: 0,
            offsetY: 50
        },
    };

    var chart = new ApexCharts(document.querySelector("#salesFigureChart"), options);
    chart.render();
    // ============================ Sales figure Chart End ==========================

    // ============================ Multiple series Chart Start ==========================
    var options = {
        series: [20, 22, 28, 10],
        chart: {
            type: 'polarArea',
            height: 250,
        },
        labels: ['Product 1', 'Product 2', 'Product 3', 'Product 4'],
        colors: ['#487FFF', '#FF9F29', '#9935FE', '#EF4A00'],
        stroke: {
            colors: ['#487FFF', '#FF9F29', '#9935FE', '#EF4A00'],
        },
        fill: {
            opacity: 0.8
        },
        legend: {
            show: false,
            position: 'bottom',
            horizontalAlign: 'center' // Align the legend horizontally
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    var chart = new ApexCharts(document.querySelector("#multipleSeriesChart"), options);
    chart.render();
    // ============================ Multiple series Chart End ==========================

    // ============================ Month Order Chart start ==========================
    var options = {
        series: [
            {
                name: 'Actual',
                data: [
                    {
                        x: 'Jan',
                        y: 100,
                        goals: [
                            {
                                name: 'Expected',
                                value: 103,
                                strokeHeight: 3,
                                strokeColor: '#487FFF'
                            }
                        ]
                    },
                    {
                        x: 'Feb',
                        y: 452,
                        goals: [
                            {
                                name: 'Expected',
                                value: 455,
                                strokeHeight: 3,
                                strokeColor: '#487FFF'
                            }
                        ]
                    },
                    {
                        x: 'Mar',
                        y: 303,
                        goals: [
                            {
                                name: 'Expected',
                                value: 306,
                                strokeHeight: 3,
                                strokeColor: '#487FFF'
                            }
                        ]
                    },
                    {
                        x: 'Apr',
                        y: 503,
                        goals: [
                            {
                                name: 'Expected',
                                value: 506,
                                strokeHeight: 3,
                                strokeColor: '#487FFF'
                            }
                        ]
                    },
                    {
                        x: 'May',
                        y: 93,
                        goals: [
                            {
                                name: 'Expected',
                                value: 96,
                                strokeHeight: 3,
                                strokeColor: '#487FFF'
                            }
                        ]
                    },
                    {
                        x: 'Jun',
                        y: 302,
                        goals: [
                            {
                                name: 'Expected',
                                value: 305,
                                strokeHeight: 3,
                                strokeColor: '#487FFF'
                            }
                        ]
                    },
                    {
                        x: 'Jul',
                        y: 452,
                        goals: [
                            {
                                name: 'Expected',
                                value: 455,
                                strokeHeight: 3,
                                strokeColor: '#487FFF'
                            }
                        ]
                    },
                    {
                        x: 'Aug',
                        y: 153,
                        goals: [
                            {
                                name: 'Expected',
                                value: 156,
                                strokeHeight: 3,
                                strokeColor: '#487FFF'
                            }
                        ]
                    },
                    {
                        x: 'Sep',
                        y: 453,
                        goals: [
                            {
                                name: 'Expected',
                                value: 456,
                                strokeHeight: 3,
                                strokeColor: '#487FFF'
                            }
                        ]
                    },
                    {
                        x: 'Oct',
                        y: 103,
                        goals: [
                            {
                                name: 'Expected',
                                value: 106,
                                strokeHeight: 3,
                                strokeColor: '#487FFF'
                            }
                        ]
                    },
                    {
                        x: 'Nov',
                        y: 253,
                        goals: [
                            {
                                name: 'Expected',
                                value: 256,
                                strokeHeight: 3,
                                strokeColor: '#487FFF'
                            }
                        ]
                    },
                    {
                        x: 'Dec',
                        y: 153,
                        goals: [
                            {
                                name: 'Expected',
                                value: 156,
                                strokeHeight: 3,
                                strokeColor: '#487FFF'
                            }
                        ]
                    },
                ]
            }
        ],
        chart: {
            height: 224,
            type: 'bar',
            toolbar: {
                show: false
            },
        },
        plotOptions: {
            bar: {
                columnWidth: '100%'
            }
        },
        colors: ['#C3D5FF'],
        dataLabels: {
            enabled: false
        },
        legend: {
            show: false,
            showForSingleSeries: true,
            customLegendItems: ['Actual', 'Expected'],
            markers: {
                fillColors: ['#C3D5FF', '#487FFF']
            }
        }
    };

    var chart = new ApexCharts(document.querySelector("#monthOrderChart"), options);
    chart.render();
    // ============================ Month Order Chart End ==========================

    // ================================ J Vector Map Start ================================ 
    $('#world-map').vectorMap(
        {
            map: 'world_mill_en',
            backgroundColor: 'transparent',
            borderColor: '#fff',
            borderOpacity: 0.25,
            borderWidth: 0,
            color: '#000000',
            regionStyle: {
                initial: {
                    fill: '#D1D5DB'
                }
            },
            markerStyle: {
                initial: {
                    r: 5,
                    'fill': '#fff',
                    'fill-opacity': 1,
                    'stroke': '#000',
                    'stroke-width': 1,
                    'stroke-opacity': 0.4
                },
            },
            markers: [{
                latLng: [35.8617, 104.1954],
                name: 'China : 250'
            },

            {
                latLng: [25.2744, 133.7751],
                name: 'AustrCalia : 250'
            },

            {
                latLng: [36.77, -119.41],
                name: 'USA : 82%'
            },

            {
                latLng: [55.37, -3.41],
                name: 'UK   : 250'
            },

            {
                latLng: [25.20, 55.27],
                name: 'UAE : 250'
            }],

            series: {
                regions: [{
                    values: {
                        "US": '#487FFF ',
                        "SA": '#487FFF',
                        "AU": '#487FFF',
                        "CN": '#487FFF',
                        "GB": '#487FFF',
                    },
                    attribute: 'fill'
                }]
            },
            hoverOpacity: null,
            normalizeFunction: 'linear',
            zoomOnScroll: false,
            scaleColors: ['#000000', '#000000'],
            selectedColor: '#000000',
            selectedRegions: [],
            enableZoom: false,
            hoverColor: '#fff',
        });
    // ================================ J Vector Map End ================================ 

    // ================================ Bars Up Down (Earning Statistics) chart Start ================================ 
    var options = {
        series: [
            {
                name: "Income",
                data: [44, 42, 57, 86, 58, 55, 70, 44, 42, 57, 86, 58, 55, 70],
            },
            {
                name: "Expenses",
                data: [-34, -22, -37, -56, -21, -35, -60, -34, -22, -37, -56, -21, -35, -60],
            },
        ],
        chart: {
            stacked: true,
            type: "bar",
            height: 64,
            fontFamily: "Poppins, sans-serif",
            toolbar: {
                show: false,
            },
            sparkline: {
                enabled: true // Remove whitespace
            },
        },
        colors: ["#9935fe26", "#9935FE"],
        plotOptions: {
            bar: {
                columnWidth: "8",
                borderRadius: [2],
                borderRadiusWhenStacked: "all",
            },
        },
        stroke: {
            width: [5, 5]
        },
        dataLabels: {
            enabled: false,
        },
        legend: {
            show: false,
            position: "top",
        },
        yaxis: {
            show: false,
            title: {
                text: undefined,
            },
            labels: {
                formatter: function (y) {
                    return y.toFixed(0) + "";
                },
            },
        },
        xaxis: {
            categories: [
                "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun",
                "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"
            ],
            show: false,
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false,
            },
            labels: {
                show: false,
                style: {
                    colors: "#d4d7d9",
                    fontSize: "10px",
                    fontWeight: 500,
                },
            },
        },
        tooltip: {
            enabled: true,
            shared: true,
            intersect: false,
            theme: "dark",
            x: {
                show: false,
            },
        },
    };
    var chart = new ApexCharts(document.querySelector("#upDownBarchart"), options);
    chart.render();
    // ================================ Bars Up Down (Earning Statistics) chart End ================================ 
</script>

</body>

</html>