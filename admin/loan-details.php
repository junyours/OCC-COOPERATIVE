<?php
require('includes/header.php');

if (!isset($_GET['id'])) {
    die("Loan ID missing.");
}

$loan_id = (int) $_GET['id'];



if (!$loan) {
    die("Loan not found.");
}
?>

<style>
    .navbar-brand {
        display: flex;
        align-items: center;
        /* vertically center image + text */
        gap: 0px;
        /* space between logo and text */
        font-weight: 800;
        color: white;
        /* adjust to your navbar color */
        text-decoration: none;
        font-size: 50px;
    }

    .navbar-brand img {
        height: 40px;
        /* adjust logo height */
        width: auto;
        object-fit: contain;
    }

    .navbar-brand span {
        white-space: nowrap;
        /* prevent text from wrapping to next line */
    }
</style>

<body class="layout-boxed navbar-top">
    <!-- Main navbar -->
    <div class="navbar navbar-inverse bg-teal-400 navbar-fixed-top">
        <div class="navbar-header">
 <a class="navbar-brand" href="index.php"><img style="height: 45px!important" src="../images/main_logo.jpg" alt=""><span>OPOL COMMUNITY COLLEGE <br>EMPLOYEES CREDIT COOPERATIVE</span></a>
            <ul class="nav navbar-nav visible-xs-block">
                <li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-tree5"></i></a></li>
            </ul>
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
                            <h4><i class="icon-cash3 position-left"></i> Loan Application Details</h4>
                        </div>
                    </div>

                    <div class="breadcrumb-line">
                        <ul class="breadcrumb">
                            <li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
                            <li><a href="loan.php"><i class="icon-cash3"></i> Loans</a></li>
                            <li class="active">Loan #<?= $loan['loan_id']; ?></li>
                        </ul>
                    </div>
                </div>



                <div class="content">
                    <div class="panel panel-flat">
                        <div class="panel-body">
                            <div class="tabbable">
                                <ul class="nav nav-tabs bg-slate nav-justified">
                                    <li class="active"><a href="#information" data-toggle="tab">Information</a></li>
                                </ul>

                                <div class="tab-content">

                                    <div class="tab-pane active" id="information">
                                        <div class="panel panel-white border-top-xlg border-top-teal-400">
                                            <div class="panel-heading">
                                                <h6 class="panel-title">
                                                    <i class="icon-info3 text-teal-400 position-left"></i> Loan Information
                                                </h6>
                                            </div>
                                            <div class="panel-body">
                                                <table class="table table-bordered table-striped">
                                                    <tr>
                                                        <td><b>Name</b></td>
                                                        <td><?= htmlspecialchars($loan['member_name']); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Amount</b></td>
                                                        <td><?= number_format($loan['requested_amount'], 2); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Term</b></td>
                                                        <td><?= $loan['term_months']; ?> months</td>
                                                    </tr>
                                                    <!-- <tr>
                                                    <td><b>Status</b></td>
                                                    <td><span class="label label-info"><?= ucfirst($loan['status']); ?></span></td>
                                                </tr> -->
                                                    <tr>
                                                        <td><b>Purpose</b></td>
                                                        <td><?= htmlspecialchars($loan['purpose']); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Date Applied</b></td>
                                                        <td><?= $loan['application_date']; ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>




                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php require('includes/footer.php'); ?>
</body>

</html>