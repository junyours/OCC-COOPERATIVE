<?php
require_once('../db_connect.php');

// Create email_logs table if it doesn't exist
$createTableQuery = "
CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    member_email VARCHAR(255) NOT NULL,
    email_type VARCHAR(50) NOT NULL,
    sent_date DATETIME NOT NULL,
    status ENUM('sent', 'failed') NOT NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (loan_id) REFERENCES loans(loan_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add index for faster queries
CREATE INDEX idx_email_logs_loan_id ON email_logs(loan_id);
CREATE INDEX idx_email_logs_sent_date ON email_logs(sent_date);
CREATE INDEX idx_email_logs_status ON email_logs(status);
";

try {
    if ($db->multi_query($createTableQuery)) {
        echo "Email logs table created successfully!<br>";
        
        // Clear the buffer
        while ($db->more_results() && $db->next_result()) {
            // Clear each result
        }
        
        echo "Table structure ready for email logging.";
    } else {
        echo "Error creating table: " . $db->error;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
