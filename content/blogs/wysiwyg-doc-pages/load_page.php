<?php
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Content-Type: application/json");

    include "connection.php";

    if(!$conn){
        echo json_encode(["error" => "Connection unsuccessful"]);
        exit;
    }

    // Validate GET parameter
    if(!isset($_GET["pageId"]) || trim($_GET["pageId"])===""){
        echo json_encode(["error" => "Missing pageId"]);
        sqlsrv_close($conn);
        exit;
    }

    $pageId = trim($_GET["pageId"]);

    // Build retrieval query
    $query = "
        SELECT pageID, pageTitle, content
        FROM docPage
        WHERE pageID = ?
    ";
    $params = array($pageId);

    $stmt = sqlsrv_query($conn, $query, $params);

    if($stmt===false){
        echo json_encode(["error" => "Query failed", "details" => sqlsrv_errors()]);
        sqlsrv_close($conn);
        exit;
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    // No result => prompt user
    if (!$row) {
        echo json_encode(["error" => "404: Page not found."]);
        exit;
    }

    // Otherwise, extract the fields...
    sqlsrv_close($conn);
    echo json_encode([
        "success" => true,
        "pageId" => $row["pageID"],
        "title" => $row["pageTitle"],
        "content" => $row["content"]
    ]);
    exit;
?>