<ul class="nav navbar-nav">


    <?php if ($_SESSION['session_type'] == "admin") { ?>

        <li><a href="index.php"><i class="icon-home"></i> &nbsp; Dashboard</a></li>
        <li><a href="products.php"><i class="icon-barcode2"></i> &nbsp; Products</a></li>
        <li><a href="loans.php"><i class="icon-coins"></i> &nbsp; Loan</a></li>

        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="icon-users position-left"></i> People <span class="caret"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
                <li><a href="cashier.php"><i class="icon-dots"></i> Employee</a></li>
                <li><a href="customer.php"><i class="icon-dots"></i> Member</a></li>
                <li><a href="supplier.php"><i class="icon-dots"></i> Supplier</a></li>
            </ul>
        </li>

        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="icon-cart position-left"></i> Transactions <span class="caret"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
                <li><a href="pos.php"><i class="icon-dots"></i> POS</a></li>
                <li><a href="receiving.php"><i class="icon-dots"></i> Receiving</a></li>
                <li><a href="expences.php"><i class="icon-dots"></i> Expenses</a></li>
                <li><a href="loan.php"><i class="icon-dots"></i> Loan</a></li>
                <li><a href="financial.php"><i class="icon-dots"></i> Financial</a></li>
            </ul>
        </li>

        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="icon-chart position-left"></i> Reports <span class="caret"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
                <li><a href="daily_sales.php"><i class="icon-dots"></i> Daily Collection</a></li>
                <li><a href="sales-report.php"><i class="icon-dots"></i> Sales</a></li>
                <li><a href="receiving-report.php"><i class="icon-dots"></i> Receiving</a></li>
                <li><a href="loan-report.php"><i class="icon-dots"></i> Loan</a></li>
                <li><a href="expences-report.php"><i class="icon-dots"></i> Expenses</a></li>
                <li><a href="financial_report.php"><i class="icon-dots"></i> Fincancial Reports</a></li>
                <li class="dropdown-submenu">
                    <a href="#"><i class="icon-dots"></i> Product</a>
                    <ul class="dropdown-menu">
                        <li><a href="product-available.php"><i class="icon-dots"></i> Product Available</a></li>
                        <li><a href="damage-report.php"><i class="icon-dots"></i> Damage</a></li>
                    </ul>
                </li>
                 <li><a href="pdf_dashboard.php"><i class="icon-dots"></i> SOA PDF</a></li>
                <li><a href="system-history.php"><i class="icon-dots"></i> System History</a></li>
                  <li><a href="export_data.php"><i class="icon-dots"></i> Export Data</a></li>
            </ul>
        </li>
           <li><a href="system_settings.php"><i class="icon-cog"></i> System Settings</a></li>

    <?php } ?>



    <?php if ($_SESSION['session_type'] == "treasurer") { ?>

        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="icon-cart position-left"></i> Transactions <span class="caret"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
                <li><a href="loan.php"><i class="icon-dots"></i> Loan</a></li>
                <li><a href="alltransactions.php"><i class="icon-dots"></i> Accounting Terminal</a></li>
            </ul>
        </li>

        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="icon-chart position-left"></i> Reports <span class="caret"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
                <li><a href="loan-report.php"><i class="icon-dots"></i> Loan</a></li>
                <li><a href="daily_sales.php"><i class="icon-dots"></i> Daily Collection</a></li>
                <li><a href="sales-report.php"><i class="icon-dots"></i> Sales</a></li>
            </ul>
        </li>

    <?php } ?>


    <?php if ($_SESSION['session_type'] == "cashier") { ?>


        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="icon-cart position-left"></i> Transactions <span class="caret"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
                <li><a href="pos.php"><i class="icon-dots"></i> POS</a></li>
            </ul>
        </li>

        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="icon-chart position-left"></i> Reports <span class="caret"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
                <li><a href="daily_sales.php"><i class="icon-dots"></i> Daily Collection</a></li>
                <li><a href="sales-report.php"><i class="icon-dots"></i> Sales</a></li>
            </ul>
        </li>

    <?php } ?>

    <?php if ($_SESSION['session_type'] == "member") { ?>

        <li>
            <a href="../member/dashboard.php">
                <i class="icon-home"></i> &nbsp; Dashboard
            </a>
        </li>

        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="icon-cart position-left"></i> Transactions <span class="caret"></span>
            </a>

            <ul class="dropdown-menu dropdown-menu-right">
                <li><a href="../member/savings.php"><i class="icon-dots"></i>Savings</a></li>
                <li><a href="../member/transaction_history.php"><i class="icon-dots"></i>Transaction History</a></li>

                <?php if ($_SESSION['member_type'] == "regular") { ?>
                    <li><a href="loan.php"><i class="icon-dots"></i> Loan</a></li>
                <?php } ?>

            </ul>
        </li>

    <?php } ?>
</ul>

<ul class="nav navbar-nav navbar-right">
    <?php require(__DIR__ . '/user-link.php');
    ?>
</ul>