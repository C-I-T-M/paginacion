<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
session_start();

if (!isset($_SESSION["usuario"])) {
    header("location:index.php");
    exit;
}
include('../recicles/link.php');

//Recibir variables para numero de filas y para pagina actual (default es 0)
$num_filas = $mysqli->real_escape_string(utf8_decode($_POST['num_filas']));  
$pagina = $mysqli->real_escape_string(utf8_decode($_POST['pagina']));


$consultas = $mysqli->query(" "); //Realizar la consulta de los datos de la tabla a mostrar

$tabla_consultas = array();    //Crear arreglo vacíio en el que se ingresarán los datos de la consulta

//Crear matriz que almacene todos los valores de la tabla (Array bidimencional)
if ($consultas->num_rows > 0) {
    while ($consulta = $consultas->fetch_assoc()) {
        array_push($tabla_consultas, array("columna1" => $consulta['columna1'], "columna2" => $consulta['columna2'], "columna3" => $consulta['columna3'], ... , "culumnaN" => $consulta['culumnaN']));
    }
}

//Crear las sub matrices dependiendo del numero de filas ingresadas por el usuario (Array tridimensional)
$tabla_consultas = array_chunk($tabla_consultas, $num_filas);  //https://www.php.net/manual/es/function.array-chunk.php

//Guardar cantidad de paginas 
$cantidad_pag = count($tabla_consultas) - 1; //*Cuenta los elementos del array principal, en este caso el creado por el chunk y se le resta 1 para que la cuenta inicie en 0 (como funcionan los arreglos)

//*Visualización del array
//!                                                                                                    array([0], [1], ...)                                                                                                     Rojo: Paginas de la tabla 
//?                                           [0] => array([0], [1], ...)                                                                                  [1] => array([0], [1], ...)                                          Azul: Filas de de la tabla por pagina
//* [0] => array(["columna1"], ["columna2"], ... , ["culumnaN"])    [1] => array(["columna1"], ["columna2"], ... , ["culumnaN"])      [0] => array(["columna1"], ["columna2"], ... , ["culumnaN"])    [1] => array(["columna1"], ["columna2"], ... , ["culumnaN"])  Verde: Datos de la tabla por fila

//Crear grupos para la paginacion
$grupos = array();    //Crear el array vacío
for ($i = 0; $i <= $cantidad_pag; $i++) {
    array_push($grupos, $i);        //Agregar en este array los numeros desde 0 hasta la cantidad de paginas que tenemos
}
$grupos = array_chunk($grupos, 5);  //Utilizar array_chunk para separar todos los numeros en grupos de 5 



?>

<div class="form-group row">
        <div class="col-lg-1">
            <label>Número de filas: </label>
            <select class="form-control input-sm" id="num_filas"  onchange="javascript:mostrar_alta(value, 0)" >  <!-- Select para el numero de filas, en este caso limitado a 4 opciones  -->
                <?php
                $filas = array(25, 50, 100, 250);
                foreach($filas as $opt_num_filas){
                    if($opt_num_filas != $num_filas){
                ?>
                    <option value="<?= $opt_num_filas?>"><?= $opt_num_filas?></option>
                <?php
                    }else{
                ?>
                    <option value="<?= $opt_num_filas?>" selected><?= $opt_num_filas?></option>
                <?php
            
                    }
                }
                ?>
            </select>
        </div>
    </div>
    
    <!-- PAGINACION  -->
    <div class="form-group row">
    <div class="col-lg-4">
            <ul class="pagination justify-content-center">
                <!-- OPCIONES PARA REGRESAR AL PRINCIPIO O REGRESAR UN LUGAR -->
                <?php if ($pagina > 0) { ?> <!-- Si nuestra pagina es mayor a 0 (no estamos en la primera pagina) -->
                    <li class="page-item">
                        <a onclick="javascript:mostrar_alta(document.getElementById('num_filas').value, <?= 0 ?>)">    <!--Se manda el valor de pagina 0 cuando se elige la opción de regresar al principio -->
                            << </a>
                    </li>
                    <li class="page-item">
                        <a onclick="javascript:mostrar_alta(document.getElementById('num_filas').value, <?= $pagina - 1 ?>)">  <!--Se manda el valor de la pagina actual menos uno cuando se elige la opción de regresar un lugar -->
                            - </a>
                    </li>
                <?php
                } else { ?> <!-- Si nuestra pagina es 0 (estamos en la primera pagina) -->
                    <!-- Agregar la opcion disabled dentro del class de ambas opciones para que no puedan ser usadas-->
                    <li class="page-item disabled">                
                        <a>
                            << </a>
                    </li>
                    <li class="page-item disabled">
                        <a>
                            - </a>
                    </li>
                    <?php
                }

                //Revisar a qué grupo pertenece la página actual
                foreach ($grupos as $grupo) {       //Revisar los grupos 
                    foreach ($grupo as $num) {      //Revisar los valores de cada grupo
                        if ($pagina == $num) {      //Si la pagina actual es igual a un numero dentro del grupo, entonces encontramos el grupo actual
                            $grupo_actual = $grupo;
                            break;                  //Terminar los bucles foreach
                        }
                    }
                }

                //Mostrar las paginaciones pertenecientes al grupo actual
                $tam_vec_pag = count($grupo_actual);        //Obtener el tamaño del vector del grupo actual
                for ($i = 0; $i < $tam_vec_pag; $i++) {     //Realizar ciclo for para mostrar las opciones de paginas de la paginación
                    if ($pagina == $grupo_actual[$i]) {     //Resaltar la pagina en la que nos encontramos
                    ?>
                        <li class="page-item active">
                            <a onclick="javascript:mostrar_alta(document.getElementById('num_filas').value, <?= $grupo_actual[$i] ?>)"><?= $grupo_actual[$i] + 1 ?></a>
                        </li>
                    <?php
                    } else {
                    ?>
                        <li class="page-item">
                            <a onclick="javascript:mostrar_alta(document.getElementById('num_filas').value, <?= $grupo_actual[$i]  ?>)"><?= $grupo_actual[$i] + 1 ?></a>
                        </li>
                    <?php
                    }
                }

                //OPCIONES PARA AVANZAR A LA ULTIMA PAGINA O AVANZAR UN LUGAR
                if ($pagina < $cantidad_pag) { ?>   <!-- Si nuestra pagina es menor a la cantidad de paginas (no estamos en la ultima pagina) -->
                    <li class="page-item">  
                        <a onclick="javascript:mostrar_alta(document.getElementById('num_filas').value, <?= $pagina + 1 ?>)"> +</a>  <!--Se manda el valor de la pagina actual más uno cuando se elige la opción de avanzar un lugar -->
                    </li>
                    <li class="page-item">
                        <a onclick="javascript:mostrar_alta(document.getElementById('num_filas').value, <?= $cantidad_pag ?>)"> >></a> <!--Se manda el valor del total de paginas (en otras palabras la ultima pagina) cuando se elige la opción de ir al final -->
                    </li>
                <?php
                } else { ?> <!-- Si nuestra pagina es igual a la cantidad de paginas (ultima pagina) -->
                    <!-- Agregar la opcion disabled dentro del class de ambas opciones para que no puedan ser usadas-->
                    <li class="page-item disabled">
                        <a> + </a>
                    </li>
                    <li class="page-item disabled">
                        <a> >> </a>
                    </li>
                <?php
                }
                ?>
            </ul>
        </div>

    </div>

<!-- TABLA -->
<div id="tabla">
    <div class="form-group row">
        <div class="col-lg-12">
            <div style="width: 100%; overflow: auto;">
                <table class="table table-condensed table-hover table-striped" style="font-size:11px" id="pagina" value="<?= $pagina ?>">
                    <thead>
                        <tr>
                            <th>columna1</th>
                            <th>columna2</th>
                            <th>columna3</th>
                            <th>...</th>
                            <th>culumnaN</th>
                            <th style="text-align: center;">Boton_funcion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        //Recordar que la variable $tabla_consultas tiene la consulta que hayamos hecho, sus índices son: $tabla_consultas[PAGINA][FILA][DATO GUARDADO]
                        //el proceso siguiente no es muy diferente a cuando hacemos un while($consulta = $consultas->fetch_assoc()){}

                        if (count($tabla_consultas[$pagina]) == $num_filas) {       //Si el tamaño de las filas es igual al numero de filas deseado por el usuario, iniciar bucle for hasta el numero de filas 
                            for ($i = 0; $i <= $num_filas - 1; $i++) { ?>
                                <tr>
                                    <td> <?= $tabla_consultas[$pagina][$i]['columna1'] ?></td>
                                    <td> <?= $tabla_consultas[$pagina][$i]['columna2'] ?></td>
                                    <td> <?= $tabla_consultas[$pagina][$i]['columna3'] ?></td>
                                    <td> <?= $tabla_consultas[$pagina][$i]['...'] ?></td>
                                    <td> <?= $tabla_consultas[$pagina][$i]['culumnaN'] ?></td>
                                    <td align="center">                                             <!-- En caso de querer mandar datos a una función, recordar que nuestro arreglo es de tres dimenciones-->
                                        <button type=" button" class="btn btn-mini btn-warning " onClick="javascript:funcion(<?= $tabla_consultas[$pagina][$i]['columna1'] ?>, <?= $pagina ?>)">
                                        <span class="glyphicon glyphicon-pencil"></span>
                                        </button>
                                    </td>
                                </tr>
                            <?php 
                            }
                        } else {
                            $tam_array = count($tabla_consultas[$pagina]);      //Si el tamaño de las filas NO es igual al numero de filas deseado por el usuario, iniciar bucle for hasta el tamaño de nuestro arreglo (filas del arreglo)
                            for ($i = 0; $i <= $tam_array - 1; $i++) { ?>
                                <tr>
                                    <td> <?= $tabla_consultas[$pagina][$i]['columna1'] ?></td>
                                    <td> <?= $tabla_consultas[$pagina][$i]['columna2'] ?></td>
                                    <td> <?= $tabla_consultas[$pagina][$i]['columna3'] ?></td>
                                    <td> <?= $tabla_consultas[$pagina][$i]['...'] ?></td>
                                    <td> <?= $tabla_consultas[$pagina][$i]['culumnaN'] ?></td>
                                    <td align="center">                                             <!-- En caso de querer mandar datos a una función, recordar que nuestro arreglo es de tres dimenciones-->
                                        <button type=" button" class="btn btn-mini btn-warning " onClick="javascript:funcion(<?= $tabla_consultas[$pagina][$i]['columna1'] ?>, <?= $pagina ?>)">
                                        <span class="glyphicon glyphicon-pencil"></span>
                                        </button>
                                    </td>
                                </tr>
                        <?php
                            }
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
</div>
