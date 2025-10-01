<?php
    class Model{

        protected static $conn;
        protected static $table;
      
    
        public static function setConnection($conn){
            self::$conn = $conn;   
        }
        protected static function all(){
            try{
                $sql = "select * from " . static::$table;
                $result = self::$conn->query($sql);
                $rows = $result->fetchAll();
                return count($rows) > 0 ? $rows : null;
            }catch(PDOException $e){
                die("Error fetching data: " . $e->getMessage());
            }
        }
    
        protected static function find($id){
            try{
                $sql = "select * from " . static::$table . " where id = :id";
                $stmt = self::$conn->prepare($sql);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row ?? null; 
    
            }catch(PDOException $e){
                die("Error fetching data: " . $e->getMessage());
            }
        }
    
        protected static function create (array $data){
           try{
            $columns = implode(", ", array_keys($data));
            $values = implode(", ", array_map(fn ($key) => ":$key", array_keys($data))); //map - binabago yung structure ng bawat laman ng array, tapos implode - pinagsasama sama into a single string value
            $sql = "INSERT INTO " . static::$table . " ($columns) VALUES ($values)"; 
            $stmt = self::$conn->prepare($sql);
            foreach ($data as $key => $value){
                $stmt->bindValue(":$key", $value); //bind value, para maipasa yung value sa statement
            }
            $stmt->execute(); //execute the statement
            $id = self::$conn->lastInsertId(); //get the last inserted id
            return self::find($id); //returns associative array based sa mahanap niyang row sa database
           }catch (PDOException $e){
                die("Error inserting data: " . $e->getMessage());
            }   
        }
    
        protected static function updateByID($id, array $data){
            try{
                $set = implode(", ", array_map(fn($key) => "$key = :$key", array_keys($data)));
                $sql = "UPDATE " . static::$table . " SET $set WHERE id = :id";
                $stmt = self::$conn->prepare($sql);
                foreach ($data as $key => $value){
                    $stmt->bindValue(":$key", $value); //bind value, para maipasa yung value sa statement
                }
                $stmt->bindValue(":id", $id); //bind value, manually
                $stmt->execute(); //execute the statement
                return self::find($id); //returns associative array based sa mahanap niyang row sa database
    
            }catch (PDOException $e){
                die("Error updating data: " . $e->getMessage());
            }
    
            
        }
    
        protected static function deleteByID($id){
            
            try{
                $sql = "DELETE FROM " . static::$table . " WHERE id = :id";
                $stmt = self::$conn->prepare($sql);
                $stmt->bindParam(":id", $id); //bind value, manually
                $stmt->execute(); //execute the statement
                
                return $stmt->rowCount() > 0; //returns true if row is deleted
    
            }catch (PDOException $e){
                die("Error deleting data: " . $e->getMessage());
            }
        }

        protected static function where($column, $operator, $value){
            try{
                $sql = "SELECT * FROM " . static::$table
                    . " WHERE $column $operator :value";
    
                $stmt = self::$conn->prepare($sql);
    
                $stmt->bindValue(':value', $value);
    
                $stmt->execute();
    
                $rows = $stmt->fetchAll();
    
                return count($rows) > 0
                    ? $rows : null;
            }
            catch(PDOException $e){
                die("Error fetching data: " . $e->getMessage());
                
            }
        }
    
        protected function belongsToMany($relatedClass, $pivotTable, $foreignKey, $relatedKey){
            try{
                $relatedTable = $relatedClass::$table;
    
                $sql = "SELECT rt.* FROM $relatedTable rt INNER JOIN $pivotTable pt ON rt.id = pt.$relatedKey WHERE pt.$foreignKey =:id";
    
                $stmt = self::$conn->prepare($sql);
    
                $stmt->bindValue(':id', $this->id);
    
                $stmt->execute();
    
                $rows = $stmt->fetchAll();
    
                return $rows
                ? array_map(fn($data) => new $relatedClass($data), $rows)
                : [];
            }
            catch(PDOException $e){
                die("Error fetching data: " . $e->getMessage());
            }
        }

        

    }







    ?>