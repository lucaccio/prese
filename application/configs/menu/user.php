<?php

return array(
                array(
                    
                        'label'         => 'Richiesta ferie',
                        'module'        => 'default',
                        'controller'    => 'richieste',
                        'action'        => 'nuova',
                        'visible'       => true
                ),
                array(
                        'label'      => 'Storico ferie',
                        'module'     => 'default',
                        'controller' => 'richieste',
                        'action'     => 'storico',
                         
                ),
                array(
                         'label'         => 'Residui',
                         'module'        => 'default',
                         'controller'    => 'residui',
                         'action'        => 'view',
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
                                        'label'         => 'Cambia Password',
                                        'module'        => 'default',
                                        'controller'    => 'user',
                                        'action'        => 'modifypassword'
                                ),
                            array(
                                        'label'         => 'Cambia Email',
                                        'module'        => 'default',
                                        'controller'    => 'user',
                                        'action'        => 'modifyemail'
                                )
                 
             
        );