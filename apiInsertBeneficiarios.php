<?php

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $loteid = $_POST['loteid'];
    $dados = $_POST['json'];

    if ($dados === null) {
        http_response_code(400);
        echo json_encode(['mensagem' => 'Erro ao decodificar o JSON.']);
        exit;
    }

    /*
    if( $token!=$configAPI['token'] ){
        http_response_code(400);
        echo json_encode(['mensagem' => 'Falha de autenticação API.']);
        exit;
    }*/

    try {

        $pdo = new PDO("mysql:host={$confDB['host']};dbname={$confDB['bancoDeDados']};charset=utf8", $confDB['usuario'], $confDB['senha']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
        $pdo->beginTransaction();

        //DELETE BENEFICIARIOS
        $sql1 = "DELETE FROM Beneficiarios
                    where loteid = :loteid";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->bindParam(':loteid', $loteid);
        $execute = $stmt1->execute();
        if(!$execute) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['result' => 'false', 'message' => 'Falha ao realizar o Insert: ' . $sql1]);
            exit;
        }

        //INSERT BENEFICIARIOS
        $sql2    = "INSERT INTO Beneficiarios 
                    (loteid,status,nome,email,contato,carteirinha,valor,vencimento) 
                    VALUES ";

        $lista  = "";
        foreach ($dados as $item) {
        $lista .= "(
            ".$item['0'].",
            ".$item['1'].",
            '".$item['2']."',
            '".$item['3']."',
            '".$item['4']."',
            '".$item['5']."',
            '".$item['6']."',
            '".$item['7']."'
        ),";
        }
        $totalLista = count($dados);

        $sql2 = $sql2 . $lista.";";
        $sql2 = str_replace(",;", ";", $sql2);
        
        $stmt2 = $pdo->prepare($sql2);
        $execute = $stmt2->execute();
        if(!$execute) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['result' => 'false', 'message' => 'Falha ao executar o SQL: ' . $sql2]);
            exit;
        }
        
        $sql3 = "UPDATE Lotes SET status=2, dataprocessamento=NOW(), totalbeneficiarios=:totalbeneficiarios
                    WHERE loteid = :loteid and status=1";
        $stmt3 = $pdo->prepare($sql3);
        $stmt3->bindParam(':loteid', $loteid);
        $stmt3->bindParam(':totalbeneficiarios', $totalLista);
        $execute = $stmt3->execute();
        if(!$execute) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['result' => 'false', 'message' => 'Falha ao executar o SQL: ' . $sql3]);
            exit;
        }
            
        $pdo->commit();
        echo json_encode([
                'result' => 'success'
            ]);
        exit;        

    } catch (PDOException $e) {
        // Se ocorrer um erro, envie uma resposta de erro
        http_response_code(500);
        echo json_encode(['result' => 'false', 'message' => 'Erro ao conectar ao banco de dados: ' . $e->getMessage()]);
        exit;
    }

}

http_response_code(400);
echo json_encode(['result' => '']);
exit;
