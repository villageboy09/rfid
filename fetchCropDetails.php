<?php
include('config.php');

if (isset($_GET['cropID'])) {
    $cropID = intval($_GET['cropID']);

    if ($cropID > 0) {
        // Queries for pests, diseases, and weeds
        $queries = [
            'pests' => [
                'title' => 'Pests',
                'sql' => "SELECT p.PestID as id, p.PestName as name, p.ImageUrl as image 
                          FROM Pests p 
                          WHERE p.CropID = ?"
            ],
            'diseases' => [
                'title' => 'Diseases',
                'sql' => "SELECT d.DiseaseID as id, d.DiseaseName as name, d.ImageUrl as image 
                          FROM Diseases d 
                          WHERE d.CropID = ?"
            ],
            'weeds' => [
                'title' => 'Weeds',
                'sql' => "SELECT w.WeedID as id, w.WeedName as name, w.ImageUrl as image 
                          FROM Weeds w 
                          WHERE w.CropID = ?"
            ]
        ];

        // Create tabs
        echo '<div class="tabs">';
        foreach ($queries as $key => $value) {
            echo '<button class="tab" onclick="showCategory(this, \'' . $key . '-section\')">' . $value['title'] . '</button>';
        }
        echo '</div>';

        // Display sections for each category
        foreach ($queries as $key => $query) {
            $stmt = mysqli_prepare($conn, $query['sql']);
            mysqli_stmt_bind_param($stmt, "i", $cropID);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            echo '<div id="' . $key . '-section" class="category-section" style="display: none;">';
            echo '<h3>' . $query['title'] . '</h3>';
            echo '<div class="grid">'; // Wrap all cards in one grid container

            if (mysqli_num_rows($result) > 0) {
                while ($item = mysqli_fetch_assoc($result)) {
                    echo createItemCard($item['name'], $item['image'], $key, $item['id']);
                }
            } else {
                echo '<p>No ' . $query['title'] . ' found for this crop.</p>';
            }

            echo '</div></div>';
        }
    }
}

// Function to create item card for pests, diseases, or weeds
function createItemCard($name, $imageUrl, $type, $id) {
    return '
    <div class="card" onclick="showItemDetails(\'' . $type . '\', ' . $id . ')">
        <img src="' . htmlspecialchars($imageUrl) . '" alt="' . htmlspecialchars($name) . '" class="card-image">
        <div class="card-content">
            <h4 class="card-title">' . htmlspecialchars($name) . '</h4>
        </div>
    </div>'; // Each card is an individual item
}

mysqli_close($conn);
?>
