<?php
// Include the database configuration file for the connection.
include('config.php');

// Check if the URL contains 'type' and 'id' parameters
if (isset($_GET['type']) && isset($_GET['id'])) {
    $type = $_GET['type']; // Fetch the type (pests, diseases, or weeds) from the URL
    $id = intval($_GET['id']); // Fetch the ID from the URL and ensure it's an integer

    // Define SQL queries based on the type (pests, diseases, or weeds)
    $queries = [
        'pests' => [
            // Query to fetch pest main details (name, control measures) and related products
            'main' => "SELECT PestName as name, ControlMeasures as measures, CropID as crop_id FROM Pests WHERE PestID = ?",
            'products' => "SELECT ProductName, ProductURL FROM PestProducts WHERE PestID = ? AND CropID = ?"
        ],
        'diseases' => [
            // Query to fetch disease main details (name, control measures) and related products
            'main' => "SELECT DiseaseName as name, ControlMeasures as measures, CropID as crop_id FROM Diseases WHERE DiseaseID = ?",
            'products' => "SELECT ProductName, ProductURL FROM DiseaseProducts WHERE DiseaseID = ? AND CropID = ?"
        ],
        'weeds' => [
            // Query to fetch weed main details (name, control measures) and related products
            'main' => "SELECT WeedName as name, ControlMeasures as measures, CropID as crop_id FROM Weeds WHERE WeedID = ?",
            'products' => "SELECT ProductName, ProductURL FROM WeedProducts WHERE WeedID = ? AND CropID = ?"
        ]
    ];

    // Check if the provided type (pests, diseases, or weeds) exists in the queries array
    if (isset($queries[$type])) {
        // Prepare and execute the query to fetch the main details (name, control measures, CropID)
        $stmt = mysqli_prepare($conn, $queries[$type]['main']);
        mysqli_stmt_bind_param($stmt, "i", $id); // Bind the ID parameter
        mysqli_stmt_execute($stmt); // Execute the query
        $result = mysqli_stmt_get_result($stmt); // Get the result set
        $item = mysqli_fetch_assoc($result); // Fetch the main item details as an associative array

        // Check if the item exists
        if ($item) {
            // Display the main details
            echo '<div class="main-details">';
            echo '<h3>' . htmlspecialchars($item['name']) . '</h3>'; // Display the name
            // Display the control measures if they exist, otherwise show a message
            if (isset($item['measures']) && !empty($item['measures'])) {
                echo '<h4>Control Measures:</h4>';
                echo '<p>' . nl2br(htmlspecialchars($item['measures'])) . '</p>'; // Display measures with line breaks
            } else {
                echo '<p>No control measures available.</p>'; // Message if no control measures exist
            }
            echo '</div>'; // Close main-details

            // Prepare and execute the query to fetch related products
            $crop_id = $item['crop_id']; // Get the CropID from the item details
            if ($stmt = mysqli_prepare($conn, $queries[$type]['products'])) {
                mysqli_stmt_bind_param($stmt, "ii", $id, $crop_id); // Bind the ID and CropID parameters
                mysqli_stmt_execute($stmt); // Execute the product query
                $products = mysqli_stmt_get_result($stmt); // Get the result set for products

                // Check if there are any related products
                if (mysqli_num_rows($products) > 0) {
                    // Start generating the product details HTML
                    echo '<div class="product-grid">'; // Create a grid layout for products

                    // Loop through each product and display the product details
                    while ($product = mysqli_fetch_assoc($products)) {
                        echo '<div class="product-card">';
                        echo '<h4>' . htmlspecialchars($product['ProductName']) . '</h4>';
                        // Optionally, you can also add a link to the product using ProductURL
                       echo '<img src="' . htmlspecialchars($product['ProductURL']) . '" alt="' . 
                             htmlspecialchars($product['ProductName']) . '" class="product-image">';                        
                    echo '</div>';
                    }
                    echo '</div>'; // Close product-grid
                } else {
                    // Message if no products are found for the item
                    echo '<p>No recommended products found for this crop and item.</p>';
                }

                // Close the prepared statement for products
                mysqli_stmt_close($stmt);
            } else {
                // If there was an issue preparing the product query, show an error message
                echo '<p>Error fetching products. Please try again later.</p>';
            }
        } else {
            // Message if the item with the given ID is not found
            echo '<p>Item not found.</p>';
        }

        // Close the prepared statement for main details
        mysqli_stmt_close($stmt);
    } else {
        // Message if the type specified in the URL is invalid
        echo '<p>Invalid type specified.</p>';
    }

    // Close the database connection
    mysqli_close($conn);
} else {
    // Message if required parameters are missing in the URL
    echo '<p>Required parameters missing. Please provide type and id.</p>';
}
?>
