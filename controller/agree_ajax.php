<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set a session variable to indicate agreement
    $_SESSION['eroom_agree'] = true;

    // Return a JSON response
    echo json_encode(['success' => true]);
} else {
    // If not a POST request, return an error
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
