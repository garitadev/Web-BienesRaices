<?php
namespace App;

class Propiedad{

    //Base de datos | protecded porque solamente de la clase se accede a él | static porque no se requiere instanciar cada conexión a la base de datos
    protected static $db;
    //este arreglo permite identificar qué forma van a tener los datos (Siguiendo el principio de Active Record cada atributo tiene el mismo nombre que la columna en la bd)  para mapear el obj 
    protected static $columasDB=['idPropiedades', 'titulo','precio','imagen','descripcion','habitaciones','wc','estacionamiento','creado','idVendedor'];

    //Validación
    protected static $errores = [];


    public $idPropiedades;
    public $titulo;
    public $precio;
    public $imagen;
    public $descripcion;
    public $habitaciones;
    public $wc;
    public $estacionamiento;
    public $creado;
    public $idVendedor;

     //Se define la conexión a la BD
     public static function setDB($database){
        //self hace referencia a los atributos static de una misma clase
        //this a lo que este como public o sea parte del objt
        self::$db = $database;
    }

    public function __construct($args = []){
        $this->idPropiedades = $args['idPropiedades'] ?? '';//en caso de que no esté presente va a ser un string vacío
        $this->titulo = $args['titulo'] ?? '';
        $this->precio = $args['precio'] ?? '';
        $this->imagen = $args['imagen'] ?? '';
        $this->descripcion = $args['descripcion'] ?? '';
        $this->habitaciones = $args['habitaciones'] ?? '';
        $this->wc = $args['wc'] ?? '';
        $this->creado = date('Y/m/d');
        $this->estacionamiento = $args['estacionamiento'] ?? '';
        $this->idVendedor = $args['idVendedor'] ?? 1;
    }

    public function guardar(){
        if(isset($this->idPropiedades)){
            //Actualizar
            $this->actualizar();

        }else{
            //Creando un nuevo registro
            $this->crear();
        }

    }



    public function crear(){
        //Antes se ingresar se debe sanitizar los datos
        $atributos = $this->sanitizarAtributos();
        //debuguear($atributos);
        //array_keys y array_values nos permiten acceder tanto a las llaves y valores de un arreglo
        $query = "INSERT INTO propiedades ( ";
        $query .= join(', ',array_keys($atributos));
        $query .= " ) VALUES (' ";
        $query .= join("', '",array_values($atributos));
        $query .= " ') ";

        //debuguear($query);


        $resultado=self::$db->query($query);
        return $resultado;
        //debuguear($resultado);

    }

    public function actualizar(){
        //Antes se ingresar se debe sanitizar los datos
        $atributos = $this->sanitizarAtributos();
        $valores = [];//va al obj y une atributos con valores

        foreach($atributos as $key => $value){
            $valores[] = "{$key}='{$value}'";

        }
        //debuguear(join(', ',$valores));
        $query = "UPDATE PROPIEDADES SET ";
        $query .= join(', ',$valores);
        $query .= " WHERE IDPROPIEDADES = '" . self::$db->escape_string($this->idPropiedades). "' "; 
        $query .= "LIMIT 1";

        //debuguear($query);
        $resultado = self::$db->query($query);
        if($resultado){
            //Redireccionar al usuario si se realiza el registro
            header('Location: /admin?resultado=2'); /*esto impide que se ingresen datos duplicados | lo que está despues del ? es el mensaje que 
                                                     va a tener la url (_GET['resultado'])*/

             //header ->solo funciona mientras no haya nada de html previo | Usar la redireccion donde realmente sea conveniete ya que usarla en reiteradas oacciones causa problemas
         }
    }

    //Identificar y unir los atributos de la bd
    public function atributos(){
        $atributos = [];
        foreach(self::$columasDB as $columna){
            if($columna === 'idPropiedades') continue; //lo que hace el continue es que cuando se cumpla la condicion lo ignora y pasa al siguiente elemento

            $atributos[$columna] = $this->$columna;
            //se crea un nuevo arreglo con los atributos y datos del obj y se unen | con el this se hace referencia al dato que esté en memoria por ej: $atributos['titulo']=$this->titulo
        }
        return $atributos;

    }


    public function sanitizarAtributos(){//se encarga de sanitizar los datos
       $atributos = $this->atributos();
       $sanitizado = [];

       //Arreglo asociativo donde key son las llaves del arreglo y value el valor ingresado por el usuario
       foreach($atributos as $key => $value){
           $sanitizado[$key] = self::$db->escape_string($value);

       }
       return $sanitizado;
       //debuguear($sanitizado);//para comprobar que se debugueo correctamente se puede poner un ' en el texto
    }

    //Subida de archivos
    public function setImagen($imagen){

        //Elima la imagen previa | Isset revisa si existe y que además qtenga un valor
        if(isset($this->idPropiedades)){
            //Comprobar si existe el archivo
            $existeArchivo = file_exists(CARPETA_IMAGENES . $this->imagen);
            
            if($existeArchivo){
                unlink(CARPETA_IMAGENES . $this->imagen);
            }
            //debuguear($existeArchivo);
        }
        //Asignar al atributo de imagen el nombre de la imagen
        if($imagen){
            $this->imagen=$imagen;
        }

        
    }

    //Validacion
    public static function getErrores(){
        return self::$errores;
    }

    public function validar(){
        //Recordar la importancia del self a la hora de usar static, sin esto nos daría error

        if(!$this->titulo){
            self::$errores[] = "Debes añadir un titulo";
        }
        if(!$this->precio){
            self::$errores[] = "El precio es obligatorio";
        }
        if( strlen($this->descripcion) <60){
            self::$errores[] = "La descripcion es obligatoria y debe tener al menos 60 caracteres";
        }
        if(!$this->habitaciones){
            self::$errores[] = "El número de habitaciones es obligatorio";
        }

        if(!$this->wc){
            self::$errores[] = "El número de Baños es obligatorio";
        }
        if(!$this->estacionamiento){
            self::$errores[] = "El número de espacios de Estacionamiento es obligatorio";
        }
        if(!$this->idVendedor){
            self::$errores[] = "Vendedor no elegido";
        }

        if(!$this->imagen){
            self::$errores[] = "La imagen es obligatoria";   
        }
       

        

        return self::$errores;
    }

    //Listar todos los registros
    public static function all(){
        $query ="SELECT * FROM PROPIEDADES";

        $resultado = self::consultarSQL($query);
       // debuguear($resultado->fetch_assoc());

       return $resultado;
    }

    //Busca un registro por su id | static porque no se requiere una nueva instancia
    public static function find($id){
        //Consulta para traer los datos de la propiedad
        $query = "SELECT * FROM propiedades WHERE idPropiedades= ${id}";
        $resultado= self::consultarSQL($query);
        //debuguear(array_shift($resultado));
        return array_shift($resultado);//array_shift nos devuelve la primera posicion de un arreglo
    }

    public static function consultarSQL($query){
        //Constultar la base de datos
        $resultado = self::$db->query($query);

        //Iterar los resultados
        $array = [];
        while($registro = $resultado ->fetch_assoc()){
            $array[] = self::crearObjeto($registro);//se crea un arreglo de objetos
        }
        //debuguear($array);

        //Liberar la memoria 
        $resultado->free();

        //Retornar los resultados
        return $array;
    }


    //Toma los arreglos que vienen de la base de datos y crea los objs
    protected static function crearObjeto($registro){

        $objeto = new self;

        foreach($registro as $key => $value){//al ser un arreglo asociativo se hace uso de key y value

            //si el obj tiene una llave del arreglo
            if(property_exists($objeto, $key )){
                $objeto -> $key = $value;
            }

        }
        return $objeto;
    }

    //Metodo que sincroniza el obj memoria con los cambios realizados por el usuario
    public function sincronizar($args = []){
        //Usamos key value ya que vamos a recorrer un arreglo asociativo
        //compara propiedades del obj con llaves del arreglo y reescribe
        foreach($args as $key=> $value){ 
            if(property_exists($this, $key) && !is_null($value)){
                $this->$key =$value;
            }


        }
    }

   
}