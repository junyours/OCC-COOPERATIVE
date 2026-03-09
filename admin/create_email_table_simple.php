<?php
// Direct database connection
$host = "localhost";
$username = "root";
$password = "";
$database_name = "occ_coop";

try {
    $db = new mysqli($host, $username, $password, $database_name);
    
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }
    
    echo "✅ Connected to database: $database_name<br>";
    
    // Create email_logs table
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
    
    if ($db->query($createTableQuery)) {
        echo "✅ Email logs table created successfully!<br>";
        
        // Create indexes
        $indexes = [
            "CREATE INDEX idx_email_logs_loan_id ON email_logs(loan_id);",
            "CREATE INDEX idx_email_logs_sent_date ON email_logs(sent_date);",
            "CREATE INDEX idx_email_logs_status ON email_logs(status);"
        ];
        
        foreach ($indexes as $index) {
            if ($db->query($index)) {
                echo "✅ Index created: " . substr($index, strpos($index, 'idx_'), 20) . "<br>";
            }
        }
        
        echo "<br>🎉 Email logs table is ready for use!<br>";
        
        // Show table structure
        $result = $db->query("DESCRIBE email_logs");
        if ($result && $result->num_rows > 0) {
            echo "<br><strong>📋 Table Structure:</strong><br>";
            while ($row = $result->fetch_assoc()) {
                echo "• " . $row['Field'] . " - " . $row['Type'] . "<br>";
            }
        }
        
    } else {
        echo "❌ Error creating table: " . $db->error . "<br>";
    }
    
    $db->close();
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
