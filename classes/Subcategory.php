<?php

/**
 * Класс для обработки подкатегорий статей
 */

class Subcategory
{
    // Свойства

    /**
    * @var int ID подкатегории из базы данных
    */
    public $id = null;

    /**
    * @var string Название подкатегории
    */
    public $name = null;

    /**
    * @var int ID категории, к которой относится подкатегория
    */
    public $category_id = null;

    /**
    * @var string Короткое описание подкатегории
    */
    public $description = null;

    /**
    * @var string Название категории (для JOIN запросов)
    */
    public $category_name = null;


    /**
    * Устанавливаем свойства объекта с использованием значений в передаваемом массиве
    *
    * @param assoc Значения свойств
    */

    public function __construct( $data=array() ) {
      if ( isset( $data['id'] ) ) $this->id = (int) $data['id'];
      if ( isset( $data['name'] ) ) $this->name = $data['name'];
      if ( isset( $data['category_id'] ) ) $this->category_id = (int) $data['category_id'];
      if ( isset( $data['description'] ) ) $this->description = $data['description'];
      if ( isset( $data['category_name'] ) ) $this->category_name = $data['category_name'];
    }

    /**
    * Устанавливаем свойства объекта с использованием значений из формы редактирования
    *
    * @param assoc Значения из формы редактирования
    */

    public function storeFormValues ( $params ) {
      // Store all the parameters
      $this->__construct( $params );
    }

    /**
    * Возвращаем объект Subcategory, соответствующий заданному ID
    *
    * @param int ID подкатегории
    * @return Subcategory|false Объект Subcategory или false, если запись не была найдена или в случае другой ошибки
    */

    public static function getById( $id ) {
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "SELECT s.*, c.name as category_name 
                FROM subcategories s
                LEFT JOIN categories c ON s.category_id = c.id 
                WHERE s.id = :id";
        $st = $conn->prepare( $sql );
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch();
        $conn = null;
        if ($row) 
            return new Subcategory($row);
        return false;
    }

    /**
    * Возвращаем все подкатегории для определенной категории
    *
    * @param int ID категории
    * @return Array|false Массив объектов Subcategory
    */
    public static function getByCategoryId( $category_id ) {
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "SELECT s.*, c.name as category_name
                FROM subcategories s 
                LEFT JOIN categories c ON s.category_id = c.id 
                WHERE s.category_id = :category_id 
                ORDER BY s.name";
        $st = $conn->prepare( $sql );
        $st->bindValue(":category_id", $category_id, PDO::PARAM_INT);
        $st->execute();
        $list = array();
        while ( $row = $st->fetch() ) {
            $subcategory = new Subcategory( $row );
            $list[] = $subcategory;
        }
        $conn = null;
        return $list;
    }

    /**
    * Возвращаем все (или диапазон) объектов Subcategory из базы данных
    *
    * @param int Optional Количество возвращаемых строк (по умолчанию = all)
    * @param string Optional Столбец, по которому сортируются подкатегории (по умолчанию = "s.name ASC")
    * @return Array|false Двух элементный массив: results => массив с объектами Subcategory; totalRows => общее количество подкатегорий
    */
    public static function getList( $numRows=1000000, $order="s.name ASC" ) { 
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD);
        $fromPart = "FROM subcategories s LEFT JOIN categories c ON s.category_id = c.id";
        $sql = "SELECT s.*, c.name as category_name $fromPart
                ORDER BY $order LIMIT :numRows";

        $st = $conn->prepare( $sql );
        $st->bindValue( ":numRows", $numRows, PDO::PARAM_INT );
        $st->execute();
        $list = array();

        while ( $row = $st->fetch() ) {
            $subcategory = new Subcategory( $row );
            $list[] = $subcategory;
        }

        // Получаем общее количество подкатегорий, которые соответствуют критериям
        $sql = "SELECT COUNT(*) AS totalRows $fromPart";
        $totalRows = $conn->query( $sql )->fetch();
        $conn = null;
        return ( array ( "results" => $list, "totalRows" => $totalRows[0] ) );
    }

    /**
    * Вставляем текущий объект Subcategory в базу данных и устанавливаем его свойство ID.
    */

    public function insert() {
        // У объекта Subcategory уже есть ID?
        if ( !is_null( $this->id ) ) trigger_error ( "Subcategory::insert(): Attempt to insert a Subcategory object that already has its ID property set (to $this->id).", E_USER_ERROR );

        // Вставляем подкатегорию
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "INSERT INTO subcategories ( name, category_id, description ) VALUES ( :name, :category_id, :description)";
        $st = $conn->prepare ( $sql );
        $st->bindValue( ":name", $this->name, PDO::PARAM_STR );
        $st->bindValue( ":category_id", $this->category_id, PDO::PARAM_INT );
        $st->bindValue( ":description", $this->description, PDO::PARAM_STR );
        $st->execute();
        $this->id = $conn->lastInsertId();
        $conn = null;
    }

    /**
    * Обновляем текущий объект Subcategory в базе данных.
    */

    public function update() {
        // У объекта Subcategory есть ID?
        if ( is_null( $this->id ) ) trigger_error ( "Subcategory::update(): Attempt to update a Subcategory object that does not have its ID property set.", E_USER_ERROR );

        // Обновляем подкатегорию
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "UPDATE subcategories SET name=:name, category_id=:category_id, description=:description WHERE id = :id";
        $st = $conn->prepare ( $sql );
        $st->bindValue( ":name", $this->name, PDO::PARAM_STR );
        $st->bindValue( ":category_id", $this->category_id, PDO::PARAM_INT );
        $st->bindValue( ":description", $this->description, PDO::PARAM_STR );
        $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
        $st->execute();
        $conn = null;
    }

    /**
    * Удаляем текущий объект Subcategory из базы данных.
    */

    public function delete() {
        // У объекта Subcategory есть ID?
        if ( is_null( $this->id ) ) trigger_error ( "Subcategory::delete(): Attempt to delete a Subcategory object that does not have its ID property set.", E_USER_ERROR );

        // Удаляем подкатегорию
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $st = $conn->prepare ( "DELETE FROM subcategories WHERE id = :id LIMIT 1" );
        $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
        $st->execute();
        $conn = null;
    }
}