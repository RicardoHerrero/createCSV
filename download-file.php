<?php

include "config.php";

try {

    $pdo = new PDO("mysql:host={$confDB['host']};dbname={$confDB['bancoDeDados']};charset=utf8", $confDB['usuario'], $confDB['senha']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    

    $sql = "SELECT nome,email,contato,carteirinha,valor,vencimento 
            FROM Beneficiarios
            WHERE loteid = ".$_GET['loteid']."
            AND status = ".$_GET['status']."
            ORDER BY nome";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if( $stmt->rowCount() > 0 ){
        $status = ($_GET['status']==2)?"sucesso":"erro";
        $nomeFile = "lote".$_GET['loteid']."-".$status.".csv";
    
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$nomeFile.'"');
    
        $saida = fopen('php://output', 'w');
        fputcsv($saida, array_keys($resultados[0]));
    
        foreach ($resultados as $linha) {
            fputcsv($saida, $linha);
        }
    
        fclose($saida);
    }else{
        echo "Nenhum Registro localizado";
    }

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}