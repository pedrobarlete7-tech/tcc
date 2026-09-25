<?php
// Canonical Home: avoid maintaining two different implementations.
header('Location: ../index.php', true, 302);
exit;