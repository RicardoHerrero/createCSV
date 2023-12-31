<?php

include 'config.php';
include "header.php";
$limite = 2;

try {
    $pdo = new PDO("mysql:host={$confDB['host']};dbname={$confDB['bancoDeDados']};charset=utf8", $confDB['usuario'], $confDB['senha']);
    
    $sql = "SELECT * FROM Lotes WHERE loteid = :loteid";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':loteid', $_GET['loteid'], PDO::PARAM_INT);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    if (! $resultado) exit("ERROR... cod. 01 = Lote não localizado!");
            
} catch (PDOException $e) {
    exit("ERROR... cod. 02 = Erro de conexão com o banco de dados: " . $e->getMessage());
}
$pdo = null;

$nomeArquivo = 'arquivosRecebidos/'.$resultado['arquivo'];
if (!file_exists($nomeArquivo)) exit("ERROR... cod. 03 = Arquivo $nomeArquivo não existe");

$linhas = file($nomeArquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($linhas === false) exit("ERROR... Cod. 04 = Não foi possível ler o arquivo $nomeArquivo.");

$numLinhas = count($linhas)-2;

$posicoes = [
    'dataVencimento' => ['start' => 101, 'length' => 6],
    'valor' => ['start' => 110, 'length' => 9],
    'nome' => ['start' => 234, 'length' => 30],
];
$arquivo = [];
foreach ($linhas as $i => $linha) {
    if ($i>0 && $i<=$limite) {
        $registro = [];
        foreach ($posicoes as $campo => $posicao) {
            $registro[$campo] = trim(substr($linha, $posicao['start'], $posicao['length']));
        }
        array_push($arquivo, $registro);
    }
}
$jsonArquivo = json_encode($arquivo);
?>

<div class="alert alert-warning" role="alert">
    <i class="fa-solid fa-skull-crossbones"></i> Atenção, esta ação pode demorar muitos minutos!
</div>

<div class="alert alert-info" role="alert">
  Detalhes do Lote: <b><?=$resultado['loteid']?></b> - <i>em <?=$resultado['dataregistro']?> - Arquivo: <a target="_blank" href="<?=$nomeArquivo?>"><?=$resultado['arquivo']?></a></i>
  <hr/>
  <b>Total de Boletos: <?=number_format($numLinhas,0,"",".")?></b> 
    <span id="messageTime">- O tempo estimado para conclusão eh de x minutos.</span>
  <br><button type="button" id="gerarArquivo" class="btn btn-success mt-2"><i class="fa-solid fa-play"></i> Iniciar agora</button>
</div>

<div class="alert alert-danger text-center" role="alert" id="boxResume" style="display:none">
  <center id="imgLoading"><img src="assets/images/loading.gif" width="100"/></center>
  <span id="messagemAtividade"><i class="fa-solid fa-circle-exclamation"></i> Atenção, está ação pode demorar muitos minutos. Eh preciso paciência.</span>
  <span id="downloadFile"></span>
  <div id="progressBar" class="progress mt-3">
    <div class="progress-bar progress-bar-striped progress-bar-animated" id="loadingBar" style="width:0%" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
  </div>
</div>

<div id="resumoEmissao" style="display: none;">
<table class="table table-hover">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Vencimento</th>
      <th scope="col">Valor</th>
      <th scope="col">Nome</th>
      <th scope="col">Telefone</th>
      <th scope="col">Email</th>
      <th scope="col">Carteirinha</th>
      <th scope="col">Status</th>
    </tr>
  </thead>
  <tbody>
    <?php
    foreach ($linhas as $i => $linha) {
        if ($i>0 && $i<=$limite) {
            echo '<tr>';
            echo '<th scope="row">'.$i.'</th>';
            foreach ($posicoes as $campo => $posicao) {
                echo '<td><span id="indice'.$i.'-'.$campo.'">'.trim(substr($linha, $posicao['start'], $posicao['length'])).'</span></td>';                
            }
            echo '<td><span id="indice'.$i.'-telefone">-</span></td>';
            echo '<td><span id="indice'.$i.'-email">-</span></td>';
            echo '<td><span id="indice'.$i.'-carteirinha">-</span></td>';
            echo '<td><span id="indice'.$i.'-status" class="badge badge-warning">Pendente</span></td>';
            echo '</tr>';
        }
    }
    ?>
  </tbody>
</table>
</div>

<script>
var jsonArquivo = '<?php echo $jsonArquivo; ?>';
const jsonArquivoJS = JSON.parse(jsonArquivo);

/*
jsonArquivoJS.forEach(function(file) {
    console.log('Nome:', file.nome, ', Valor:', file.valor);
});*/

function chamarAPI(indice) {
    const apiUrl = 'apiBlueMed.php';
    var requestOptions = {
        method: 'GET',
    };

    return new Promise((resolve, reject) => {
        fetch(`${apiUrl}?cardOrCpf=42159943819&datavencimento=${indice['dataVencimento']}`, requestOptions)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erro na chamada da API: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            resolve(data);
        })
        .catch(error => {
            reject(error);
        });
    });
}

async function processarIndices(totalIndices) {
  
  var nome = ""
  var telefone = ""
  var email = ""
  var carteirinha = ""
  var loading = 0;
  var valor = ""
  var vencimento = ""
  
  var h=0;

  var sql = "INSERT INTO Beneficiarios (loteid,status,nome,email,contato,carteirinha,valor,vencimento) VALUES ";

  for (const indice of jsonArquivoJS) {
    try {
      h++;
      const resultado = await chamarAPI(indice);
      nome = resultado.data.nome;
      telefone = (resultado.data.telefone == undefined )? '' : resultado.data.telefone ;
      email = (resultado.data.email == undefined )? '' : resultado.data.email ;
      carteirinha = resultado.data.carteirinha;
      status = (telefone!="")? 2 : 1;
      valor = indice['valor']/100;
      vencimento = formatarStringParaData(indice['dataVencimento'])

      sql += `(<?=$_GET['loteid']?>, ${status} , '${nome}', '${email}', '${telefone}', '${carteirinha}', '${valor}', ${vencimento} ),`;

      document.getElementById("indice"+h+"-dataVencimento").textContent = vencimento;
      document.getElementById("indice"+h+"-valor").textContent = valor;
      document.getElementById("indice"+h+"-nome").textContent = nome;
      document.getElementById("indice"+h+"-telefone").textContent = telefone;
      document.getElementById("indice"+h+"-email").textContent = email;
      document.getElementById("indice"+h+"-carteirinha").textContent = carteirinha;
      document.getElementById("indice"+h+"-status").textContent = 'Concluído';
      document.getElementById("indice"+h+"-status").classList.remove("badge-warning");
      document.getElementById("indice"+h+"-status").classList.add("badge-success");

      //Calculando o Loading
      loading = parseInt( h / totalIndices  * 100 )
      var width = loading+"%"
      //console.log(width)
      var barraLoading = document.getElementById("loadingBar")
      barraLoading.style.width = width
      barraLoading.setAttribute("aria-valuenow", loading);

      if( loading == 100 ){
        sql += ";"
        finalizarAtividade(sql)
        break;
      } 

    } catch (erro) {
      // Trata qualquer erro que ocorra durante a chamada da API
      console.error(`Erro para o índice ${indice['nome']}:`, erro.message);
    }
  }
}

function iniciarAtividade(){
    document.getElementById('resumoEmissao').style.display = 'block'; 
    document.getElementById('messageTime').style.display = 'none'; 
    document.getElementById('gerarArquivo').style.display = 'none'; 
    document.getElementById('boxResume').style.display = 'block'; 
    processarIndices(<?=$limite?>);
}

function finalizarAtividade(sql){
    document.getElementById('imgLoading').style.display = 'none'
    document.getElementById('messagemAtividade').innerHTML = '<i class="fa-regular fa-circle-check"></i> Acabouu!.. Vou preparar o arquivo CSV para download.'
    document.getElementById('loadingBar').classList.remove("progress-bar-animated")
    document.getElementById('loadingBar').classList.add("bg-success")
    document.getElementById('boxResume').classList.add("alert-success")
    document.getElementById('boxResume').classList.remove("alert-danger")
    document.getElementById('progressBar').style.display = 'none'

    sql = sql.replace('),;',');')
    console.log(sql)
    //SALVANDO NO BANCO DE DADOS

}

function formatarStringParaData(str) {
    let dia = str.substring(0, 2);
    let mes = str.substring(2, 4);
    let ano = str.substring(4, 6);
    ano = "20" + ano;
    return `${ano}-${mes}-${dia}`;
}

document.getElementById('gerarArquivo').addEventListener('click', iniciarAtividade);
</script>

<?php
include "footer.php";