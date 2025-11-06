<?php
require ('../config.php');

if (isset($_GET['articleId'])) {
    $articleId = (int)$_GET['articleId'];
    $article = Article::getById($articleId);

    echo json_encode([
            'success' => true,
            'content' => $article->content,
            'title' => $article->title,
            'method' => 'GET'
        ], JSON_UNESCAPED_UNICODE);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем и декодируем JSON данные из тела запроса
    $jsonInput = file_get_contents('php://input');
    $input = json_decode($jsonInput, true);
    
    // Проверяем наличие articleId в данных
    if (isset($input['articleId'])) {
        $articleId = (int)$input['articleId'];
        
        // Получаем статью из базы данных
        $article = Article::getById($articleId);
        
        if ($article) {
            // Получаем авторов статьи
            $authors = Article::getArticleAuthors($articleId);
            $authorData = [];
            foreach ($authors as $author) {
                $authorData[] = [
                    'id' => $author->id,
                    'username' => $author->username
                ];
            }
            
            // Формируем успешный ответ
            echo json_encode([
                'success' => true,
                'content' => $article->content,
                'title' => $article->title,
                'summary' => $article->summary,
                'publicationDate' => $article->publicationDate,
                'category' => $article->category_name,
                'subcategory' => $article->subcategory_name,
                'authors' => $authorData,
                'articleId' => $articleId
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Статья с ID ' . $articleId . ' не найдена'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Отсутствует параметр articleId'
        ]);
    }
}