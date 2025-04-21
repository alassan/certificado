<?php
// Exibe mensagem de sucesso
if (isset($_GET['sucesso']) && $_GET['sucesso'] == 1) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            Ação realizada com sucesso!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
          </div>';
}

// Exibe mensagem de erro via GET
if (isset($_GET['erro']) && !empty($_GET['erro'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($_GET['erro']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
          </div>';
}

// Exibe mensagem de erro via sessão
if (isset($_SESSION['mensagem_erro'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($_SESSION['mensagem_erro']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
          </div>';
    unset($_SESSION['mensagem_erro']);
}

// Exibe mensagem de sucesso via sessão
if (isset($_SESSION['mensagem_sucesso'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($_SESSION['mensagem_sucesso']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
          </div>';
    unset($_SESSION['mensagem_sucesso']);
}
?>
