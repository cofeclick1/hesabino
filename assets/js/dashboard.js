$(document).ready(function () {
    
    // تنظیمات پایه برای همه نمودارها
const baseChartOptions = {
    chart: {
        fontFamily: 'IRANSans',
        dir: 'rtl',
        height: 350,
        toolbar: {
            show: true
        }
    },
    tooltip: {
        style: {
            fontSize: '12px',
            fontFamily: 'IRANSans'
        }
    },
    yaxis: {
        labels: {
            style: {
                fontFamily: 'IRANSans',
                fontSize: '12px'
            },
            formatter: function(value) {
                return new Intl.NumberFormat('fa').format(value);
            }
        }
    },
    xaxis: {
        labels: {
            style: {
                fontFamily: 'IRANSans',
                fontSize: '12px'
            }
        }
    }
    };
    
    // نمودار فروش
   let salesChartOptions = {
    ...baseChartOptions,
    chart: {
        ...baseChartOptions.chart,
        type: 'line'
    },
    series: [{
        name: 'فروش',
        data: []
    }],
    stroke: {
        curve: 'smooth',
        width: 3
    },
    tooltip: {
        ...baseChartOptions.tooltip,
        y: {
            formatter: function(value) {
                return new Intl.NumberFormat('fa').format(value) + ' ریال';
            }
        }
    }
};

    let salesChart = new ApexCharts(document.querySelector("#salesChart"), salesChartOptions);
    salesChart.render();

    function updateSalesChart(period = 'week') {
        $.ajax({
            url: 'ajax/get-sales-data.php',
            data: { period: period },
            success: function(response) {
                if (response.data && response.labels) {
                    salesChart.updateOptions({
                        series: [{
                            data: response.data
                        }],
                        xaxis: {
                            categories: response.labels
                        }
                    });
                } else {
                    console.error('Invalid data format for sales chart');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching sales data:', error);
            }
        });
    }

    $('.btn-group .btn').click(function() {
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
        updateSalesChart($(this).data('period'));
    });

    updateSalesChart();

    // نمودار نقدینگی
    let cashFlowChartOptions = {
        chart: {
            type: 'line',
            height: 350
        },
        series: [{
            name: 'نقدینگی',
            data: []
        }],
        xaxis: {
            categories: []
        }
    };

    let cashFlowChart = new ApexCharts(document.querySelector("#cashFlowChart"), cashFlowChartOptions);
    cashFlowChart.render();

    function updateCashFlowChart() {
        $.ajax({
            url: 'ajax/get-cash-flow-data.php',
            success: function(response) {
                if (response.data && response.labels) {
                    cashFlowChart.updateOptions({
                        series: [{
                            data: response.data
                        }],
                        xaxis: {
                            categories: response.labels
                        }
                    });
                } else {
                    console.error('Invalid data format for cash flow chart');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching cash flow data:', error);
            }
        });
    }

    updateCashFlowChart();

    // نمودار درآمد و هزینه‌ها
    let incomeExpenseChartOptions = {
        chart: {
            type: 'pie',
            height: 350
        },
        series: [],
        labels: []
    };

    let incomeExpenseChart = new ApexCharts(document.querySelector("#incomeExpenseChart"), incomeExpenseChartOptions);
    incomeExpenseChart.render();

    function updateIncomeExpenseChart() {
        $.ajax({
            url: 'ajax/get-income-expense-data.php',
            success: function(response) {
                if (response.data && response.labels) {
                    incomeExpenseChart.updateOptions({
                        series: response.data,
                        labels: response.labels
                    });
                } else {
                    console.error('Invalid data format for income and expense chart');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching income and expense data:', error);
            }
        });
    }

    updateIncomeExpenseChart();

    // نمودار بدهکاران
    let debtorsChartOptions = {
        chart: {
            type: 'bar',
            height: 350
        },
        series: [{
            name: 'بدهکاران',
            data: []
        }],
        xaxis: {
            categories: []
        }
    };

    let debtorsChart = new ApexCharts(document.querySelector("#debtorsChart"), debtorsChartOptions);
    debtorsChart.render();

    function updateDebtorsChart() {
        $.ajax({
            url: 'ajax/get-debtors-data.php',
            success: function(response) {
                if (response.data && response.labels) {
                    debtorsChart.updateOptions({
                        series: [{
                            data: response.data
                        }],
                        xaxis: {
                            categories: response.labels
                        }
                    });
                } else {
                    console.error('Invalid data format for debtors chart');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching debtors data:', error);
            }
        });
    }

    updateDebtorsChart();

    // نمودار سود یا زیان
    let profitLossChartOptions = {
        chart: {
            type: 'bar',
            height: 350
        },
        series: [{
            name: 'سود یا زیان',
            data: []
        }],
        xaxis: {
            categories: []
        }
    };

    let profitLossChart = new ApexCharts(document.querySelector("#profitLossChart"), profitLossChartOptions);
    profitLossChart.render();

    function updateProfitLossChart() {
        $.ajax({
            url: 'ajax/get-profit-loss-data.php',
            success: function(response) {
                if (response.data && response.labels) {
                    profitLossChart.updateOptions({
                        series: [{
                            data: response.data
                        }],
                        xaxis: {
                            categories: response.labels
                        }
                    });
                } else {
                    console.error('Invalid data format for profit and loss chart');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching profit and loss data:', error);
            }
        });
    }

    updateProfitLossChart();

    // نمودار چک های دریافتی
    let receivedChequesChartOptions = {
        chart: {
            type: 'bar',
            height: 350
        },
        series: [{
            name: 'چک های دریافتی',
            data: []
        }],
        xaxis: {
            categories: []
        }
    };

    let receivedChequesChart = new ApexCharts(document.querySelector("#receivedChequesChart"), receivedChequesChartOptions);
    receivedChequesChart.render();

    function updateReceivedChequesChart() {
        $.ajax({
            url: 'ajax/get-received-cheques-data.php',
            success: function(response) {
                if (response.data && response.labels) {
                    receivedChequesChart.updateOptions({
                        series: [{
                            data: response.data
                        }],
                        xaxis: {
                            categories: response.labels
                        }
                    });
                } else {
                    console.error('Invalid data format for received cheques chart');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching received cheques data:', error);
            }
        });
    }

    updateReceivedChequesChart();

    // نرخ ارز
    function updateCurrencyRate() {
        $.ajax({
            url: 'https://api.exchangerate-api.com/v4/latest/USD',
            success: function(response) {
                let currencyRateHtml = `
                    <ul>
                        <li>USD: ${response.rates.IRR} IRR</li>
                        <li>EUR: ${response.rates.EUR} IRR</li>
                        <li>GBP: ${response.rates.GBP} IRR</li>
                    </ul>
                `;
                $('#currencyRate').html(currencyRateHtml);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching currency rates:', error);
            }
        });
    }

    updateCurrencyRate();
});