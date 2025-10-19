<?php

/**
 * Класс для обработки статей
 */
class Article
{
    // Свойства
    /**
    * @var int ID статей из базы данны
    */
    public $id = null;

    /**
    * @var int Дата первой публикации статьи
    */
    public $publicationDate = null;

    /**
    * @var string Полное название статьи
    */
    public $title = null;

     /**
    * @var int ID категории статьи
    */
    public $categoryId = null;

    /**
    * @var int ID подкатегории статьи
    */
    public $subcategory_id = null;

    /**
    * @var string Название категории (для JOIN запросов)
    */
    public $category_name = null;

    /**
    * @var string Название подкатегории (для JOIN запросов)
    */
    public $subcategory_name = null;

    /**
    * @var string Краткое описание статьи
    */
    public $summary = null;

    /**
    * @var string HTML содержание статьи
    */
    public $content = null;

    /**
    * @var bool Acitivity of article
    */
    public $activity = null;

     /**
    * @var array Массив авторов статьи
    */
    public $authors = array();
    
    /**
     * Создаст объект статьи
     * 
     * @param array $data массив значений (столбцов) строки таблицы статей
     */
    public function __construct($data=array())
    {
        
      if (isset($data['id'])) {
          $this->id = (int) $data['id'];
      }
      
      if (isset( $data['publicationDate'])) {
          $this->publicationDate = (string) $data['publicationDate'];     
      }

      //die(print_r($this->publicationDate));

      if (isset($data['title'])) {
          $this->title = $data['title'];        
      }
      
      if (isset($data['categoryId'])) {
          $this->categoryId = (int) $data['categoryId'];      
      }

      if (isset($data['subcategory_id'])) {
          $this->subcategory_id = (int) $data['subcategory_id'];      
      }

      if (isset($data['category_name'])) {
          $this->category_name = $data['category_name'];        
      }

      if (isset($data['subcategory_name'])) {
          $this->subcategory_name = $data['subcategory_name'];        
      }
      
      if (isset($data['summary'])) {
          $this->summary = $data['summary'];         
      }
      
      if (isset($data['content'])) {
          $this->content = $data['content'];  
      }

      if (isset($data['activity'])) {
          $this->activity = $data['activity'];  
      }
      if ( isset($params['authors']) ) {
          $this->authors = $params['authors'];
      }
    }


    /**
    * Устанавливаем свойства с помощью значений формы редактирования записи в заданном массиве
    *
    * @param assoc Значения записи формы
    */
    public function storeFormValues ( $params ) {

      // Сохраняем все параметры
      $this->__construct( $params );

      // Разбираем и сохраняем дату публикации
      if ( isset($params['publicationDate']) ) {
        $publicationDate = explode ( '-', $params['publicationDate'] );

      if ( isset($params['authors']) ) {
          $this->authors = $params['authors'];
      }

        if ( count($publicationDate) == 3 ) {
          list ( $y, $m, $d ) = $publicationDate;
          $this->publicationDate = mktime ( 0, 0, 0, $m, $d, $y );
        }
      }

          // Преобразуем пустую подкатегорию в NULL
        if ( isset($params['subcategory_id']) && $params['subcategory_id'] === '' ) {
            $this->subcategory_id = null;
        }

        // Разбираем и сохраняем дату публикации
        if ( isset($params['publicationDate']) ) {
            $publicationDate = explode ( '-', $params['publicationDate'] );

            if ( count($publicationDate) == 3 ) {
                list ( $y, $m, $d ) = $publicationDate;
                $this->publicationDate = mktime ( 0, 0, 0, $m, $d, $y );
            }
        }
    }


    /**
    * Возвращаем объект статьи соответствующий заданному ID статьи
    *
    * @param int ID статьи
    * @return Article|false Объект статьи или false, если запись не найдена или возникли проблемы
    */
    public static function getById($id) {
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "SELECT a.*, UNIX_TIMESTAMP(a.publicationDate) AS publicationDate, 
                       c.name as category_name, s.name as subcategory_name 
                FROM articles a 
                LEFT JOIN categories c ON a.categoryId = c.id 
                LEFT JOIN subcategories s ON a.subcategory_id = s.id 
                WHERE a.id = :id";
        $st = $conn->prepare($sql);
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch();
        $conn = null;
        
        if ($row) { 
            $article = new Article($row);
            $article->loadAuthors();
            return $article;
        }
    }


    /**
    * Загружает авторов для текущей статьи
    */
    public function loadAuthors() {
        if ( $this->id ) {
            $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
            $sql = "SELECT u.id, u.username 
                    FROM users u 
                    INNER JOIN article_authors aa ON u.id = aa.user_id 
                    WHERE aa.article_id = :article_id";
            $st = $conn->prepare($sql);
            $st->bindValue(":article_id", $this->id, PDO::PARAM_INT);
            $st->execute();
            
            $this->authors = array();
            while ( $row = $st->fetch() ) {
                $this->authors[] = $row['id'];
            }
            $conn = null;
        }
    }

    /**
    * Сохраняет авторов статьи
    */
    public function saveAuthors() {
        // Сначала удаляем старых авторов
        $this->deleteAuthors();
        
        // Затем добавляем новых
        if ( !empty($this->authors) ) {
            $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
            $sql = "INSERT INTO article_authors (article_id, user_id) VALUES ";
            $values = array();
            $params = array();
            
            foreach ( $this->authors as $index => $authorId ) {
                $values[] = "(:article_id, :user_id_$index)";
                $params[":user_id_$index"] = (int)$authorId;
            }
            
            $sql .= implode(", ", $values);
            $st = $conn->prepare($sql);
            $st->bindValue(":article_id", $this->id, PDO::PARAM_INT);
            
            foreach ( $params as $key => $value ) {
                $st->bindValue($key, $value, PDO::PARAM_INT);
            }
            
            $st->execute();
            $conn = null;
        }
    }

    /**
    * Удаляет всех авторов статьи
    */
    public function deleteAuthors() {
        if ( $this->id ) {
            $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
            $sql = "DELETE FROM article_authors WHERE article_id = :article_id";
            $st = $conn->prepare($sql);
            $st->bindValue(":article_id", $this->id, PDO::PARAM_INT);
            $st->execute();
            $conn = null;
        }
    }


    /**
    * Возвращает все (или диапазон) объекты Article из базы данных
    *
    * @param int $numRows Количество возвращаемых строк (по умолчанию = 1000000)
    * @param int $categoryId Вернуть статьи только из категории с указанным ID
    * @param string $order Столбец, по которому выполняется сортировка статей (по умолчанию = "publicationDate DESC")
    * @return Array|false Двух элементный массив: results => массив объектов Article; totalRows => общее количество строк
    */
    public static function getList($numRows=1000000, 
            $categoryId=null, $order="a.publicationDate DESC") 
    {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $fromPart = "FROM articles a 
                    LEFT JOIN categories c ON a.categoryId = c.id 
                    LEFT JOIN subcategories s ON a.subcategory_id = s.id";
        $categoryClause = $categoryId ? "WHERE a.categoryId = :categoryId" : "";
        $sql = "SELECT a.*, UNIX_TIMESTAMP(a.publicationDate) 
                AS publicationDate, c.name as category_name, s.name as subcategory_name
                $fromPart $categoryClause
                ORDER BY  $order  LIMIT :numRows";
        
        $st = $conn->prepare($sql);
        $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
	/**
	 * Можно использовать debugDumpParams() для отладки параметров, 
	 * привязанных выше с помощью bind()
	 * @see https://www.php.net/manual/ru/pdostatement.debugdumpparams.php
	 */
      
        if ($categoryId) 
            $st->bindValue( ":categoryId", $categoryId, PDO::PARAM_INT);
        
        $st->execute(); // выполняем запрос к базе данных
        $list = array();

        while ($row = $st->fetch()) {
            $article = new Article($row);
            $list[] = $article;
        }

        // Получаем общее количество статей, которые соответствуют критерию
        $sql = "SELECT COUNT(*) AS totalRows $fromPart $categoryClause";
	$st = $conn->prepare($sql);
	if ($categoryId) 
            $st->bindValue( ":categoryId", $categoryId, PDO::PARAM_INT);
	$st->execute(); // выполняем запрос к базе данных                    
        $totalRows = $st->fetch();
        $conn = null;
        
        return (array(
            "results" => $list, 
            "totalRows" => $totalRows[0]
            ) 
        );
    }

    
    public static function getListBySubcategory( $subcategoryId, $numRows=1000000, $order="a.publicationDate DESC" ) {
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $fromPart = "FROM articles a 
                    LEFT JOIN categories c ON a.categoryId = c.id 
                    LEFT JOIN subcategories s ON a.subcategory_id = s.id 
                    WHERE a.subcategory_id = :subcategoryId";
        $sql = "SELECT a.*, UNIX_TIMESTAMP(a.publicationDate) AS publicationDate, 
                    c.name as category_name, s.name as subcategory_name 
                $fromPart
                ORDER BY $order LIMIT :numRows";

        $st = $conn->prepare( $sql );
        $st->bindValue( ":subcategoryId", $subcategoryId, PDO::PARAM_INT );
        $st->bindValue( ":numRows", $numRows, PDO::PARAM_INT );
        $st->execute();
        $list = array();

        while ( $row = $st->fetch() ) {
            $article = new Article( $row );
            $list[] = $article;
        }

        $sql = "SELECT COUNT(*) AS totalRows $fromPart";
        $st = $conn->prepare( $sql );
        $st->bindValue( ":subcategoryId", $subcategoryId, PDO::PARAM_INT );
        $st->execute();
        $totalRows = $st->fetch();
        $conn = null;
        return ( array ( "results" => $list, "totalRows" => $totalRows[0] ) );
    }

    /**
     * Возвращает все (или диапазон) объекты Article из базы данных
     *
     * @param int $numRows Количество возвращаемых строк (по умолчанию = 1000000)
     * @param int $categoryId Вернуть статьи только из категории с указанным ID
     * @param string $order Столбец, по которому выполняется сортировка статей (по умолчанию = "publicationDate DESC")
     * @return Array|false Двух элементный массив: results => массив объектов Article; totalRows => общее количество строк
     */
    public static function getListVersion2($numRows=1000000, 
            $categoryId=null, $order="a.publicationDate DESC") 
    {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $fromPart = "FROM articles a 
                    LEFT JOIN categories c ON a.categoryId = c.id 
                    LEFT JOIN subcategories s ON a.subcategory_id = s.id";
        $categoryClause = $categoryId ? "WHERE a.categoryId = :categoryId" : "";
        $sql = "SELECT 
                    a.id,
                    a.publicationDate,
                    a.categoryId,
                    a.subcategory_id,
                    a.title,
                    c.name as category_name,
                    s.name as subcategory_name,
                    CASE 
                        WHEN LENGTH(a.content) > 50 THEN CONCAT(SUBSTRING(a.content, 1, 50), '...')
                        ELSE a.content
                    END AS summary,
                    a.content,
                    a.activity,
                    UNIX_TIMESTAMP(a.publicationDate) AS publicationDate
                $fromPart $categoryClause
                ORDER BY $order LIMIT :numRows";
        
        $st = $conn->prepare($sql);
        $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
        
        /**
         * Можно использовать debugDumpParams() для отладки параметров, 
         * привязанных выше с помощью bind()
         * @see https://www.php.net/manual/ru/pdostatement.debugdumpparams.php
         */
        
        if ($categoryId) 
            $st->bindValue(":categoryId", $categoryId, PDO::PARAM_INT);
        
        $st->execute(); // выполняем запрос к базе данных
        $list = array();

        while ($row = $st->fetch()) {
            $article = new Article($row);
            $list[] = $article;
        }
            
        // Получаем общее количество статей, которые соответствуют критерию
        $sql = "SELECT COUNT(*) AS totalRows $fromPart $categoryClause";
        $st = $conn->prepare($sql);
        if ($categoryId) 
            $st->bindValue(":categoryId", $categoryId, PDO::PARAM_INT);
        $st->execute(); // выполняем запрос к базе данных                    
        $totalRows = $st->fetch();
        $conn = null;
        
        return (array(
            "results" => $list, 
            "totalRows" => $totalRows[0]
            ) 
        );
    }

    /**
    * Вставляем текущий объект Article в базу данных, устанавливаем его ID
    */
    public function insert() {

        // Есть уже у объекта Article ID?
        if ( !is_null( $this->id ) ) trigger_error ( "Article::insert(): Attempt to insert an Article object that already has its ID property set (to $this->id).", E_USER_ERROR );

        // Вставляем статью
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "INSERT INTO articles ( publicationDate, categoryId, subcategory_id, title, summary, content, activity ) VALUES ( FROM_UNIXTIME(:publicationDate), :categoryId, :subcategory_id, :title, :summary, :content, :activity )";
        $st = $conn->prepare ( $sql );
        $st->bindValue( ":publicationDate", $this->publicationDate, PDO::PARAM_INT );
        $st->bindValue( ":categoryId", $this->categoryId, PDO::PARAM_INT );
        $st->bindValue( ":subcategory_id", $this->subcategory_id, PDO::PARAM_INT );
        $st->bindValue( ":title", $this->title, PDO::PARAM_STR );
        $st->bindValue( ":summary", $this->summary, PDO::PARAM_STR );
        $st->bindValue( ":content", $this->content, PDO::PARAM_STR );
        $st->bindValue( ":activity", $this->activity, PDO::PARAM_INT );
        $st->execute();
        $this->id = $conn->lastInsertId();
        $conn = null;
    }

    /**
    * Обновляем текущий объект статьи в базе данных
    */
    public function update() {

      // Есть ли у объекта статьи ID?
      if ( is_null( $this->id ) ) trigger_error ( "Article::update(): "
              . "Attempt to update an Article object "
              . "that does not have its ID property set.", E_USER_ERROR );

      // Обновляем статью
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $sql = "UPDATE articles SET publicationDate=FROM_UNIXTIME(:publicationDate),"
              . " categoryId=:categoryId, subcategory_id=:subcategory_id, title=:title, summary=:summary, activity=:activity, "
              . " content=:content WHERE id = :id";
      
      $st = $conn->prepare ( $sql );
      $st->bindValue( ":publicationDate", $this->publicationDate, PDO::PARAM_INT );
      $st->bindValue( ":categoryId", $this->categoryId, PDO::PARAM_INT );
      $st->bindValue( ":subcategory_id", $this->subcategory_id, PDO::PARAM_INT );
      $st->bindValue( ":title", $this->title, PDO::PARAM_STR );
      $st->bindValue( ":summary", $this->summary, PDO::PARAM_STR );
      $st->bindValue( ":content", $this->content, PDO::PARAM_STR );
      $st->bindValue( ":activity", $this->activity, PDO::PARAM_INT );
      $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
      $st->execute();
      $conn = null;

       $this->saveAuthors();
    }


    /**
    * Удаляем текущий объект статьи из базы данных
    */
    public function delete() {

      // Есть ли у объекта статьи ID?
      if ( is_null( $this->id ) ) trigger_error ( "Article::delete(): Attempt to delete an Article object that does not have its ID property set.", E_USER_ERROR );

      $this->deleteAuthors();

      // Удаляем статью
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $st = $conn->prepare ( "DELETE FROM articles WHERE id = :id LIMIT 1" );
      $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
      $st->execute();
      $conn = null;
    }

     /**
    * Получает авторов статьи с полной информацией
    */
    public static function getArticleAuthors($articleId) {
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "SELECT u.* 
                FROM users u 
                INNER JOIN article_authors aa ON u.id = aa.user_id 
                WHERE aa.article_id = :article_id 
                ORDER BY u.username";
        $st = $conn->prepare($sql);
        $st->bindValue(":article_id", $articleId, PDO::PARAM_INT);
        $st->execute();
        
        $authors = array();
        while ( $row = $st->fetch() ) {
            $authors[] = new User($row);
        }
        $conn = null;
        return $authors;
    }
}