<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Sistema de Gestão de Cursos da Fundação Wall Ferraz</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap & Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <!-- DataTables -->
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- jQuery e DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <style>
    :root {
      --sidebar-width: 240px;
      --primary-color: #0d6efd;
      --secondary-color: #6c757d;
    }
    
    html, body {
      height: 100%;
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }
    
    body {
      background-color: #f8f9fa;
      display: flex;
      flex-direction: column;
    }
    
    .wrapper {
      display: flex;
      flex-grow: 1;
      position: relative;
    }
    
    .main-content {
      margin-left: var(--sidebar-width);
      padding: 1.5rem;
      flex-grow: 1;
      transition: margin-left 0.3s;
    }
    
    /* Menu Mobile */
    #menuToggle {
      position: fixed;
      top: 15px;
      left: 15px;
      z-index: 1050;
      display: none;
      width: 40px;
      height: 40px;
      padding: 0;
    }
    
    @media (max-width: 768px) {
      .main-content {
        margin-left: 0;
        padding: 1rem;
      }
      #menuToggle {
        display: flex;
        align-items: center;
        justify-content: center;
      }
    }
    
    /* Efeitos globais */
    .transition-all {
      transition: all 0.3s ease;
    }
    
    .hover-scale {
      transition: transform 0.2s;
    }
    .hover-scale:hover {
      transform: scale(1.02);
    }
  </style>
</head>
<body>
<!-- Botão hamburguer para menu mobile -->
<button class="btn btn-outline-secondary d-md-none" id="menuToggle">
  <i class="bi bi-list"></i>
</button>

<div class="wrapper">