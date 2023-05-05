<?php

return array(
    array(
                'label'         => 'Gestione ferie',
                'module'        => 'default',
                'controller'    => 'richieste',
                'action'        => '',
                'visible'       => true,
                'pages'         => array(
                    array(
                                'label'         => 'Richiedi ferie',
                                'module'        => 'default',
                                'controller'    => 'richieste',
                                'action'        => 'nuova'
                        ),
                        array(
                                'label'         => 'Storico',
                                'module'        => 'default',
                                'controller'    => 'richieste',
                                'action'        => 'storico'
                        ),
                        array(
                                'label'         => 'Residui',
                                'module'        => 'default',
                                'controller'    => 'residui',
                                'action'        => 'view',
                                'visible'       => true
                        )
                )
        ), 
    array(
                'label'         => 'Sostituzioni',
                'module'        => 'default',
                'controller'    => 'sostituzioni',
                'action'        => 'elenco',
    ),
    array(
                'label'         => 'Primanota',
                'module'        => 'default',
                'controller'    => 'primanota',
                'action'        => 'index',
                'visible'       => false
    ),
    array(
                'label'      => 'Calendario',
                'module'     => 'default',
                'controller' => 'calendario',
                'action'     => 'mensile',
                'resource'   => 'mvc:admin', // resource
        ),
    array(
                'label'      => 'Dati Personali',
                'module'     => 'default',
                'controller' => '',
                'action'     => '',
                 
                'pages'         => array(
                    array(
                                'label'         => 'Password',
                                'module'        => 'default',
                                'controller'    => 'user',
                                'action'        => 'modifypassword'
                        ),
                        array(
                                'label'         => 'Email',
                                'module'        => 'default',
                                'controller'    => 'user',
                                'action'        => 'modifyemail'
                        )
        )
    )
    
);
