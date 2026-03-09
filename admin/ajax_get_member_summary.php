<?php

require_once('../db_connect.php');

$debug = true;
if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

if (!isset($_GET['member_id']) || empty($_GET['member_id'])) {
    echo '<div style="padding:15px; background:#f8d7da; color:#721c24; border-radius:5px;">No Member ID received.</div>';
    exit;
}

$mid = intval($_GET['member_id']);

try {
 
    $m_query = $db->query("SELECT *, CONCAT(first_name, ' ', last_name) as name FROM tbl_members WHERE member_id = $mid");
    $member = $m_query->fetch_assoc();
    if (!$member) {
        echo '<div style="padding:15px; background:#fff3cd; color:#856404; border-radius:5px;">Member not found.</div>';
        exit;
    }

 
    $savings_res = $db->query("
        SELECT COALESCE(SUM(t.amount), 0) AS balance
        FROM transactions t
        INNER JOIN accounts a ON a.account_id = t.account_id
        INNER JOIN account_types at ON at.account_type_id = a.account_type_id
        WHERE a.member_id = $mid AND at.type_name = 'savings'
        AND t.status = 1 AND t.voided_at IS NULL AND t.reversed_transaction_id IS NULL
    ");
    $savings = $savings_res->fetch_assoc()['balance'] ?? 0;

 
    $capital_res = $db->query("
        SELECT COALESCE(SUM(t.amount), 0) AS balance
        FROM transactions t
        INNER JOIN accounts a ON a.account_id = t.account_id
        INNER JOIN account_types at ON at.account_type_id = a.account_type_id
        WHERE a.member_id = $mid AND at.type_name = 'capital_share'
        AND t.status = 1 AND t.voided_at IS NULL AND t.reversed_transaction_id IS NULL
    ");
    $capital = $capital_res->fetch_assoc()['balance'] ?? 0;

  
    $loan_res = $db->query("
        SELECT COALESCE(SUM(ls.total_due + IFNULL(ls.penalty_due,0)), 0) AS total_loandue
        FROM loan_schedule ls
        INNER JOIN loans l ON ls.loan_id = l.loan_id
        INNER JOIN accounts a ON l.account_id = a.account_id
        WHERE a.member_id = $mid AND ls.status = 'ongoing'
    ");
    $loan_due = $loan_res->fetch_assoc()['total_loandue'] ?? 0;

    echo '
    <div style="font-family: \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; max-width: 650px; margin: 10px auto; background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        
        <div style="padding: 20px; background: #f8f9fa; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="margin: 0; color: #2c3e50; font-size: 1.25rem; letter-spacing: -0.5px;">' . strtoupper($member['name']) . '</h3>
                <span style="font-size: 12px; color: #6c757d; font-weight: 600; text-transform: uppercase; background: #e9ecef; padding: 2px 8px; border-radius: 4px; margin-top: 5px; display: inline-block;">
                    ' . ($member['type'] ?? 'REGULAR MEMBER') . '
                </span>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 11px; color: #adb5bd; display: block;">MEMBER ID</span>
                <span style="font-weight: bold; color: #495057;">#' . str_pad($mid, 5, "0", STR_PAD_LEFT) . '</span>
            </div>
        </div>

        <div style="padding: 20px;">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                
                <div style="padding: 15px; background: #f0fff4; border: 1px solid #c6f6d5; border-radius: 8px;">
                    <span style="display: block; font-size: 12px; color: #2f855a; font-weight: 600; margin-bottom: 5px;">SAVINGS</span>
                    <span style="font-size: 18px; font-weight: 700; color: #22543d;">₱' . number_format($savings, 2) . '</span>
                </div>

                <div style="padding: 15px; background: #ebf8ff; border: 1px solid #bee3f8; border-radius: 8px;">
                    <span style="display: block; font-size: 12px; color: #2b6cb0; font-weight: 600; margin-bottom: 5px;">CAPITAL</span>
                    <span style="font-size: 18px; font-weight: 700; color: #2a4365;">₱' . number_format($capital, 2) . '</span>
                </div>

                <div style="padding: 15px; background: ' . ($loan_due > 0 ? '#fff5f5' : '#f8f9fa') . '; border: 1px solid ' . ($loan_due > 0 ? '#fed7d7' : '#e9ecef') . '; border-radius: 8px;">
                    <span style="display: block; font-size: 12px; color: ' . ($loan_due > 0 ? '#c53030' : '#718096') . '; font-weight: 600; margin-bottom: 5px;">LOAN DUE</span>
                    <span style="font-size: 18px; font-weight: 700; color: ' . ($loan_due > 0 ? '#9b2c2c' : '#2d3748') . ';">₱' . number_format($loan_due, 2) . '</span>
                </div>

            </div>
        </div>

        <div style="padding: 12px 20px; background: #fafafa; border-top: 1px solid #eee; font-size: 12px; color: #999; text-align: center;">
            Member Summary  on ' . date('M d, Y h:i A') . '
        </div>
    </div>
    ';
} catch (Exception $e) {
    echo '<div style="padding:15px; background:#f8d7da; color:#721c24; border-radius:5px;">System Error: ' . $e->getMessage() . '</div>';
}
