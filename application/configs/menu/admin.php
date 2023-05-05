<?php
//27/04/2021
return  array(

array(
            'privilege' => 'admins',
            'label'      => 'Calendario',
            'module'     => 'default',
            'controller' => 'calendario',
            'action'     => 'mensile',
            'resource'   => 'admin', // resource
            'pages'         => array(
                    
                    array(
                         
                            'label'         => 'Mensile',
                            'module'        => 'default',
                            'controller'    => 'calendario',
                            'action'        => 'mensile',
                            'visible' => false
                    ),
                    array(
                            'label'         => 'Giornaliera',
                            'module'        => 'default',
                            'controller'    => 'calendario',
                            'action'        => 'giornaliera',
                            'visible' => false
                    ),
                    array(
                            'label'         => 'Stampa Pdf',
                            'module'        => 'default',
                            'controller'    => 'calendario',
                            'action'        => 'stampa',
                            'visible' => false
                    )
            )
    ),

array(
            'label'         => 'Richieste',
            'module'        => 'default',
            'controller'    => '',
            'action'        => '',
            'visible'       => true,
            'pages'         => array(
                    array(
                        'label'         => 'Nuova',
                        'module'        => 'default',
                        'controller'    => 'richieste',
                        'action'        => 'add'
                    ),
                    array(
                        'label'         => 'Multipla',
                        'module'        => 'default',
                        'controller'    => 'richieste',
                        'action'        => 'multiple'
                    ),
                    array(
                        'label'         => 'Elenco',
                        'module'        => 'default',
                        'controller'    => 'richieste',
                        'action'        => 'list'
                    ),
                    array(
                        'label'         => 'Storico',
                        'module'        => 'default',
                        'controller'    => 'richieste',
                        'action'        => 'storico-inserimenti'
                    ),
                
                )
            ),
array(
    'label'         => 'Giornaliera',
    'module'        => 'default',
    'controller'    => 'giornaliera',
    'action'        => 'stampe',
    'visible'       => true 
) ,
  array(
            'label'         => 'Utenti',
            'module'        => 'default',
            'controller'    => '',
            'action'        => '',
            'visible'       => true,
            'pages'         => array(
                                    array(
                                               'label'         => 'Nuovo',
                                               'module'        => 'default',
                                               'controller'    => 'user',
                                               'action'        => 'add' 
                                    ),
                                    array(
                                               'label'         => 'Elenco',
                                               'module'        => 'default',
                                               'controller'    => 'user',
                                               'action'        => 'list'                                                     
                                    ),
                                    array(
                                               'label'         => 'Check Ferie',
                                               'module'        => 'default',
                                               'controller'    => 'user',
                                               'action'        => 'check-residui',
                                               'visible'       => true
                                    ),
                array(
                                               'label'         => 'Check  ',
                                               'module'        => 'default',
                                               'controller'    => 'residui',
                                               'action'        => 'check',
                                               'visible'       => true
                                    ),
                                    ),
                                     
      ),
array(
            'label'         => 'Assenze',
            'module'        => 'default',
            'controller'    => 'assenze',
            'action'        => 'list',
            'visible'       => false,
),
array(
            'label'         => 'Sostituzioni',
            'module'        => 'default',
            'controller'    => 'sostituzioni',
            'action'        => 'list',
            'visible'       => true,
             
      ),
array(
    'label'         => 'Giri',
    'module'        => 'default',
    'controller'    => 'giri',
    'action'        => 'index',
    'visible'       => true,
),
  array(
            'label'         => 'Sedi',
            'module'        => 'default',
            'controller'    => '',
            'action'        => '',
            'visible'       => true,
            'pages'         => array(
                                    array(
                                               'label'         => 'Aggiungi',
                                               'module'        => 'default',
                                               'controller'    => 'sedi',
                                               'action'        => 'add'
                                        ),
                                    array(
                                               'label'         => 'Elenco',
                                               'module'        => 'default',
                                               'controller'    => 'sedi',
                                               'action'        => 'list'
                                        )
                                    )
    ),
    array(
            'label'         => 'Festivita',
            'module'        => 'default',
            'controller'    => '',
            'action'        => '',
            'visible'       => true,
            'pages'         => array(
                                    array(
                                               'label'         => 'Aggiungi',
                                               'module'        => 'default',
                                               'controller'    => 'festivita',
                                               'action'        => 'add'
                                        ),
                                    array(
                                               'label'         => 'Elenco',
                                               'module'        => 'default',
                                               'controller'    => 'festivita',
                                               'action'        => 'list'
                                        )
                                    )
    ) ,
array(
            'label'         => 'Hotel',
            'module'        => 'default',
            'controller'    => '',
            'action'        => '',
            'visible'       => false,
            'pages'         => array(
                                    array(
                                               'label'         => 'Elenco',
                                               'module'        => 'default',
                                               'controller'    => 'strutture',
                                               'action'        => 'list'
                                        ),
                                    array(
                                               'label'         => 'Aggiungi',
                                               'module'        => 'default',
                                               'controller'    => 'strutture',
                                               'action'        => 'add'
                                        )
                                    )
    ) ,
array(
            'label'      => 'Ferie',
            'module'     => 'default',
            'controller' => 'ferie',
            'action'     => 'index',
            'resource'   => 'mvc:admin', // resource
            'visible'       => false
    ),


     array(
            'label'      => 'Contratti',
            'module'     => 'default',
            'controller' => '',
            'action'     => '',
            'resource'   => 'mvc:admin', // resource
            'visible'       => true,
            'pages'         => array(
                array(
                    'label'         => 'Nuovo',
                    'module'        => 'default',
                    'controller'    => 'contratti',
                    'action'        => 'new',
                    'visible'       => true
                ),
                array(
                    'label'         => 'Elenco',
                    'module'        => 'default',
                    'controller'    => 'contratti',
                    'action'        => 'list',
                    'visible'       => true
                ),    
            )
    ),

array(
            'label'         => 'Tipologie',
            'module'        => 'default',
            'controller'    => '',
            'action'        => '',
            'visible'       => true,
            'pages'         => array(
                array(
                    'label'         => 'Nuova',
                    'module'        => 'default',
                    'controller'    => 'tipologie',
                    'action'        => 'new',
                    'visible'       => true
                ),
                array(
                    'label'         => 'Elenco',
                    'module'        => 'default',
                    'controller'    => 'tipologie',
                    'action'        => 'list',
                    'visible'       => true
                )
            )
    ) ,

);


