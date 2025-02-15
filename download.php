<?php
if (isset($_GET['url'])) {
    $url = $_GET['url'];

    // Initialize a cURL session to fetch the PDF
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // Fetch the PDF content
    $pdfData = curl_exec($ch);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    // Check if the response is a PDF
    if (strpos($contentType, 'application/pdf') !== false) {
        // Set headers to initiate a download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="downloaded.pdf"');
        echo $pdfData;
    } else {
        echo 'Failed to retrieve a valid PDF file. Please check the URL.';
    }
} else {
    echo 'No URL specified.';
}
