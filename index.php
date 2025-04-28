<?php 

    // Conexiónn a la base de datos
    $host= "localhost";
    $usuario="root";
    $password="";
    $basededatos="barberia";

    $conexion= new mysqli($host,$usuario,$password,$basededatos);

    if($conexion->connect_error){
        die("Conexión no establecida". $conexion->connect_error);
    }
    
    // Devolver en un archivo json
    header("Content-Type: application/json"); 


    // Variable metodo guado el tipo de petición HTTP que se está haciendo: puede ser GET, POST, PUT, DELETE, etc.
    // GET /apibarberia/index.php/ → $metodo = "GET"
    $metodo= $_SERVER['REQUEST_METHOD'];

    // // imprimir el metodo (Get,Put,Post,Delete...) que está llegando
    // print_r($metodo);

    // ruta para obtener la info del id, del dato que buscamos 
    // Verifica si la variable PATH_INFO existe (parte de la URL después del script PHP) y la guarda. Si no hay nada, pone '/'.
    $path= isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:'/';

    // El id que busco está dentro de la url
    $buscarId= explode('/', $path);

    // Si la ruta no es simplemente /, agarra el último segmento del array como el ID que se busca. Si la URL era solo /, no hay ID y se pone null.
    $id=($path!=='/') ? end($buscarId):null;




    // validar el método en una serie de casos, ejemplo switch. Validando las solicitudes
    switch($metodo){

        // SELECT, realiza una consulta, clientes
        case 'GET':
            consulta($conexion, $id);
            break;
        // INSERT, el POST es para insertar datos
        case 'POST':
            insertar($conexion);
            break;
         // UPDATE, para la actualización
         case 'PUT':
            actualizar($conexion, $id);
            break;
         // DELETE, borrar los registros
         case 'DELETE':
            borrar($conexion, $id);
            break;
        default:
            echo "Método no permitido";
            break;
    }


    // GET
    function consulta($conexion, $id){
        $sql= ($id===null) ? "SELECT * FROM cliente": "SELECT * FROM cliente WHERE idCliente=$id";

        // Mostrar la información
        $resultado= $conexion->query($sql);

        if($resultado){
            //El array almacena la info para mostrarla después
           $datos= array();
           while($fila= $resultado->fetch_assoc()){
                //Almacenar los datos cuando vengan de la fila
                $datos[]= $fila;
           }
           //Convertir toda la info de la base de datos en formato json
           echo json_encode($datos);

        }

    }

    //POST
    function insertar($conexion){

        // Decodificar el json, a partir de un envio de datos y
        // obtener toda la info con file_get_contents
        $dato= json_decode(file_get_contents('php://input'),true);
        $nombre = $dato['nombre'];
        $apellidos = $dato['apellidos'];
        $telefono = $dato['telefono'];
        $correo = $dato['correo'];
        // print_r($nombre);

        
        $sql= "INSERT INTO cliente(nombre, apellidos, telefono, correo) VALUES ('$nombre', '$apellidos', '$telefono', '$correo')";
        $resultado= $conexion->query($sql);

        if($resultado){
            $dato['id']= $conexion->insert_id;
            echo json_encode($dato);
        }else{
            echo json_encode(array('error' => 'Error al crear cliente', 'detalle' => $conexion->error));
        }

    }

    //DELETE
    function borrar($conexion, $id){

        echo "El id a borrar es: ". $id; 

        $sql= "DELETE FROM cliente WHERE idCliente = $id";
        $resultado= $conexion->query($sql);

        if($resultado){
            echo json_encode(array('mensaje' => 'Cliente borrado'));
        }else{
            echo json_encode(array('error' => 'Error al borrar cliente', 'detalle' => $conexion->error));
        }

    }

    //UPDATE
    function actualizar($conexion, $id){

        $dato= json_decode(file_get_contents('php://input'),true);
        $nombre = $dato['nombre'];

        echo "El id a editar es: ". $id. " con el dato ". $nombre;

        $sql= "UPDATE cliente SET nombre = '$nombre' WHERE idCliente = $id";
        $resultado= $conexion->query($sql);

        if($resultado){
            echo json_encode(array('mensaje' => 'Cliente actualizado'));
        }else{
            echo json_encode(array('error' => 'Error al actualizar el cliente', 'detalle' => $conexion->error));
        }

    }

?>