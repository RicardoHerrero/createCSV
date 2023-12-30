<?php
function uploadArquivo($arquivo){

    include "config.php";

    try {

        $pdo = new PDO("mysql:host={$confDB['host']};dbname={$confDB['bancoDeDados']};charset=utf8", $confDB['usuario'], $confDB['senha']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
        $pdo->beginTransaction();

        $arquivoNome = salvarArquivo($arquivo);

        //INSERT NO BANCO
        $sql = "INSERT INTO Lotes (
                        dataregistro,
                        arquivo,
                        totalbeneficiarios,
                        status
                    ) VALUES (
                        NOW(),
                        :nome, 
                        0,
                        1
                    )";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $arquivoNome, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $loteID = $pdo->lastInsertId();
        }
        $pdo->commit();
        return true;

    } catch (LogicException $e) {
        $pdo->rollback();
        echo "Erro: " . $e->getMessage();
        return false;

    } catch (PDOException $e) {
        $pdo->rollback();
        echo "Erro de conexão com o banco de dados: " . $e->getMessage();
        return false;
    }
}


function salvarArquivo($arquivo){    
    if ($arquivo['error'] === 0) {
        $diretorioDestino = 'arquivosRecebidos/';

        $nomeOriginal = pathinfo($arquivo['name'], PATHINFO_FILENAME);
        $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
        $caminhoCompleto = $diretorioDestino . $nomeOriginal . '.' . $extensao;
        $contador = 1;
        while (file_exists($caminhoCompleto)) {
            $nomeOriginal2 = $nomeOriginal . '_' . $contador;
            $caminhoCompleto = $diretorioDestino . $nomeOriginal2 . '.' . $extensao;
            $contador++;
        }
        move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto);
        return $nomeOriginal2. '.' . $extensao;
    } else {
        throw new LogicException('Erro durante o upload do arquivo. Código de erro: ' . $arquivo['error'], 2);
    }
}
