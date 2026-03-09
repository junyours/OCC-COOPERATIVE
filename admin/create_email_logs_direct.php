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
";

// Add indexes
$indexQueries = [
    "CREATE INDEX idx_email_logs_loan_id ON email_logs(loan_id);",
    "CREATE INDEX idx_email_logs_sent_date ON email_logs(sent_date);", 
    "CREATE INDEX idx_email_logs_status ON email_logs(status);"
];

try {
    // Create table
    if ($db->query($createTableQuery)) {
        echo "✅ Email logs table created successfully!<br>";
        
        // Create indexes
        foreach ($indexQueries as $query) {
            if ($db->query($query)) {
                echo "✅ Index created successfully<br>";
            } else {
                echo "⚠️ Index creation warning: " . $db->error . "<br>";
            }
        }
        
        echo "<br>🎉 Table structure ready for email logging!";
        
        // Verify table exists
        $result = $db->query("DESCRIBE email_logs");
        if ($result) {
            echo "<br><br>📋 Table structure:<br>";
            while ($row = $result->fetch_assoc()) {
                echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
            }
        }
        
    } else {
        echo " Error creating table: " . $db->error;
    }
} catch (Exception $e) {
    echo " Error: " . $e->getMessage();
}
?>
