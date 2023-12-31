<!doctype html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" href="https://viva.care/img/favicon.png"/>
    <base href="<?=$config['urlBase']?>">
    <title>Viva.Care - Gerador de CSV</title>
    <link href="https://getbootstrap.com.br/docs/4.1/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="assets/css/example.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">  
    <script src="assets/js/jquery-3.6.0.js"></script>
  </head>

  <body>
    <div class="d-flex flex-column flex-md-row align-items-center p-3 px-md-4 mb-3 bg-white border-bottom shadow-sm">
      <h5 class="my-0 mr-md-auto font-weight-normal"><img class="logo" src="assets/images/logo-tx-preto.png" /></h5>
      <nav class="my-2 my-md-0 mr-md-3">
        <a class="p-2 text-dark" href=""><i class="fa-solid fa-house"></i> Inicio</a>
        <a class="p-2 text-dark" href="enviar-arquivo"><i class="fa-solid fa-cloud-arrow-up"></i> Upload Arquivo</a>
        <a class="p-2 text-dark" href="lista-arquivos"><i class="fa-solid fa-folder-open"></i> Listar Arquivos</a>
      </nav>
    </div>

    <div class="pricing-header px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center">
      <h1 class="display-4">Gerador de <span class="badge badge-primary badge-vivacare">.CSV</span></h1>
      <p class="lead">Gerenciador de arquivos CSV para mensagens - Envio em Lote Forthics.</p>
    </div>

    <div class="container">
        <div class="mb-3">