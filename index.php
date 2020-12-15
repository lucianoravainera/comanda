<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once './clases/PHPExcel.php';
require_once './composer/vendor/autoload.php';
require_once './clases/AccesoDatos.php';
require_once './clases/empleadosApi.php';
require_once './clases/loginApi.php';
require_once './clases/itemApi.php';
require_once './clases/MWparaAutentificar.php';
require_once './clases/MWparaCors.php';
require_once './clases/AutentificadorJWT.php';
require_once './clases/pedidoApi.php';
require_once './clases/pedidoItemApi.php';
require_once './clases/mesaApi.php';
require_once './clases/clienteApi.php';
require_once './clases/reporteApi.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$app = new \Slim\App(["settings" => $config]);

$app->post('/ingreso/', \loginApi::class . ':login')->add(\MWparaCORS::class . ':HabilitarCORSTodos'); //OK


$app->group('/empleado', function () {

    $this->get('/', \empleadosApi::class . ':traerTodos'); //OK
    //$this->get('/empleadosLogins', \empleadosApi::class . ':traerFechasLogins');
    //$this->get('/cantidadOperaciones', \empleadosApi::class . ':traerCantidadOperaciones'); //no funciona llama a estacionamiento
    $this->get('/{id}', \empleadosApi::class . ':traerUno');//OK

    $this->post('/', \empleadosApi::class . ':CargarUno'); //OK

    $this->put('/', \empleadosApi::class . ':ModificarUno');//OK no valida
    $this->put('/uploadFoto', \empleadosApi::class . ':ModificarFoto'); //REVISAR

    $this->delete('/', \empleadosApi::class . ':BorrarUno');//OK

    $this->get('/pedidos/', \pedidoApi::class . ':traerTodos');//OK trae todo el listado

})->add(\MWparaAutentificar::class . ':VerificarAdmin');



$app->group('/pedido', function () {
  
   $this->get('/', \pedidoApi::class . ':traerTodos');

   $this->get('/clientes', \pedidoApi::class . ':traerClientes');

   //$this->get('/{desde}/{hasta}', \pedidoApi::class . ':traerTodos');

    $this->delete('/', \pedidoApi::class . ':BorrarUno');

    $this->put('/', \pedidoApi::class . ':ModificarUno');

   $this->post('/', \pedidoApi::class . ':CargarUno');

    $this->post('/mesa', \pedidoApi::class . ':AsignarMesa');

})->add(\MWparaAutentificar::class . ':VerificarMozo');



$app->group('/item', function () {
  
    $this->get('/', \itemApi::class . ':traerTodos'); //OK

    $this->post('/', \itemApi::class . ':CargarUno'); //OK

    $this->post('/update', \itemApi::class . ':ModificarUno');//OK
 
     $this->delete('/{id}', \itemApi::class . ':BorrarUno');//OK
     
 })->add(\MWparaAutentificar::class . ':VerificarMozo');


 $app->group('/pedidoitem', function () { //REVISAR
  
    $this->get('/', \pedidoItemApi::class . ':traerMisPedidos');

    // $this->post('/', \itemApi::class . ':CargarUno');

    $this->put('/', \pedidoItemApi::class . ':tomarUnPedido');

    $this->put('/terminar', \pedidoItemApi::class . ':terminarUnPedido');
 
    // $this->delete('/', \itemApi::class . ':BorrarUno');
     
 })->add(\MWparaAutentificar::class . ':VerificarEmpleado');


 $app->group('/mesa', function () { //aca me fijo con codigo de mesa como esta el pedido
  
    $this->get('/', \mesaApi::class . ':traerTodas');// OK

    //$this->put('/', \mesaApi::class . ':cambiarEstadoMozo'); //no se usa

    $this->put('/update', \mesaApi::class . ':actualizarEstadoMesa');

    $this->put('/cerrar', \mesaApi::class . ':cerrarMesa');
 });



 $app->group('/cliente', function () {
  
    $this->get('/pedido', \clienteApi::class . ':traerEstadoPedido');

    // $this->get('/pedidosmesa', \clienteApi::class . ':traerPedidosMesa');

    $this->post('/encuesta', \clienteApi::class . ':completarEncuesta');
     
    $this->post('/alta', \clienteApi::class . ':CargarUno');//OK

    //$this->post('/pedido', \pedidoApi::class . ':CargarUno');
    
    $this->get('/item', \itemApi::class . ':traerTodos');

    $this->get('/pedidos', \pedidoApi::class . ':traerPedidosCliente');

 })->add(\MWparaAutentificar::class . ':VerificarCliente');

$app->group('/alta', function () {

   $this->post('/cliente', \clienteApi::class . ':CargarUno');//OK 

})->add(\MWparaAutentificar::class . ':VerificarEmpleado');//no se si esta bien o dejarlo libre al usuario



 $app->group('/reporte', function () {
  
    $this->get('/empleado/ingresos', \reporteApi::class . ':traerDiasHorarios');
    
    $this->get('/empleado/operacionessector', \reporteApi::class . ':traerOperacionesPorSector');

    $this->get('/empleado/operacionessectorempleados', \reporteApi::class . ':traerOperacionesPorSectorListadoEmpleados');

    $this->get('/empleado/operacionesempleado', \reporteApi::class . ':traerOperacionesPorEmpleado');

    $this->get('/pedido/masvendido', \reporteApi::class . ':traerMasVendido');

    $this->get('/pedido/menosvendido', \reporteApi::class . ':traerMenosVendido');

    $this->get('/pedido/fueradetiempo', \reporteApi::class . ':traerPedidosRetrasados');
    // $this->post('/encuesta', \reporteApi::class . ':completarEncuesta');
     
 })->add(\MWparaAutentificar::class . ':VerificarAdmin');

 $app->group('/encuestas', function () {
 
   $this->get('/', \reporteApi::class . ':traerEncuestas');

});

  $app->run();
