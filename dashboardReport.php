<?php
include('header.php');
checkUser();  // Ensure user is logged in
userArea();

// Retrieve `from` and `to` dates from URL parameters, if available
$from = isset($_GET['from']) ? $_GET['from'] : '';
$to = isset($_GET['to']) ? $_GET['to'] : '';
$error_message = "";

// Validate date range if both dates are provided
if ($from && $to && strtotime($to) < strtotime($from)) {
    $error_message = "End date cannot be before the start date.";
}

// Base query for fetching report data
$query = "SELECT category.name AS category, expense.item, expense.expense_date, expense.price 
          FROM expense 
          JOIN category ON expense.category_id = category.id 
          WHERE expense.added_by = '" . $_SESSION['UID'] . "'";

// Apply date range filter if `from` and `to` dates are available
if ($from && $to) {
    $query .= " AND expense.expense_date BETWEEN '$from' AND '$to'";
} elseif ($from) {
    $query .= " AND expense.expense_date >= '$from'";
} elseif ($to) {
    $query .= " AND expense.expense_date <= '$to'";
}

// Order by expense date
$query .= " ORDER BY expense.expense_date ASC";
$res = mysqli_query($con, $query);

// Check if no data is found
$no_data_message = (mysqli_num_rows($res) == 0) ? "No data found for the selected date range." : "";

?>
<script>
    document.title="Deails";
</script>
<br><br><br>
<br><br>

<h2>Detailed Report</h2>
<br><br>
<!-- Display error message if dates are invalid -->
<?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<!-- Display the date range and filter information if dates are provided -->
<?php if ($from || $to): ?>
    <p>Showing report from <strong><?php echo date('d-m-Y', strtotime($from)); ?></strong> to <strong><?php echo date('d-m-Y', strtotime($to)); ?></strong></p>
<?php else: ?>
    <p>Showing <strong>all expenses</strong>.</p>
<?php endif; ?>
<br>

<!-- Display message if no data found -->
<?php if ($no_data_message): ?>
    <div class="alert alert-warning"><?php echo $no_data_message; ?></div>
<?php endif; ?>

<!-- Displaying the report table -->
<?php if (!$error_message && !$no_data_message): ?>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Category</th>
                <th>Item</th>
                <th>Expense Date</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $total_price = 0;
        while ($row = mysqli_fetch_assoc($res)) {
            $total_price += $row['price'];
        ?>
            <tr>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td><?php echo htmlspecialchars($row['item']); ?></td>
                <td><?php echo date('d-m-Y', strtotime($row['expense_date'])); ?></td>
                <td><?php echo number_format($row['price'], 2); ?></td>
            </tr>
        <?php } ?>
            <tr>
                <th colspan="3">Total</th>
                <th><?php echo number_format($total_price, 2); ?></th>
            </tr>
        </tbody>
    </table>
    <a href="send_expense_report.php" class="btn send">Send Report to Email</a>

<?php endif; ?>

<?php
include('footer.php');
?>
