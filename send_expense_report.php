<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
include('config.php');
include('function.php');
checkUser();

// Get user ID and email from session
$uid = $_SESSION['UID'];
$email = $_SESSION['EMAIL']; // Use the email from session

// Check if email exists in the session
if (!$email) {
    die('No email found in session.');
}

// Query to fetch user expenses
$res = mysqli_query($con, "SELECT expense.*, category.name FROM expense, category WHERE expense.category_id = category.id AND expense.added_by = '$uid' ORDER BY expense_date ASC");

// Generate the report content
$html = '<h2>Expense Report</h2>';
$html .= '<table border="1" cellpadding="4" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category</th>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Details</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>';

while ($row = mysqli_fetch_assoc($res)) {
    $html .= '<tr>
                <td>' . $row['id'] . '</td>
                <td>' . $row['name'] . '</td>
                <td>' . $row['item'] . '</td>
                <td>' . $row['price'] . '</td>
                <td>' . $row['details'] . '</td>
                <td>' . $row['added_on'] . '</td>
              </tr>';
}

$html .= '</tbody></table>';

// Check if there is data to send
if (mysqli_num_rows($res) == 0) {
    die('No data found to send.');
}

// PHPMailer setup
$mail = new PHPMailer(true);

try {
    // SMTP settings for SendGrid
    $mail->isSMTP();
    $mail->Host       = 'smtp.sendgrid.net';  // SendGrid SMTP server.
    $mail->SMTPAuth   = true;
    $mail->Username   = 'apikey';  // This is fixed; use 'apikey' for SendGrid.
    $mail->Password   = 'your-sendgrid-api-key';  // Replace with your SendGrid API key.
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // TLS encryption.
    $mail->Port       = 587;  // Port for TLS.

    // Set email content
    $mail->setFrom('your-email@example.com', 'Expense Tracker');  // Set your own email as the sender.
    $mail->addAddress($email);  // Use the user's email address from the session.

    $mail->isHTML(true);
    $mail->Subject = 'Your Expense Report';
    $mail->Body    = $html;

    // Send the email
    $mail->send();
    echo 'Expense report sent successfully!';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>