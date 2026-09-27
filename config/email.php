<?php
// NOVO: simula e-mails somente no computador local; não envia mensagens reais.
return [
    'modo' => 'local',
    'pasta' => getenv('ENSINOTEC_MAIL_DIR') ?: dirname(__DIR__, 3) . '/tmp/ensinotec-emails',
];
