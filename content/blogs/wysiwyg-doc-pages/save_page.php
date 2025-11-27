<?php
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Content-Type: application/json");
    
    include "connection.php";
    if($conn){

        $data = json_decode(file_get_contents('php://input'), true);
        if(isset($data["title"], $data["content"])){
            $pageId = isset($data["pageId"]) && $data["pageId"] !== "" ? trim($data["pageId"]) : null;
            $title = trim($data["title"]);
            $content = trim($data["content"]);

            if($pageId===null){
                $query = "
                    INSERT INTO docPage (pageTitle, content, createdAt, updatedAt)
                    OUTPUT INSERTED.pageID
                    VALUES (?, ?, GETDATE(), GETDATE())
                ";
                $params = array($title, $content);
                $stmt = sqlsrv_query($conn, $query, $params);
                if($stmt===false){
                    echo json_encode(["error" => "Insert failed", "details" => sqlsrv_errors()]);
                    exit;
                }

                sqlsrv_fetch($stmt);
                $newId = sqlsrv_get_field($stmt, 0);
                sqlsrv_close($conn);
                echo json_encode(["success" => true, "pageId" => $newId]);
                exit;
            }
            else{
                $query = "
                    UPDATE docPage
                    SET pageTitle = ?, content = ?, updatedAt = GETDATE()
                    WHERE pageID = ?
                ";
                $params = array($title, $content, $pageId);
                $stmt = sqlsrv_query($conn, $query, $params);
                if($stmt===false){
                    echo json_encode(["error" => "Update failed", "details" => sqlsrv_errors()]);
                    exit;
                }

                sqlsrv_close($conn);
                echo json_encode(["success" => true, "pageId" => $pageId]);
                exit;
            }
            
        }
        else{
            sqlsrv_close($conn);
            echo json_encode(["error" => "Missing required fields"]);
            exit;
        }
    }
    else{
        sqlsrv_close($conn);
        echo "Connection unsuccessful.";
        die(print_r(sqlsrv_errors(), true));
    }
?>