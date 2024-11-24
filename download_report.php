<?php
include('config.php');
include('function.php');
checkUser();

// Initialize filters from URL parameters or form submission
$category_filter = isset($_POST['category_id']) ? $_POST['category_id'] : '';
$start_date = isset($_GET['from']) ? $_GET['from'] : (isset($_POST['start_date']) ? $_POST['start_date'] : '');
$end_date = isset($_GET['to']) ? $_GET['to'] : (isset($_POST['end_date']) ? $_POST['end_date'] : '');

// Fetch categories for the filter dropdown
$category_query = "SELECT id, name FROM category";
$category_res = mysqli_query($con, $category_query);

// Base query for report with filters
$query = "SELECT SUM(expense.price) AS price, category.name 
          FROM expense 
          JOIN category ON expense.category_id = category.id 
          WHERE expense.added_by = '" . $_SESSION['UID'] . "'";

// Apply category filter if selected
if ($category_filter) {
    $query .= " AND expense.category_id = $category_filter";
}

// Apply date range filter if dates are selected
if ($start_date && $end_date) {
    $query .= " AND expense.expense_date BETWEEN '$start_date' AND '$end_date'";
} elseif ($start_date) {
    $query .= " AND expense.expense_date >= '$start_date'";
} elseif ($end_date) {
    $query .= " AND expense.expense_date <= '$end_date'";
}

// Group by category
$query .= " GROUP BY expense.category_id";

$res = mysqli_query($con, $query);

// Check if no data found
if (mysqli_num_rows($res) == 0) {
    die('No data found for the selected filters.');
}

// Set the filename for the CSV
$filename = 'Expense_Report_' . date('Y-m-d_H-i-s') . '.csv'; // Include current date and time in filename

// Set headers for download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open output stream
$output = fopen('php://output', 'w');

// Write column headers
fputcsv($output, array('Category', 'Total Expense', 'Date Generated'));

// Write data rows (excluding IDs), adding the current date and time
while ($row = mysqli_fetch_assoc($res)) {
    // Include the current date and time in each row
    $date_generated = date('Y-m-d H:i:s'); // Format current date and time
    fputcsv($output, array($row['name'], $row['price'], $date_generated));
}

// Close the output stream
fclose($output);
exit();
?>
