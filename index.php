<?php 

require "src/AFIP.php";

use AFIP\Afip;

$AFIP = new Afip();

try{
    $AFIP->service('wsfe')->login();

    $token = $AFIP->getTokenAuthorization();

    var_dump($AFIP->service('wsfe')->factory()->FECompUltimoAutorizado(
        [
			'PtoVta' 	=> 1,
			'CbteTipo' 	=> 1
        ]
    ));

    var_dump($AFIP->service('wsfe')->factory()->FECompConsultar(
        [
		    'FeCompConsReq' => 
            [
				'PtoVta' 	=> 1,
				'CbteTipo' 	=> 1,
                'CbteNro' 	=> 3
            ]
        ]
    ));
} 
catch(Exception $e){
    var_dump($e->getMessage());
}