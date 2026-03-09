<?php require('includes/header.php'); ?>

<?php


if (
    !isset($_SESSION['is_login_yes'], $_SESSION['user_id'], $_SESSION['usertype'])
    || $_SESSION['is_login_yes'] != 'yes'
    || !in_array((int)$_SESSION['usertype'], [1, 3])
) {
    die("Unauthorized access.");
}


$query = "
    SELECT
        c.cust_id,
        c.name,
        c.address,
        c.contact,
        m.type AS member_type
    FROM tbl_customer c
    LEFT JOIN tbl_members m ON m.cust_id = c.cust_id
    WHERE c.cust_id != 1
";
$result = $db->query($query);
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

    /* Hover effect for member table rows */
    /* Make member rows pop on hover */
    table.datatable-button-html5-basic tbody tr {
        cursor: pointer;
        transition: all 0.2s ease;
        /* smooth animation */
    }

    table.datatable-button-html5-basic tbody tr:hover {
        background-color: #e0f7fa;
        /* light teal */
        transform: scale(1.02);
        /* slightly bigger */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        /* soft shadow */
    }

    /* Pop effect for breadcrumb links */
    .breadcrumb-elements a {
        display: inline-block;
        /* needed for transform */
        transition: all 0.2s ease;
    }

    .breadcrumb-elements a:hover {
        transform: scale(1.05);
        /* slightly bigger */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        /* subtle shadow */
        border-radius: 5px;
        /* optional: rounded edges for nicer look */
        background-color: rgba(0, 128, 128, 0.1);
        /* subtle background change */
    }
</style>

<body class="layout-boxed navbar-top">
    <!-- Main navbar -->
    <div class="navbar navbar-inverse bg-teal-400 navbar-fixed-top">
        <div class="navbar-header">
            <a class="navbar-brand" href="index.php"><img src="../images/main_logo.jpg" alt=""><span>OPOL COMMUNITY COLLEGE <br>EMPLOYEES CREDIT COOPERATIVE</span></a>
            <ul class="nav navbar-nav visible-xs-block">
                <li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-tree5"></i></a></li>
            </ul>
        </div>
        <div class="navbar-collapse collapse" id="navbar-mobile">
            <?php require('includes/sidebar.php'); ?>
        </div>
    </div>
    <!-- /main navbar -->

    <!-- Page container -->
    <div class="page-container">

        <!-- Page content -->
        <div class="page-content">

            <!-- Main content -->
            <div class="content-wrapper">

                <!-- Page header -->
                <div class="page-header page-header-default">
                    <div class="page-header-content">
                        <div class="page-title">
                            <h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard</span> - Member</h4>
                        </div>
                    </div>

                    <div class="breadcrumb-line">
                        <ul class="breadcrumb">
                            <li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
                            <li class="active"><i class="icon-users position-left"></i>Member</li>
                        </ul>
                        <ul class="breadcrumb-elements">
                            <li><a href="javascript:;" data-toggle="modal" data-target="#modal_new"><i class="icon-add position-left text-teal-400"></i> New Member</a></li>
                        </ul>
                    </div>
                </div>






                <div class="content">

                    <div class="panel  panel-white border-top-xlg border-top-teal-400">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-list text-teal-400 position-left"></i> Member List</h6>
                        </div>
                        <div class="panel-body">
                            <table class="table datatable-button-html5-basic table-hover table-bordered" width="100%">
                                <thead>
                                    <tr style="border-bottom: 4px solid #ddd;background: #eee;">
                                        <th hidden>Memeber ID</th>
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th>Contact</th>
                                        <th>Member Type</th>
                                        <!-- <th></th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()) { ?>
                                        <tr style="cursor:pointer;"
                                            onclick="view_details(this)"
                                            title="View <?= htmlspecialchars($row['name']); ?>,Transaction History">

                                            <td hidden>34236<?= $row['cust_id']; ?></td>
                                            <td> <i class="icon-user icon-2x text-indigo-400"></i><?= $row['name']; ?></td>
                                            <td><?= $row['address']; ?></td>
                                            <td><?= $row['contact']; ?></td>


                                            <td align="center">
                                                <span style="" class="label <?= $row['member_type'] === 'regular'
                                                                                ? 'label-success'
                                                                                : 'label-info'; ?>">
                                                    <?= ucfirst($row['member_type']); ?>
                                                </span>
                                            </td>

                                            <!-- ACTION BUTTON -->
                                            <!-- <td onclick="event.stopPropagation(); edit_details(this);"
                                                cust_id="<?= $row['cust_id']; ?>"
                                                name="<?= $row['name']; ?>"
                                                address="<?= $row['address']; ?>"
                                                contact="<?= $row['contact']; ?>"
                                                member_type="<?= $row['member_type']; ?>"
                                                title="Edit"
                                                style="width:40px;text-align:center;">
                                                <button type="button"
                                                    class="btn border-teal text-teal-400 btn-flat btn-icon btn-xs">
                                                    <i class="icon-pencil7"></i>
                                                </button>
                                            </td> -->

                                        </tr>
                                    <?php } ?>
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>
                <!-- /content area -->
                <?php require('includes/footer-text.php'); ?>

            </div>
            <!-- /main content -->

        </div>
        <!-- /page content -->

    </div>
    <!-- /page container -->
</body>
<?php require('includes/footer.php'); ?>

<div id="modal_new" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form action="#" id="form-customer" class="form-horizontal" role="form"> <input type="hidden" name="save-customer"> <!-- HEADER -->
                <div class="modal-header bg-teal"> <button type="button" class="close text-white" data-dismiss="modal"> &times; </button>
                    <h5 class="modal-title"> <i class="icon-user-plus"></i> Register New Member </h5>
                </div> <!-- BODY -->
                <div class="modal-body"> <!-- NAME -->
                    <div class="row">
                        <div class="col-md-6"> <label>First Name</label> <input class="form-control input-lg" name="first_name" required> </div>
                        <div class="col-md-6"> <label>Last Name</label> <input class="form-control input-lg" name="last_name" required> </div>
                    </div>
                    <hr> <!-- GENDER + CONTACT -->
                    <div class="row">
                        <div class="col-md-6"> <label>Gender</label> <select name="gender" class="form-control input-lg" required>
                                <option value="">-- Select --</option>
                                <option>Male</option>
                                <option>Female</option>
                                <option>Other</option>
                            </select> </div>
                        <div class="col-md-6"> <label>Contact Number</label> <input class="form-control input-lg" name="contact"> </div>
                    </div>
                    <hr> <!-- EMAIL + MEMBER TYPE -->
                    <div class="row">
                        <div class="col-md-6"> <label>Email</label> <input type="email" class="form-control input-lg" name="email" required> </div>
                        <div class="col-md-6"> <label>Member Type</label> <select name="member_type" id="member_type" class="form-control input-lg" required>
                                <option value="">-- Select Member Type --</option>
                                <option value="regular">Regular</option>
                                <option value="associate">Associate</option>
                            </select> </div>
                    </div>
                    <hr> <!-- ADDRESS -->
                    <div class="form-group"> <label>Complete Address</label> <textarea name="address" rows="3" class="form-control"> </textarea> </div>
                    <hr> <!-- CAPITAL SHARE -->
                    <div class="form-group" id="capitalShareGroup"> <label> Capital Share Contribution </label>
                        <div class="input-group"> <span class="input-group-addon"> ₱ </span> <input type="number" name="capital_share" class="form-control input-lg" min="0" step="0.01" value="0"> </div>
                    </div>
                </div> <!-- FOOTER -->
                <div class="modal-footer"> <button type="button" class="btn btn-default" data-dismiss="modal"> Cancel </button> <button type="submit" id="btnSaveMember" class="btn bg-teal-600 btn-lg"> <span id="btnText"> <i class="icon-check"></i> Save Member </span> <span id="btnLoader" style="display:none;"> <i class="icon-spinner spinner"></i> Saving... </span> </button> </div>
            </form>
        </div>
    </div>
</div>



<div id="modal_edit" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="#" id="form-customer-edit" class="form-horizontal" data-toggle="validator" role="form">
                <input type="hidden" name="update-customer">
                <input type="hidden" id="cust_id" name="cust_id">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h5 class="modal-title">Edit Member Form</h5>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Name</label>
                        <div class="col-sm-9">
                            <div class="input-group input-group-xlg">
                                <span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
                                <input class="form-control" id="name" name="name" placeholder="Name" type="text" required data-error="Name is required.">
                            </div>
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Address</label>
                        <div class="col-sm-9">
                            <textarea name="address" id="address" rows="5" cols="5" class="form-control" placeholder="Address"></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Contact</label>
                        <div class="col-sm-9">
                            <div class="input-group input-group-xlg">
                                <span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
                                <input class="form-control" id="contact" name="contact" placeholder="Contact" type="text" data-error="Contact is required.">
                            </div>
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn bg-teal-400 btn-labeled">
                        <b><i class="icon-pencil"></i></b> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- /modal add -->
<script type="text/javascript" src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
<script src="../js/validator.min.js"></script>
<script type="text/javascript">
    document.getElementById('member_type').addEventListener('change', function() {
        const capitalGroup = document.getElementById('capitalShareGroup');
        const capitalInput = capitalGroup.querySelector('input');

        if (this.value === 'associate') {
            capitalGroup.style.display = 'none';
            capitalInput.value = 0;
        } else {
            capitalGroup.style.display = 'block';
        }
    });

    $(function() {

        // Table setup
        $.extend($.fn.dataTable.defaults, {
            autoWidth: false,
            dom: '<"datatable-header"fBl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
            language: {
                search: '<span>Filter:</span> _INPUT_',
                searchPlaceholder: 'Type to filter...',
                lengthMenu: '<span>Show:</span> _MENU_',
                paginate: {
                    'first': 'First',
                    'last': 'Last',
                    'next': '&rarr;',
                    'previous': '&larr;'
                }
            }
        });

        // Basic initialization
        $('.datatable-button-html5-basic').DataTable({
            "order": [
                [0, "desc"]
            ],
            buttons: {
                dom: {
                    button: {
                        className: 'btn btn-default'
                    }
                },
                buttons: [] // No copy/csv/pdf buttons
            }
        });
    });

    function view_details(el) {
        var cust_id = $(el).find('td:first').text().replace('34236', '');
        window.location = 'customer_history.php?cust_id=' + cust_id;
    }

    function edit_details(el) {
        $("#modal_edit").modal('show');
        $("#cust_id").val($(el).attr('cust_id'));
        $("#name").val($(el).attr('name'));
        $("#address").val($(el).attr('address'));
        $("#contact").val($(el).attr('contact'));
    }

    $('#form-customer').validator().on('submit', function(e) {
        if (!e.isDefaultPrevented()) {

            $(':input[type="submit"]').prop('disabled', true);


            $.jGrowl('Processing registration. The member account is being created and login credentials will be sent to the registered email address..', {
                header: 'Please Wait',
                theme: 'alert-styled-right bg-info',
                life: 5000
            });

            var data = $(this).serialize();

            $.ajax({
                type: 'POST',
                url: '../transaction.php',
                data: data,
                success: function(msg) {
                    console.log(msg);

                    if (msg == '1') {


                        setTimeout(function() {
                            $.jGrowl('New member successfully added.', {
                                header: 'Success Notification',
                                theme: 'alert-styled-right bg-success'
                            });
                            setTimeout(function() {
                                window.location = 'customer.php';
                            }, 1500);
                        }, 2000);
                    } else if (msg == 'duplicate') {
                        $.jGrowl('Member already exists. Please use a unique name or contact.', {
                            header: 'Duplicate Entry',
                            theme: 'alert-styled-right bg-warning'
                        });
                        $(':input[type="submit"]').prop('disabled', false);
                    } else {
                        $.jGrowl('Something went wrong while saving member.', {
                            header: 'Error',
                            theme: 'alert-styled-right bg-danger'
                        });
                        $(':input[type="submit"]').prop('disabled', false);
                    }
                },
                error: function() {
                    $.jGrowl('Server error occurred.', {
                        header: 'Error',
                        theme: 'alert-styled-right bg-danger'
                    });
                    $(':input[type="submit"]').prop('disabled', false);
                }
            });
            return false;
        }
    });


    $('#form-customer-edit').validator().on('submit', function(e) {
        if (!e.isDefaultPrevented()) {
            $(':input[type="submit"]').prop('disabled', true);
            var data = $(this).serialize();
            $.ajax({
                type: 'POST',
                url: '../transaction.php',
                data: data,
                success: function(msg) {
                    if (msg == '1') {
                        $.jGrowl('Customer successfully updated.', {
                            header: 'Success Notification',
                            theme: 'alert-styled-right bg-success'
                        });
                        setTimeout(function() {
                            window.location = 'customer.php';
                        }, 1500);
                    } else {
                        alert('Something went wrong!');
                    }
                },
                error: function(msg) {
                    alert('Something went wrong!');
                }
            });
            return false;
        }
    });
</script>

</html>