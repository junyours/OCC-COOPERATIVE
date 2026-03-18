<?php
ini_set('max_execution_time', 0);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
// print_r($_SESSION);
// echo '</pre>';


require('../db_connect.php');


if (!isset($_SESSION['is_login_yes']) || $_SESSION['is_login_yes'] != 'yes') {

	header("Location: ../index.php");
	exit();
}




?>


<!DOCTYPE html>
<html lang="en">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="manifest" href="/manifest.json">
	<meta name="theme-color" content="#0052a4">
	<link rel="apple-touch-icon" href="/assets/icons/your_logo.png">
	
	<!-- Favicon for browser tab -->
	<link rel="icon" type="image/x-icon" href="../images/main_logo.jpg">
	<link rel="shortcut icon" href="../images/main_logo.jpg">
	<link rel="icon" type="image/png" sizes="32x32" href="../images/main_logo.jpg">
	<link rel="icon" type="image/png" sizes="16x16" href="../images/main_logo.jpg">
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>  
	
	<title>OCCECC</title>

	<!-- Global stylesheets -->
	<!-- <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css"> -->
	<link href="../assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
	<link href="../assets/css/bootstrap.css" rel="stylesheet" type="text/css">
	<link href="../assets/css/core.css" rel="stylesheet" type="text/css">
	<link href="../assets/css/components.css" rel="stylesheet" type="text/css">
	<link href="../assets/css/colors.css" rel="stylesheet" type="text/css">
	<!-- /global stylesheets -->


</head>

<!-- /theme JS files -->
<style type="text/css">
	.content {
		/*height: 570px!important;*/
	}

	body::-webkit-scrollbar-track {

		background-color: #28343a;
	}

	body::-webkit-scrollbar {
		width: 10px;
		background-color: #F5F5F5;
	}

	body::-webkit-scrollbar-thumb {

		-webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, .3);
		background-color: #0052a4;;
		height: 20px;
	}

	#spinner_div {
		position: fixed;
		left: 0px;
		top: 0px;
		width: 100%;
		height: 100%;
		z-index: 9999;
		background: url(../images/LoaderIcon.gif) 50% 50% no-repeat #fff;
		/*  background-size: 70px;*/
	}

	.modal-open {
		padding-right: 0px !important;
	}

	.modal-body {
		height: 650px;
		overflow-y: auto;
	}

	.modal-body::-webkit-scrollbar-track {
		background-color: #dddd;
	}

	.modal-body::-webkit-scrollbar {
		width: 8px;
	}

	.modal-body::-webkit-scrollbar-thumb {
		background-color: #0052a4;
	}

	.modal-footer {
		padding-top: 20px;
		border-top: 1px solid #eee;
	}

	.modal-bodys {
		padding: 20px 20px 20px 20px;
	}

	.navbar-brand {
		padding: 0px 20px !important;
		font-size: 14px !important;
	}

	.right {
		text-align: right;
	}

	.left {
		text-align: left;
	}

	.center {
		text-align: center;
	}

	.tr-table {
		border-bottom: 4px solid #ddd;
		background: #eee
	}

	.entry-page {
		width: 100%;
		text-align: right;
		padding: 10px 22px 0px 0px;
	}

	.entry-page .dropdown-menu {
		min-width: 105px;
	}

	.no-found {
		padding-top: 100px;
		width: 100%;
		text-align: center;
		color: #fff
	}

	.no-found h3 {
		font-size: 14px !important;
	}

	.menu-img {
		height: 150px;
		width: 100%;
		padding-top: 75%;
		background-size: cover !important;
		;
		background-position: center !important;
		;
		background-repeat: no-repeat !important;
		;
		cursor: pointer;
	}

	.menu-content {
		/* padding: 2px !important; */
	}

	.menu-content {
		padding: 10px;
	}

	.menu-content .title {
		font-size: 14;
		font-weight: 600;
	}

	.stock-inventory .form-group {
		display: flex;
		align-items: center;
	}

	.stock-inventory .bootstrap-touchspin {
		width: 120px;
		margin-left: 10px;
	}

	.stock-inventory .touchspin-empty {
		text-align: center;
	}

	.panel-menu-footer {
		display: flex;
	}

	.panel-menu-footer .flex-item {
		width: 50%;
	}

	.panel-menu-footer .flex-item button {
		border-radius: 0px !important;
	}
</style>
<script type="text/javascript">
	function numbersonly(e) {
		var unicode = e.charCode ? e.charCode : e.keyCode
		if (unicode != 8) {
			if (unicode < 48 || unicode > 57)
				return false
		}
	}
</script>
</head>
<!-- <div id="spinner_div"></div> -->