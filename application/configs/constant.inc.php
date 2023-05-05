<?php

define('OFF',0);

define('ON', 1);

define('VERSION','V.1.0');

define('DEBUG_ENABLED', 0);

define('SC', 'SOSTITUZIONE_CANCELLATA');

define('SN', 'SOSTITUZIONE_NUOVA');

# tipologia di assenze
define('MATERNITA',1);
define('FERIE', 2);
define('MALATTIA',3);
define('INFORTUNIO',4);
define('EX-FESTIVITA',5);
define('PERMESSO_MATTINA',6);
define('PERMESSO_SERA',7);
define('SANTO PATRONO',8);
define('PERMESSO_104',9);
define('ALLATTAMENTO',10);
define('TRASFERTA',11);

#----------------------
#   massimo usabile
# --------------------
define('MAX_FERIE', 26);
define('MAX_USER_INSERT_FERIE', 14);

define('MAX_PERMESS0', 0);

define('MAX_EXFEST_FULL', 30.84);
define('MAX_EXFEST_SHORT', 28.44);


define('GG_FERIE_MESE',2.17);
define('ORE_EXFEST_MESE',2.57);

# mi serve come spartiacque per assegnare le ore di permessi:
# se < 15 allora un tot di ore; se >= di 15 un tot di ore maggiori
define('TOTALE_DIPENDENTI',15);

#### EMAIL ####
define('AMMINISTRAZIONE', 'feriemanager@gmail.com');
define('CARLOTTA', 'carlotta.prisma@gmail.com');
define('MAURA', 'maura.prisma@gmail.com');
define('DEVELOPER', 'developmentprisma@gmail.com');
define('EMAIL_DEVELOPER', 'developmentprisma@gmail.com');

define('INFO_SITO_MSG', 'Verificare sempre sul sito http://62.149.161.214/feriemanager/');
define('DO_NOT_REPLY_MSG','NON RISPONDERE A QUESTO INDIRIZZO EMAIL');
 
### STATUS 
define('IN_LAVORAZIONE',0);
define('ACCETTATO',1);
define('RIFIUTATO',3);
define('ANNULLATO',4);