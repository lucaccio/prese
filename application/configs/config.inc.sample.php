<?php

# sandbox
$g_enable_sandbox = ON;

# abilito l'email
$g_enable_email = ON;

##### EMAIL SOSTITUZIONE
$g_mail_sostituzione_new    = ON;
$g_mail_sostituzione_delete = ON;

##### EMAIL RICHIESTE
$g_mail_richiesta_new     = ON;
$g_mail_richiesta_refused = ON;

##### EMAIL ASSENZE
$g_mail_assenza_annulled = OFF;
$g_mail_assenza_accepted = OFF;
$g_mail_assenza_new      = OFF;
$g_mail_assenza_delete   = OFF;



# invio email al developer
$g_mail_to_developer = ON;


############################
#         LOGGER           #
############################

# abilito / disabilito logger
$g_enable_logger = ON;

$g_enable_logger_on_production_env = OFF;

# tipo di writer
$g_logger_writer = 'firebug';



###### ASSENZE #####

$g_enable_free_substitute_check = ON;

# se ON, permetto di fare, per lo stesso giorno, una richiesta di permesso sera e permesso mattina
$g_enable_request_permesso_mattina_sera_on_same_day = ON;

# se ON, blocca l'assegnazione se non ci sono residui sufficenti
$g_enable_check_residui_for_admin = OFF;

# se ON, blocca l'assegnazione se non ci sono residui sufficenti ( per gli utenti diversi da admin )
$g_enable_check_residui_for_user = OFF;

$g_enable_blocco_giorni = ON;

# abilito al salvataggio nel database (OFF va bene in caso di sviluppo del software)
$g_enable_save_on_table = ON;

# inserimento richiesta ferie
$g_user_block_insert_new_request = OFF;