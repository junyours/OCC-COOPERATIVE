<?php require('includes/header.php'); ?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get date range from GET parameters
$start_date = $_POST['start_date'] ?? $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_POST['end_date'] ?? $_GET['end_date'] ?? date('Y-m-d');
?>

<style>
    .navbar-brand {
        display: flex;
        align-items: center;
        font-weight: 800;
        color: white;
        text-decoration: none;
        font-size: 16px;
        line-height: 1.2;
    }

    .navbar-brand img {
        height: 40px;
        width: auto;
        margin-right: 12px;
        border-radius: 20px;
    }

    .navbar-brand span {
        white-space: nowrap;
    }
    
    .panel {
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .panel-body {
        padding: 20px;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .date-filter {
        background: white;
        padding: 20px;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .breadcrumb-elements {
        margin: 0;
        padding: 0;
        list-style: none;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .breadcrumb-elements li {
        margin-right: 5px;
        margin-bottom: 5px;
    }
    
    .btn-rounded {
        border-radius: 3px;
    }
    
    .panel-white {
        background: white;
        border: 1px solid #ddd;
    }
    
    .border-top-xlg {
        border-top-width: 4px !important;
    }
    
    .border-top-teal-400 {
        border-top-color: #26a69a !important;
    }
    
    .text-teal-400 {
        color: #26a69a !important;
    }
    
    .chart-container {
        height: 300px;
        margin: 20px 0;
    }
</style>

<body class="layout-boxed navbar-top">

   <div class="navbar navbar-inverse bg-primary navbar-fixed-top">
        <div class="navbar-header">
            <a class="navbar-brand" href="index.php">
                <img src="../images/main_logo.jpg" alt="Logo">
                <span>OPOL COMMUNITY COLLEGE <br>EMPLOYEES CREDIT COOPERATIVE</span>
            </a>
        </div>
        <div class="navbar-collapse collapse" id="navbar-mobile">
            <?php require('includes/sidebar.php'); ?>
        </div>
    </div>

<div class="page-container">
    <div class="page-content">
        <div class="content-wrapper">
            <div class="page-header page-header-default">
                <div class="page-header-content">
                    <div class="page-title">
                        <h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Transaction Analytics</span></h4>
                    </div>
                </div>
                <div class="breadcrumb-line">
                    <ul class="breadcrumb">
                        <li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
                        <li><a href="transaction_reports.php"><i class="icon-exchange-alt position-left"></i> Transaction Reports</a></li>
                        <li class="active"><i class="icon-chart-pie position-left"></i> Analytics</li>
                    </ul>
                </div>
            </div>
            
            <div class="content">

<!-- Date Range Filter -->
<div class="panel panel-body ">
    <div>
        <form class="heading-form" method="GET">
            <ul class="breadcrumb-elements" style="float:left">
                <li style="padding-top: 2px;padding-right: 2px">
                    <div class="input-group">
                        <span class="input-group-addon" style="padding: 5px 12px;">
                            <i class="icon-calendar"></i>
                        </span>
                        <input style="width: 180px" type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                    </div>
                </li>
                <li style="padding-top: 2px;padding-right: 2px">
                    <div class="input-group">
                        <span class="input-group-addon" style="padding: 5px 12px;">
                            <i class="icon-calendar"></i>
                        </span>
                        <input style="width: 180px" type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                    </div>
                </li>
                <li data-toggle="tooltip" title="Update Charts" style="padding-top: 2px;padding-right: 2px"><button type="submit" class="btn bg-teal-400"><b><i class="icon-search4"></i></b></button></li>
                <li data-toggle="tooltip" title="Back to Reports" style="padding-top: 2px;padding-right: 2px"><a href="transaction_reports.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn bg-blue-700"><b><i class="icon-arrow-left"></i></b></a></li>
            </ul>
        </form>
    </div>
</div>

            <!-- Charts Row 1 -->
<div class="row">
    <!-- Daily Transaction Trends -->
    <div class="col-md-8">
        <div class="panel panel-white border-top-xlg border-top-teal-400">
            <div class="panel-heading">
                <h6 class="panel-title"><i class="icon-graph text-teal-400"></i> Daily Transaction Trends<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
            </div>
            <div class="panel-body">
                <div class="chart-container">
                    <canvas id="dailyTrendsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Type Distribution -->
    <div class="col-md-4">
        <div class="panel panel-white border-top-xlg border-top-blue-400">
            <div class="panel-heading">
                <h6 class="panel-title"><i class="icon-pie text-blue-400"></i> Transaction Types<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
            </div>
            <div class="panel-body">
                <div class="chart-container">
                    <canvas id="typeDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="row">
    <!-- Account Type Distribution -->
    <div class="col-md-6">
        <div class="panel panel-white border-top-xlg border-top-green-400">
            <div class="panel-heading">
                <h6 class="panel-title"><i class="icon-pie text-green-400"></i> Account Types<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
            </div>
            <div class="panel-body">
                <div class="chart-container">
                    <canvas id="accountDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trends -->
    <div class="col-md-6">
        <div class="panel panel-white border-top-xlg border-top-orange-400">
            <div class="panel-heading">
                <h6 class="panel-title"><i class="icon-bar-graph text-orange-400"></i> Monthly Trends<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
            </div>
            <div class="panel-body">
                <div class="chart-container">
                    <canvas id="monthlyTrendsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Members -->
<div class="row">
    <div class="col-12">
        <div class="panel panel-white border-top-xlg border-top-purple-400">
            <div class="panel-heading">
                <h6 class="panel-title"><i class="icon-users text-purple-400"></i> Top Members by Transaction Volume<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
            </div>
            <div class="panel-body">
                <div class="chart-container">
                    <canvas id="topMembersChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let charts = {};

function updateCharts() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    fetch(`transaction_reports_api.php?start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(data => {
            updateDailyTrendsChart(data.daily_data);
            updateTypeDistributionChart(data.type_distribution);
            updateAccountDistributionChart(data.account_distribution);
            updateMonthlyTrendsChart(data.monthly_trends);
            updateTopMembersChart(data.top_members);
        })
        .catch(error => console.error('Error fetching data:', error));
}

function updateDailyTrendsChart(data) {
    const ctx = document.getElementById('dailyTrendsChart').getContext('2d');
    
    if (charts.dailyTrends) {
        charts.dailyTrends.destroy();
    }
    
    charts.dailyTrends = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.date),
            datasets: [
                {
                    label: 'Deposits',
                    data: data.map(d => d.deposits),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                },
                {
                    label: 'Withdrawals',
                    data: data.map(d => d.withdrawals),
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.1
                },
                {
                    label: 'Loan Payments',
                    data: data.map(d => d.loan_payments),
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ₱' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

function updateTypeDistributionChart(data) {
    const ctx = document.getElementById('typeDistributionChart').getContext('2d');
    
    if (charts.typeDistribution) {
        charts.typeDistribution.destroy();
    }
    
    charts.typeDistribution = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(d => d.type),
            datasets: [{
                data: data.map(d => d.count),
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(153, 102, 255)',
                    'rgb(255, 159, 64)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function updateAccountDistributionChart(data) {
    const ctx = document.getElementById('accountDistributionChart').getContext('2d');
    
    if (charts.accountDistribution) {
        charts.accountDistribution.destroy();
    }
    
    charts.accountDistribution = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.map(d => d.account_type),
            datasets: [{
                data: data.map(d => d.total_amount),
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ₱' + context.parsed.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

function updateMonthlyTrendsChart(data) {
    const ctx = document.getElementById('monthlyTrendsChart').getContext('2d');
    
    if (charts.monthlyTrends) {
        charts.monthlyTrends.destroy();
    }
    
    charts.monthlyTrends = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.month),
            datasets: [
                {
                    label: 'Total Amount',
                    data: data.map(d => d.total_amount),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Transaction Count',
                    data: data.map(d => d.transaction_count),
                    type: 'line',
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
}

function updateTopMembersChart(data) {
    const ctx = document.getElementById('topMembersChart').getContext('2d');
    
    if (charts.topMembers) {
        charts.topMembers.destroy();
    }
    
    charts.topMembers = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.member_name),
            datasets: [{
                label: 'Transaction Count',
                data: data.map(d => d.transaction_count),
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgb(75, 192, 192)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

// Load charts on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCharts();
});
</script>

<?php require('includes/footer.php'); ?>
