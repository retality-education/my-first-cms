<?php

/**
 * Класс для хранения пользователя
 */

class User
{
    // Свойства

    /**
    * @var int ID пользователя из базы данных
    */
    public $id = null;

    /**
    * @var string Имя пользователя
    */
    public $username = null;

    /**
    * @var string Пароль
    */
    public $password = null;

    /**
    * @var int Флаг активности пользователя
    */
    public $activity = null;


    /**
    * Устанавливаем свойства объекта с использованием значений в передаваемом массиве
    *
    * @param data Значения свойств
    */

    public function __construct( $data=array() ) {
      if ( isset( $data['id'] ) ) $this->id = (int) $data['id'];
      if ( isset( $data['username'] ) ) $this->username = $data['username'];
      if ( isset( $data['password'] ) ) $this->password = $data['password'];
      if ( isset( $data['activity'] ) ) $this->activity = (int) $data['activity'];
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
    * Возвращаем объект User, соответствующий заданному ID
    *
    * @param int ID пользователя
    * @return User|false Объект User object или false, если запись не была найдена или в случае другой ошибки
    */

    public static function getById( $id ) 
    {
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "SELECT * FROM users WHERE id = :id";
        $st = $conn->prepare( $sql );
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch();
        $conn = null;
        if ($row) 
            return new User($row);
    }


    /**
    * Возвращаем все (или диапазон) объектов User из базы данных
    *
    * @param int Optional Количество возвращаемых строк (по умолчаниюt = all)
    * @param string Optional Столбец, по которому сортируются пользователи(по умолчанию = "username ASC")
    * @return Array|false Двух элементный массив: results => массив с объектами User; totalRows => общее количество пользователей
    */
    public static function getList( $numRows=1000000, $order="username ASC" ) 
    { 
    $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD);
    $fromPart = "FROM users";
    $sql = "SELECT * $fromPart
            ORDER BY $order LIMIT :numRows";

    $st = $conn->prepare( $sql );
    $st->bindValue( ":numRows", $numRows, PDO::PARAM_INT );
    $st->execute();
    $list = array();

    while ( $row = $st->fetch() ) {
      $user = new User( $row );
      $list[] = $user;
    }

    // Получаем общее количество пользователей, которые соответствуют критериям
    $sql = "SELECT COUNT(*) AS totalRows $fromPart";
    $totalRows = $conn->query( $sql )->fetch();
    $conn = null;
    return ( array ( "results" => $list, "totalRows" => $totalRows[0] ) );
    }


    /**
    * Вставляем текущий объект User в базу данных и устанавливаем его свойство ID.
    */

    public function insert() {

      // У объекта User уже есть ID?
      if ( !is_null( $this->id ) ) trigger_error ( "User::insert(): Attempt to insert a User object that already has its ID property set (to $this->id).", E_USER_ERROR );

      // Вставляем пользователя
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $sql = "INSERT INTO users ( username, password, activity) VALUES ( :username, :password, :activity )";
      $st = $conn->prepare ( $sql );
      $st->bindValue( ":username", $this->username, PDO::PARAM_STR );
      $st->bindValue( ":password", $this->password, PDO::PARAM_STR );
      $st->bindValue( ":activity", $this->activity, PDO::PARAM_INT );
      $st->execute();
      $this->id = $conn->lastInsertId();
      $conn = null;
    }


    /**
    * Обновляем текущий объект User в базе данных.
    */

    public function update() {

      // У объекта User есть ID?
      if ( is_null( $this->id ) ) trigger_error ( "User::update(): Attempt to update a User object that does not have its ID property set.", E_USER_ERROR );

      // Обновляем пользователя
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $sql = "UPDATE users SET username=:username, password=:password, activity=:activity WHERE id = :id";
      $st = $conn->prepare ( $sql );
      $st->bindValue( ":username", $this->username, PDO::PARAM_STR );
      $st->bindValue( ":password", $this->password, PDO::PARAM_STR );
      $st->bindValue( ":activity", $this->activity, PDO::PARAM_INT );
      $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
      $st->execute();
      $conn = null;
    }


    /**
    * Удаляем текущий объект User из базы данных.
    */

    public function delete() {

      // У объекта User есть ID?
      if ( is_null( $this->id ) ) trigger_error ( "User::delete(): Attempt to delete a User object that does not have its ID property set.", E_USER_ERROR );

      // Удаляем пользователя
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $st = $conn->prepare ( "DELETE FROM users WHERE id = :id LIMIT 1" );
      $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
      $st->execute();
      $conn = null;
    }

}
	  
	

