<?php
/**
 * Entrada na pasta do projeto (ex.: http://localhost/vincit).
 * Redireciona para a pasta pública da loja (site/).
 */
header('Location: site/', true, 302);
exit;
