<?php

//phpinfo(); die();

require("config.php");

try {
    initApplication();
} catch (Exception $e) { 
    $results['errorMessage'] = $e->getMessage();
    require(TEMPLATE_PATH . "/viewErrorPage.php");
}


function initApplication()
{
    $action = isset($_GET['action']) ? $_GET['action'] : "";

    switch ($action) {
        case 'archive':
          archive();
          break;
        case 'viewArticle':
          viewArticle();
          break;
        case 'archiveBySubcategory':
          archiveBySubcategory();
          break;
        default:
          homepage();
    }
}
function archiveBySubcategory() {
    $results = array();
    $subcategoryId = isset($_GET['subcategoryId']) ? (int)$_GET['subcategoryId'] : null;
    $subcategory = Subcategory::getById($subcategoryId);
    
    if (!$subcategory) {
        header("Location: index.php");
        return;
    }
    
    $data = Article::getListBySubcategory($subcategoryId);
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    $results['subcategory'] = $subcategory;
    $results['pageHeading'] = $subcategory->name;
    $results['pageTitle'] = $subcategory->name . " | Widget News";
    
    $data = Category::getList();
    $results['categories'] = array();
    foreach ($data['results'] as $category) {
        $results['categories'][$category->id] = $category;
    }
    
    require(TEMPLATE_PATH . "/archive.php");
}
function archive() 
{
    $results = [];
    
    $categoryId = ( isset( $_GET['categoryId'] ) && $_GET['categoryId'] ) ? (int)$_GET['categoryId'] : null;
    
    $results['category'] = Category::getById( $categoryId );
    
    $data = Article::getList( 100000, $results['category'] ? $results['category']->id : null );
    
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    
    $data = Category::getList();
    $results['categories'] = array();
    
    foreach ( $data['results'] as $category ) {
        $results['categories'][$category->id] = $category;
    }
    
    $results['pageHeading'] = $results['category'] ?  $results['category']->name : "Article Archive";
    $results['pageTitle'] = $results['pageHeading'] . " | Widget News";
    
    require( TEMPLATE_PATH . "/archive.php" );
}

/**
 * Загрузка страницы с конкретной статьёй
 * 
 * @return null
 */
function viewArticle() 
{   
    if ( !isset($_GET["articleId"]) || !$_GET["articleId"] ) {
      homepage();
      return;
    }

    $results = array();
    $articleId = (int)$_GET["articleId"];
    $results['article'] = Article::getById($articleId);
    
    if (!$results['article']) {
        throw new Exception("Статья с id = $articleId не найдена");
    }
    
    $results['category'] = Category::getById($results['article']->categoryId);
    $results['pageTitle'] = $results['article']->title . " | Простая CMS";
    
    require(TEMPLATE_PATH . "/viewArticle.php");
}

/**
 * Вывод домашней ("главной") страницы сайта
 */
function homepage() 
{
    $results = array();
    $data = Article::getListVersion2(HOMEPAGE_NUM_ARTICLES);

    $activeArticles = array_filter($data['results'], function($article) {
        return $article->activity == 1;
    });

    $results['articles'] = $activeArticles;
    $results['totalRows'] = count($activeArticles);
    
    $data = Category::getList();
    $results['categories'] = array();
    foreach ( $data['results'] as $category ) { 
        $results['categories'][$category->id] = $category;
    } 
    
    $results['pageTitle'] = "Простая CMS на PHP";
    
//    echo "<pre>";
//    print_r($data);
//    echo "</pre>";
//    die();
    
    require(TEMPLATE_PATH . "/homepage.php");
    
}