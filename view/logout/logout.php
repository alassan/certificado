<?php
session_start();
session_destroy();

// Redireciona para a página pública inicial
header("Location: /certificado/index.php");
exit;
