<?php
/* Template Name: Service Orders */

session_start(); // Start the session to store the passcode state
$passcode = '7887'; // Set your passcode here
$file_path = get_stylesheet_directory() . '/service-orders.json';
$orders = [];

// Check if the passcode is submitted
if (isset($_POST['passcode'])) {
    if ($_POST['passcode'] === $passcode) {
        $_SESSION['access_granted'] = true; // Set a session variable for access
    } else {
        $error_message = 'Incorrect passcode. Please try again.';
    }
}

// Read data from the JSON file if access is granted
if (isset($_SESSION['access_granted']) && $_SESSION['access_granted'] === true) {
    if (file_exists($file_path)) {
        $orders = json_decode(file_get_contents($file_path), true) ?: [];
    }
}

get_header();
?>

<div class="container">
    <?php if (!isset($_SESSION['access_granted']) || $_SESSION['access_granted'] !== true) : ?>
        <!-- Passcode Form -->
        <h1>Enter Passcode</h1>
        <form method="post" style="margin-top: 20px;">
            <input type="password" name="passcode" placeholder="Enter Passcode" required style="padding: 10px; margin-right: 10px;">
            <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">
                Submit
            </button>
        </form>
        <?php if (isset($error_message)) : ?>
            <p style="color: red; margin-top: 10px;"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <div style="height: 15rem;"></div>
    <?php else : ?>
        <!-- Display Table -->
        <h1>Service Orders</h1>
        <table border="1" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>First Name</th>
                    <th>Phone Number</th>
                    <th>Service</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)) : ?>
                    <?php foreach ($orders as $index => $order) : ?>
                        <tr data-index="<?php echo $index; ?>">
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo esc_html($order['firstName']); ?></td>
                            <td><?php echo esc_html($order['phoneNumber']); ?></td>
                            <td><?php echo esc_html($order['service']); ?></td>
                            <td>
                                <button class="delete-row" data-index="<?php echo $index; ?>" style="padding: 5px 10px; background-color: red; color: white; border: none; cursor: pointer;">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5">No orders available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div style="height: 15rem;"></div>
    <?php endif; ?>
</div>

<script>
    document.querySelectorAll('.delete-row').forEach(button => {
        button.addEventListener('click', function() {
            const index = this.getAttribute('data-index');
            if (confirm('Are you sure you want to delete this order?')) {
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=delete_service_order&index=' + index,
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Order deleted successfully.');
                            location.reload();
                        } else {
                            alert('Failed to delete order.');
                        }
                    })
                    .catch(() => alert('An error occurred.'));
            }
        });
    });
</script>

<?php
get_footer();
?>