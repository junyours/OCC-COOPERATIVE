<?php
require('../db_connect.php');
session_start();

if (!isset($_SESSION['is_login_yes']) || $_SESSION['is_login_yes'] != 'yes') {
    exit(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

header('Content-Type: application/json');

$db->begin_transaction();

try {

    /* =========================================================
       HELPER: GET TRANSACTION TYPE ID
    ==========================================================*/
    function getTransactionTypeId($db, $type_name)
    {
        $stmt = $db->prepare("SELECT transaction_type_id FROM transaction_types WHERE type_name = ?");
        $stmt->bind_param("s", $type_name);
        $stmt->execute();
        $stmt->bind_result($id);
        $stmt->fetch();
        $stmt->close();
        return $id;
    }

    /* =========================================================
       DEPOSIT
    ==========================================================*/
    if ($_POST['action_type'] === 'deposit') {

        $account_id = $_POST['account_id'];
        $amount     = $_POST['amount'];
        $ref_no     = $_POST['ref_no'] ?? '';
        $remarks    = $_POST['remarks'] ?? '';
        $date       = $_POST['date'];

        if ($amount < 250) {
            throw new Exception("Minimum deposit is ₱250.");
        }

        $type_id = getTransactionTypeId($db, 'deposit');

        $stmt = $db->prepare("INSERT INTO transactions 
            (account_id, transaction_type_id, amount, reference_no, remarks, transaction_date, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())");

        $stmt->bind_param("iidsss", $account_id, $type_id, $amount, $ref_no, $remarks, $date);
        $stmt->execute();
        $stmt->close();
    }

    /* =========================================================
       WITHDRAWAL
    ==========================================================*/
    if ($_POST['action_type'] === 'withdrawal') {

        $account_id = $_POST['account_id'];
        $amount     = $_POST['amount'];
        $ref_no     = $_POST['ref_no'] ?? '';
        $remarks    = $_POST['remarks'] ?? '';
        $date       = $_POST['date'];

        // Check Balance
        $balance_query = $db->query("
            SELECT SUM(
                CASE 
                    WHEN tt.type_name IN ('deposit','capital_share','loan_release') THEN t.amount
                    WHEN tt.type_name IN ('withdrawal') THEN -t.amount
                    ELSE 0
                END
            ) AS balance
            FROM transactions t
            JOIN transaction_types tt ON t.transaction_type_id = tt.transaction_type_id
            WHERE t.account_id = $account_id
        ");

        $balance = $balance_query->fetch_assoc()['balance'] ?? 0;

        if ($balance < $amount) {
            throw new Exception("Insufficient balance.");
        }

        $type_id = getTransactionTypeId($db, 'withdrawal');

        $stmt = $db->prepare("INSERT INTO transactions 
            (account_id, transaction_type_id, amount, reference_no, remarks, transaction_date, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())");

        $stmt->bind_param("iidsss", $account_id, $type_id, $amount, $ref_no, $remarks, $date);
        $stmt->execute();
        $stmt->close();
    }

    /* =========================================================
       LOAN PAYMENT
    ==========================================================*/
    if (isset($_POST['save-loan-payments'])) {

        $loan_id   = $_POST['loan_id'];
        $account_id = $_POST['account_id'];
        $amount    = $_POST['amount_paid'];
        $schedule_id = $_POST['schedule_id'] ?? null;
        $ref_no    = $_POST['ref_no'] ?? '';

        // Insert into loan_payments
        $stmt = $db->prepare("INSERT INTO loan_payments
            (loan_id, schedule_id, account_id, amount_paid, principal_paid, interest_paid, penalty_paid, payment_date, reference_no, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, NOW())");

        // Simplified allocation (full to principal for now)
        $principal = $amount;
        $interest  = 0;
        $penalty   = 0;

        $stmt->bind_param("iiidddds", $loan_id, $schedule_id, $account_id, $amount, $principal, $interest, $penalty, $ref_no);
        $stmt->execute();
        $stmt->close();

        // Insert into transactions
        $type_id = getTransactionTypeId($db, 'loan_payment');

        $stmt2 = $db->prepare("INSERT INTO transactions
            (account_id, transaction_type_id, amount, reference_no, transaction_date, created_at)
            VALUES (?, ?, ?, ?, CURDATE(), NOW())");

        $stmt2->bind_param("iids", $account_id, $type_id, $amount, $ref_no);
        $stmt2->execute();
        $stmt2->close();
    }

    /* =========================================================
       LOAN CANCELLATION
    ==========================================================*/
    if ($_POST['action_type'] === 'cancellation') {

        $loan_id = $_POST['loan_id'];

        $db->query("UPDATE loans SET status='cancelled' WHERE loan_id = $loan_id");

        $type_id = getTransactionTypeId($db, 'cancelled loan');

        $db->query("
            INSERT INTO transactions (account_id, transaction_type_id, amount, transaction_date, created_at)
            SELECT account_id, $type_id, 0, CURDATE(), NOW()
            FROM loans WHERE loan_id = $loan_id
        ");
    }

    $db->commit();
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
