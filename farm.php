<?php
// Start the session to access user data
session_start();
require 'config.php'; // Database configuration
require('fpdf/fpdf.php'); // Include FPDF library

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['unique_pin'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get the selected crop ID from the query string
$selected_crop_id = isset($_GET['crop_id']) ? intval($_GET['crop_id']) : 0;

if ($selected_crop_id > 0) {
    // Prepare and execute a query to get details of the selected crop
    $stmt = $conn->prepare("SELECT * FROM crops WHERE id = ?");
    $stmt->bind_param("i", $selected_crop_id);
    $stmt->execute();
    $crop_details_result = $stmt->get_result();
    $crop_details = $crop_details_result->fetch_assoc();

    // Fetch crop-specific images
    $images_stmt = $conn->prepare("SELECT * FROM crop_images WHERE crop_id = ?");
    $images_stmt->bind_param("i", $selected_crop_id);
    $images_stmt->execute();
    $images_result = $images_stmt->get_result();
    $images = $images_result->fetch_all(MYSQLI_ASSOC);
} else {
    header("Location: user_dashboard.php");
    exit;
}

// Handle form submission for generating PDF
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['image_id'])) {
    $image_id = intval($_POST['image_id']);

    // Fetch crop image details
    $stmt = $conn->prepare("SELECT ci.*, dc.chemical_name, dc.application_instructions, c.crop_name
                            FROM crop_images ci
                            JOIN disease_control dc ON ci.disease_control_id = dc.id
                            JOIN crops c ON ci.crop_id = c.id
                            WHERE ci.id = ?");
    $stmt->bind_param("i", $image_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        $crop_name = $data['crop_name'];
        $chemical_name = $data['chemical_name'];
        $instructions = $data['application_instructions'];

        // Create PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('times', 'B', 16);
        $pdf->Cell(40, 10, 'Crop Information');

        $pdf->Ln(10);
        $pdf->SetFont('times', '', 12);
        $pdf->Cell(40, 10, 'Crop Name: ' . $crop_name);

        $pdf->Ln(10);
        $pdf->Cell(40, 10, 'Chemical Name: ' . $chemical_name);

        $pdf->Ln(10);
        $pdf->MultiCell(0, 10, 'Application Instructions: ' . $instructions);

        // Output the PDF
        $pdf->Output('D', 'Crop_Details_' . $crop_name . '.pdf');
        exit; // Ensure no further output after PDF download
    } else {
        echo "No data found for the selected image.";
    }

    // Close the statement
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.16/tailwind.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="font-poppins bg-gray-100"> <!-- Updated font class -->
    <header class="bg-indigo-500 py-4">
        <h1 class="text-white text-center text-2xl font-bold">Crop Advisory</h1>
    </header>
    <main class="container mx-auto my-8">
        <section>
            <?php if (isset($crop_details)): ?>
                <h3 class="text-indigo-500 text-xl font-bold mb-4">Crop Images</h3>
                <?php if (!empty($images)): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <?php foreach ($images as $image): ?>
                            <div class="bg-white p-4 rounded-lg shadow-md">
                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="Crop Image" class="w-full h-48 object-cover rounded-lg mb-4">
                                <form action="" method="post" class="text-center">
                                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                    <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded">Know Prices</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600">No images found for this crop.</p>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-gray-600">No crop details found.</p>
            <?php endif; ?>
        </section>

        <div class="flex justify-between mt-8">
            <a href="user_dashboard.php" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded">Back to Dashboard</a>
            <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">Logout</a>
        </div>
    </main>
    <footer class="bg-indigo-500 py-2 text-white text-center">
        <p>&copy; <?php echo date("Y"); ?> CropSync Private Limited. All rights reserved.</p>
    </footer>
</body>
</html>

<?php
// Close connection after all operations
$conn->close();
?>
