<?php
include 'config.php';
include 'funcoes.php';
include "header.php";

try {
    $pdo = new PDO("mysql:host={$confDB['host']};dbname={$confDB['bancoDeDados']};charset=utf8", $confDB['usuario'], $confDB['senha']);
    
    $sql = "SELECT 
            L.loteid, L.dataregistro, L.arquivo, L.totalbeneficiarios, L.status,
            S.descricao as statusText, S.generico as statusColor 
            FROM Lotes L
            INNER JOIN zParametros S ON S.tabela='Lotes' AND S.campo='status' AND S.id=L.status
            ORDER BY 1 DESC";
    $stmt = $pdo->query($sql);
    
    if ($stmt->rowCount() > 0) {
        ?>
        <table class="table table-hover">
        <thead>
            <tr>
            <th scope="col">#</th>
            <th scope="col">Arquivo</th>
            <th scope="col">Data</th>
            <th scope="col">Registros</th>
            <th scope="col">Status</th>
            <th scope="col">Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <th scope="row"><?=$row['loteid']?></th>
                    <td><?=$row['arquivo']?></td>
                    <td><?=$row['dataregistro']?></td>
                    <td class="text-right pr-4"><?=($row['status']!="1")?number_format($row['totalbeneficiarios'],0,",","."):'-'?></td>
                    <td><span class="badge badge-<?=$row['statusColor']?>"><?=$row['statusText']?></span></td>
                    <td>
                        <?php
                        if($row['status']=="1"){?>
                            <a class="btn btn-primary btn-sm" href="processar-arquivo/<?=$row['loteid']?>" role="button"><i class="fa-solid fa-gears"></i> Processar Arquivo</a>
                        <?php
                        }else if($row['status']=="2"){
                            $sucesso = contarResultado($row['loteid'],2);
                            $erro = contarResultado($row['loteid'],1);
                        ?>
                            <a class="btn btn-success btn-sm <?=($sucesso==0)?'disabled':''?>" title="Total de Registros: <?=$sucesso?>" href="download-arquivo/<?=$row['loteid']?>/success" role="button"><i class="fa-solid fa-check"></i> Sucesso</a>
                            <a class="btn btn-danger btn-sm <?=($erro==0)?'disabled':''?>" title="Total de Registros: <?=$erro?>" href="download-arquivo/<?=$row['loteid']?>/error" role="button"><i class="fa-solid fa-triangle-exclamation"></i> Erro</a>
                        <?php
                        }?>
                        
                    </td>
                </tr>
            <?php
            }?>
            
        </tbody>
        </table>
        <?php
    } else {
        echo "Nenhum resultado encontrado.";
    }
} catch (PDOException $e) {
    echo "Erro de conexão com o banco de dados: " . $e->getMessage();
}

$pdo = null;
include "footer.php";
