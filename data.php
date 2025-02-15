<?php
include('config.php');

session_start();

// Ensure the user is logged in
if (!isset($_SESSION['unique_pin'])) {
    header("Location: index.php");
    exit;
}

// Fetch the logged-in user's unique_pin from the session
$unique_pin = $_SESSION['unique_pin'];

// Fetch crops for the logged-in farmer based on their unique_pin
$sqlCrops = "SELECT CropID, CropName, ImageURL 
             FROM CropsData 
             WHERE unique_pin = ?";
$stmt = $conn->prepare($sqlCrops);
$stmt->bind_param("s", $unique_pin);  // Bind the user's unique_pin to the query
$stmt->execute();
$resultCrops = $stmt->get_result();

// Fetch farmer details (name, profile_picture) from the `farmers` table
$sqlFarmer = "SELECT name, profile_picture FROM farmers WHERE unique_pin = ?";
$stmtFarmer = $conn->prepare($sqlFarmer);
$stmtFarmer->bind_param("s", $unique_pin);
$stmtFarmer->execute();
$resultFarmer = $stmtFarmer->get_result();

if ($resultFarmer->num_rows > 0) {
    $farmer = $resultFarmer->fetch_assoc();
    $farmerName = $farmer['name'];
    $profilePicture = $farmer['profile_picture'];
} else {
    // If no farmer is found, redirect to login (this shouldn't happen if the session is valid)
    header("Location: index.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

// Time-based greeting logic
$hour = date('H');  // Get the current hour in 24-hour format
if ($hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour < 18) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    background-color: #f0f2f5;
    color: #333;
    min-height: 100vh;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

.screen {
    display: none;
    min-height: 100vh;
    padding: 20px 0;
}

.screen.active {
    display: block;
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Header Styles */
.header {
    background: #ffffff;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.header-title {
    color: #1a73e8;
    font-weight: 600;
    font-size: clamp(1.5rem, 4vw, 2rem);
}

.back-button {
    position: absolute;
    right: 20px;
    background: #f0f2f5;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.back-button:hover {
    background: #e4e6e9;
    transform: translateX(-3px);
}

/* User Profile Section */
.user-profile {
    background: #ffffff;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 30px;
}

.profile-image-container {
    position: relative;
}

.profile-image {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #1a73e8;
}

.profile-info {
    flex-grow: 1;
}

.greeting {
    font-size: 1.8rem;
    color: #1a73e8;
    margin-bottom: 8px;
}

.farmer-name {
    font-size: 2.2rem;
    font-weight: 600;
    color: #333;
}

/* Logout Button */
.logout-btn {
    background-color: #f0f2f5;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.logout-btn:hover {
    background-color: #e0e3e7;
}

.logout-btn i {
    font-size: 18px;
}

/* Crops Section */
.crops-section {
    background: #ffffff;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.section-title {
    font-size: 1.5rem;
    color: #1a73e8;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f2f5;
}

/* Grid and Cards */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
}

.card {
    background: #ffffff;
    border-radius: 15px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
    border: 1px solid #e0e3e7;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.card-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.card-content {
    padding: 20px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.card-title {
    font-size: 1.2rem;
    font-weight: 500;
    color: #1a73e8;
    margin-bottom: 10px;
}

/* Tabs Styling */
.tabs {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
    flex-wrap: wrap;
    background: #ffffff;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.tab {
    padding: 12px 24px;
    background: #f0f2f5;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
    flex: 1;
    min-width: 120px;
    text-align: center;
}

.tab.active {
    background: #1a73e8;
    color: #ffffff;
}

/* Category Section */
.category-section {
    background: #ffffff;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Modal Styling */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    backdrop-filter: blur(5px);
}

.modal-content {
    background-color: #f0f2f5;
    margin: 5vh auto;
    padding: 30px;
    border-radius: 15px;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    animation: modalSlide 0.3s ease-out;
}

@keyframes modalSlide {
    from { transform: translateY(-30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-inner {
    background: #ffffff;
    border-radius: 15px;
    padding: 25px;
    margin-top: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
    background: #ffffff;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modal-header h2 {
    color: #1a73e8;
    font-weight: 600;
    font-size: 1.5rem;
    margin: 0;
}

.close {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #f0f2f5;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 24px;
}

.close:hover {
    background: #e4e6e9;
    transform: rotate(90deg);
}

/* Product Grid */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    padding: 20px;
}

.product-card {
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    background: #ffffff;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.product-image {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
    margin-top: 10px;
}

.product-card h4 {
    margin: 10px 0;
    color: #333;
    font-weight: 500;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 15px;
    }

    .user-profile {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }

    .profile-image {
        width: 100px;
        height: 100px;
    }

    .greeting {
        font-size: 1.5rem;
    }

    .farmer-name {
        font-size: 1.8rem;
    }

    .modal-content {
        padding: 20px;
        margin: 3vh auto;
    }

    .tabs {
        padding: 15px;
    }

    .category-section {
        padding: 20px;
    }
}

@media (max-width: 480px) {
    .tabs {
        flex-direction: column;
    }

    .modal-content {
        width: 95%;
        padding: 15px;
    }

    .modal-inner {
        padding: 15px;
    }

    .header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }

    .back-button {
        position: relative;
        left: 0;
    }

    .logout-btn {
        width: 100%;
        justify-content: center;
    }
}
.profile-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    max-width: 100%;
    margin: 20px auto; /* Center it horizontally */
    text-align: center;
}

    </style>
</head>
<body>
    <div id="cropListScreen" class="screen active">

    <div class="container">
        <div class="header">
            <h1>Crop Management System</h1>
            <!-- Logout Button placed inside the header -->
            <form action="logout.php" method="POST">
                <button type="submit" class="logout-btn" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>  <!-- Logout icon -->
                </button>
            </form>
        </div>

        <div class="profile-container">
        <img src="<?php echo $profilePicture; ?>" alt="Profile Image" class="profile-image"><br>
        <h1><?php echo $greeting; ?>, <?php echo $farmerName; ?>!</h1>
        </div>
            <div class="grid">
                <?php if (mysqli_num_rows($resultCrops) > 0) {
                    while ($crop = mysqli_fetch_assoc($resultCrops)) { ?>
                        <div class="card" onclick="showCropDetails(<?php echo $crop['CropID']; ?>)">
                            <img class="card-image" src="<?php echo htmlspecialchars($crop['ImageURL']); ?>" alt="<?php echo htmlspecialchars($crop['CropName']); ?>">
                            <div class="card-content">
                                <h3 class="card-title"><?php echo htmlspecialchars($crop['CropName']); ?></h3>
                            </div>
                        </div>
                    <?php }
                } ?>
            </div>
        </div>
    </div>

    <div id="cropDetailsScreen" class="screen">
        <div class="container">
            <div class="header">
                <button class="back-button" onclick="showScreen('cropListScreen')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back
                </button>
                <h1>Crop Details</h1>
            </div>
            
            <div id="detailsContent"></div>
        </div>
    </div>

<div id="detailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Item Details</h2>
            <button class="close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-inner">
            <!-- Section for displaying the main item details -->
            <div id="modalContent"></div>
        </div>
    </div>
</div>


    <script>
      function showScreen(screenId) {
    document.querySelectorAll('.screen').forEach(screen => {
        screen.classList.remove('active');
    });
    document.getElementById(screenId).classList.add('active');
}

function showCropDetails(cropID) {
    fetchCropDetails(cropID);
    showScreen('cropDetailsScreen');
}

function fetchCropDetails(cropID) {
    fetch('fetchCropDetails.php?cropID=' + cropID)
    .then(response => response.text())
    .then(data => {
        document.getElementById('detailsContent').innerHTML = data;
        const firstTab = document.querySelector('.tab');
        if (firstTab) {
            firstTab.click();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('detailsContent').innerHTML = 
            '<div class="category-section">Error fetching data. Please try again.</div>';
    });
}

function showCategory(element, category) {
    document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
    element.classList.add('active');
    document.querySelectorAll('.category-section').forEach(section => {
        section.style.display = section.id === category ? 'block' : 'none';
    });
}

function showItemDetails(type, id) {
            const modal = document.getElementById('detailModal');
            const modalContent = document.getElementById('modalContent');
            modal.style.display = 'block';

            // Fetch item details
            fetch('fetchItemDetails.php?type=' + type + '&id=' + id)
            .then(response => response.text())
            .then(data => {
                modalContent.innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                modalContent.innerHTML = '<p>Error fetching item details. Please try again.</p>';
            });
        }


function closeModal() {
    document.getElementById('detailModal').style.display = 'none';
}



window.onclick = function(event) {
    const modal = document.getElementById('detailModal');
    if (event.target == modal) {
        closeModal();
    }
}


function closeModal() {
    document.getElementById('detailModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('detailModal');
    if (event.target == modal) {
        closeModal();
    }
}

    </script>
</body>
</html>
