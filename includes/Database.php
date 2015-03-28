<?php

/**
 * Description of Database
 *
 * @author Cristopher Mendoza
 */
class Database {

    var $table;

    public function __construct($table) {
        $this->table = $table;
    }

    static function connectDB() {
        $db = new mysqli('localhost', 'wilddeve_examen', 'yalla ya', 'wilddeve_examen');
        return $db;
    }

    public function save($data) {
        $db = self::connectDB();
        $query = 'INSERT INTO ' . $this->table . ' (' . implode(', ', array_keys($data)) . ') VALUES ("';
        /* foreach ($data as $value) {
          $query .= '"' . $value . '",';
          } */
        $query .= implode('", "', $data);
        $query .= '")';
        if (!$db->query($query)) {
            echo '{ "message": "Hubo un problema al momento de crear el Registro, INFO: ' . str_replace('\'', '', $db->error) . ' "}';
        } else {
            //echo '{ "message": "El Registro a sido creado con exito", "nextStep" : "http://localhost/inventario/newRegister.php"}';
        }
        //echo $query . '<br>';
        $lastId = $db->insert_id;
        $db->close();
        return $lastId;
    }

    public function update($data, $filterColumn, $filterValue) {
        $db = self::connectDB();
        $query = 'UPDATE ' . $this->table . ' SET ';
        foreach ($data as $key => $value) {
            $query .= $key . ' = "' . $value . '" ';
        }
        $query .= 'WHERE ' . $filterColumn . ' = "' . $filterValue . '"';
        if (!$db->query($query)) {
            echo ('{ "message": "Hubo un problema al momento de crear el Registro, INFO: ' . str_replace('\'', '', $db->error) . ' "}');
        } else {
            echo '{ "message": "El Registro a sido creado con exito", "nextStep" : "http://localhost/inventario/newRegister.php"}';
        }
        echo '<br>'.$query;
        $db->close();
    }
    
    public function getAllData() {
        $db = self::connectDB();
        $query = 'SELECT * FROM ' . $this->table;
        $exec = $db->query($query);
        //echo $query.'<br>';
        while ($fila = $exec->fetch_assoc()) {
            $data[] = array_map('utf8_encode', $fila);
        }
        $db->close();
        if (isset($data)) {
            return $data;
        } else {
            //die($query);
            return FALSE;
        }
    }

    public function getDataById($fields, $id) {
        $db = self::connectDB();
        $query = 'SELECT ' . implode(', ', $fields) . ' FROM ' . $this->table . ' WHERE id' . $this->table . ' = ' . $id;
        $exec = $db->query($query);
        $data = $exec->fetch_array();
        $data = array_map('utf8_encode', $data);
        $db->close();
        return $data;
    }

    public function getAllDataByField($field, $value) {
        $db = self::connectDB();
        $query = 'SELECT * FROM ' . $this->table . ' WHERE ' . $field . ' = "' . $value . '"';
        $exec = $db->query($query);
        //echo $query.'<br>';
        while ($fila = $exec->fetch_assoc()) {
            $data[] = array_map('utf8_encode', $fila);
        }
        $db->close();
        if (isset($data)) {
            return $data;
        } else {
            //die($query);
            return FALSE;
        }
    }

    public function getFilteredDataOrderedByField($filterColumn, $filterValue, $orderColumn, $orderType) {
        $db = self::connectDB();
        $query = 'SELECT * FROM ' . $this->table . ' WHERE ' . $filterColumn . ' = "' . $filterValue . '"';
        $query .= 'ORDER BY ' . $orderColumn . ' ' . $orderType;
        $exec = $db->query($query);
        while ($fila = $exec->fetch_assoc()) {
            $data[] = array_map('utf8_encode', $fila);
        }
        $db->close();
        if (isset($data)) {
            return $data;
        } else {
            //die($query);
            return FALSE;
        }
    }

    public function printTable() {
        return $this->table;
    }

}
