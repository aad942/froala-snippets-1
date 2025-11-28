<?php
    header("Content-Type: application/json");
    include "connection.php";

    if (!$conn) {
        echo json_encode(["error" => "Connection unsuccessful"]);
        exit;
    }

    $query = "SELECT pageID, pageTitle FROM docPage ORDER BY pageTitle DESC";
    $stmt = sqlsrv_query($conn, $query);

    if ($stmt === false) {
        echo json_encode(["error" => "Query failed", "details" => sqlsrv_errors()]);
        exit;
    }

    $pages = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $pages[] = [
            "pageId" => $row["pageID"],
            "title"  => $row["pageTitle"]
        ];
    }

    sqlsrv_close($conn);

    echo json_encode(["success" => true, "pages" => $pages]);
exit;
?>