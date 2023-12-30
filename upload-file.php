<?php
$erro = "";
include "config.php";
include "funcoes.php";
include "header.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['arquivo'])) {
        $loteID = uploadArquivo($_FILES['arquivo']);
        header("Location: lista-arquivos");
    } else {
        $erro = 'Nenhum arquivo enviado.';
    }
}
if( $erro != "" ){ 
?>
    <div class="alert alert-danger" role="alert">
        <i class="fa-solid fa-bug"></i> <?=$erro?>
    </div>

<?php 
}
?>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="arquivo"><b><i class="fa-solid fa-file-arrow-up"></i> Selecione arquivo TXT CNAB240</b></label>
            <input type="file" class="form-control-file" name="arquivo" id="arquivo" required />
            <br>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Enviar arquivo</button>
        </div>
    </form>

<?php
include "footer.php";