<?php

require("config.php");
session_start();
$action = isset($_GET['action']) ? $_GET['action'] : "";
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "";

if ($action != "login" && $action != "logout" && !$username) {
    login();
    exit;
}

switch ($action) {
    case 'login':
        login();
        break;
    case 'logout':
        logout();
        break;
    case 'newArticle':
        newArticle();
        break;
    case 'editArticle':
        editArticle();
        break;
    case 'deleteArticle':
        deleteArticle();
        break;
    case 'listCategories':
        listCategories();
        break;
    case 'newCategory':
        newCategory();
        break;
    case 'editCategory':
        editCategory();
        break;
    case 'deleteCategory':
        deleteCategory();
        break;
    case 'listUsers':
        listUsers();
        break;
    case 'newUser':
        newUser();
        break;
    case 'editUser':
        editUser();
        break;
    case 'deleteUser':
        deleteUser();
        break;
    case 'listSubcategories':
        listSubcategories();
        break;
    case 'newSubcategory':
        newSubcategory();
        break;
    case 'editSubcategory':
        editSubcategory();
        break;
    case 'deleteSubcategory':
        deleteSubcategory();
        break;
    default:
        listArticles();
}

/**
 * Авторизация пользователя (админа) -- установка значения в сессию
 */
function login() {

    $results = array();
    $results['pageTitle'] = "Admin Login | Widget News";

    if (isset($_POST['login'])) {

        // Пользователь получает форму входа: попытка авторизировать пользователя
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // 1. Проверка на администратора
        if ($username == ADMIN_USERNAME && $password == ADMIN_PASSWORD) {
            // Вход как администратор прошел успешно
            $_SESSION['username'] = ADMIN_USERNAME;
            $_SESSION['role'] = 'admin';
            header("Location: admin.php");
            return;
        }

        // 2. Проверка пользователя в базе данных
        try {
            $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
            $sql = "SELECT * FROM users WHERE username = :username AND password = :password AND activity = 1";
            $st = $conn->prepare($sql);
            $st->bindValue(":username", $username, PDO::PARAM_STR);
            $st->bindValue(":password", $password, PDO::PARAM_STR);
            $st->execute();
            $user = $st->fetch(PDO::FETCH_ASSOC);
            $conn = null;

            if ($user) {
                // Пользователь найден в БД и активен
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = 'admin';
                header("Location: admin.php");
                return;
            } else {
                // Пользователь не найден или неактивен
                $results['errorMessage'] = "Неправильное имя пользователя или пароль, либо пользователь деактивирован.";
            }

        } catch (PDOException $e) {
            $results['errorMessage'] = "Ошибка базы данных: " . $e->getMessage();
        }

        // Если дошли сюда - ошибка авторизации
        require(TEMPLATE_PATH . "/admin/loginForm.php");

    } else {
        // Пользователь еще не получил форму: выводим форму
        require(TEMPLATE_PATH . "/admin/loginForm.php");
    }

}


function logout() {
    unset( $_SESSION['username'] );
    header( "Location: admin.php" );
}


function newArticle() {
    $results = array();
    $results['pageTitle'] = "New Article";
    $results['formAction'] = "newArticle";

    if ( isset( $_POST['saveChanges'] ) ) {
        // Проверяем соответствие категории и подкатегории
        if (isset($_POST['subcategory_id']) && $_POST['subcategory_id'] != '' && 
            isset($_POST['categoryId']) && $_POST['categoryId'] != '') {
            $subcategory = Subcategory::getById((int)$_POST['subcategory_id']);
            if ($subcategory && $subcategory->category_id != (int)$_POST['categoryId']) {
                $results['errorMessage'] = "Selected subcategory does not belong to the selected category.";
                $results['article'] = new Article($_POST);
                $data = Category::getList();
                $results['categories'] = $data['results'];
                $results['subcategories'] = Subcategory::getList()['results'];
                require( TEMPLATE_PATH . "/admin/editArticle.php" );
                return;
            }
        }

        $article = new Article();
        $article->storeFormValues( $_POST );
        $article->insert();
        header( "Location: admin.php?status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {
        header( "Location: admin.php" );
    } else {
        $results['article'] = new Article;
        $data = Category::getList();
        $results['categories'] = $data['results'];
        $results['subcategories'] = Subcategory::getList()['results'];
        $results['users'] = User::getList()['results'];
        require( TEMPLATE_PATH . "/admin/editArticle.php" );
    }
}


/**
 * Редактирование статьи
 * 
 * @return null
 */
function editArticle() {
    $results = array();
    $results['pageTitle'] = "Edit Article";
    $results['formAction'] = "editArticle";

    if (isset($_POST['saveChanges'])) {
        if (isset($_POST['subcategory_id']) && $_POST['subcategory_id'] != '' && 
            isset($_POST['categoryId']) && $_POST['categoryId'] != '') {
            $subcategory = Subcategory::getById((int)$_POST['subcategory_id']);
            if ($subcategory && $subcategory->category_id != (int)$_POST['categoryId']) {
                $results['errorMessage'] = "Selected subcategory does not belong to the selected category.";
                $results['article'] = new Article($_POST);
                $data = Category::getList();
                $results['categories'] = $data['results'];
                $results['subcategories'] = Subcategory::getList()['results'];
                require(TEMPLATE_PATH . "/admin/editArticle.php");
                return;
            }
        }

        if ( !$article = Article::getById( (int)$_POST['articleId'] ) ) {
            header( "Location: admin.php?error=articleNotFound" );
            return;
        }

        $article->storeFormValues( $_POST );
        $article->update();
        header( "Location: admin.php?status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {
        header( "Location: admin.php" );
    } else {
        $results['article'] = Article::getById((int)$_GET['articleId']);
        $data = Category::getList();
        $results['categories'] = $data['results'];
        $results['subcategories'] = Subcategory::getList()['results'];
        $results['users'] = User::getList()['results'];
        require(TEMPLATE_PATH . "/admin/editArticle.php");
    }
}


function deleteArticle() {

    if ( !$article = Article::getById( (int)$_GET['articleId'] ) ) {
        header( "Location: admin.php?error=articleNotFound" );
        return;
    }

    $article->delete();
    header( "Location: admin.php?status=articleDeleted" );
}


function listArticles() {
    $results = array();
    
    $data = Article::getList();
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    
    $data = Category::getList();
    $results['categories'] = array();
    foreach ($data['results'] as $category) { 
        $results['categories'][$category->id] = $category;
    }
    
    $results['pageTitle'] = "Все статьи";

    if (isset($_GET['error'])) { // вывод сообщения об ошибке (если есть)
        if ($_GET['error'] == "articleNotFound") 
            $results['errorMessage'] = "Error: Article not found.";
    }

    if (isset($_GET['status'])) { // вывод сообщения (если есть)
        if ($_GET['status'] == "changesSaved") {
            $results['statusMessage'] = "Your changes have been saved.";
        }
        if ($_GET['status'] == "articleDeleted")  {
            $results['statusMessage'] = "Article deleted.";
        }
    }

    require(TEMPLATE_PATH . "/admin/listArticles.php" );
}

function listCategories() {
    $results = array();
    $data = Category::getList();
    $results['categories'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    $results['pageTitle'] = "Article Categories";

    if ( isset( $_GET['error'] ) ) {
        if ( $_GET['error'] == "categoryNotFound" ) $results['errorMessage'] = "Error: Category not found.";
        if ( $_GET['error'] == "categoryContainsArticles" ) $results['errorMessage'] = "Error: Category contains articles. Delete the articles, or assign them to another category, before deleting this category.";
    }

    if ( isset( $_GET['status'] ) ) {
        if ( $_GET['status'] == "changesSaved" ) $results['statusMessage'] = "Your changes have been saved.";
        if ( $_GET['status'] == "categoryDeleted" ) $results['statusMessage'] = "Category deleted.";
    }

    require( TEMPLATE_PATH . "/admin/listCategories.php" );
}
	  
	  
function newCategory() {

    $results = array();
    $results['pageTitle'] = "New Article Category";
    $results['formAction'] = "newCategory";

    if ( isset( $_POST['saveChanges'] ) ) {

        // User has posted the category edit form: save the new category
        $category = new Category;
        $category->storeFormValues( $_POST );
        $category->insert();
        header( "Location: admin.php?action=listCategories&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // User has cancelled their edits: return to the category list
        header( "Location: admin.php?action=listCategories" );
    } else {

        // User has not posted the category edit form yet: display the form
        $results['category'] = new Category;
        require( TEMPLATE_PATH . "/admin/editCategory.php" );
    }

}


function editCategory() {

    $results = array();
    $results['pageTitle'] = "Edit Article Category";
    $results['formAction'] = "editCategory";

    if ( isset( $_POST['saveChanges'] ) ) {

        // User has posted the category edit form: save the category changes

        if ( !$category = Category::getById( (int)$_POST['categoryId'] ) ) {
          header( "Location: admin.php?action=listCategories&error=categoryNotFound" );
          return;
        }

        $category->storeFormValues( $_POST );
        $category->update();
        header( "Location: admin.php?action=listCategories&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // User has cancelled their edits: return to the category list
        header( "Location: admin.php?action=listCategories" );
    } else {

        // User has not posted the category edit form yet: display the form
        $results['category'] = Category::getById( (int)$_GET['categoryId'] );
        require( TEMPLATE_PATH . "/admin/editCategory.php" );
    }

}


function deleteCategory() {

    if ( !$category = Category::getById( (int)$_GET['categoryId'] ) ) {
        header( "Location: admin.php?action=listCategories&error=categoryNotFound" );
        return;
    }

    $articles = Article::getList( 1000000, $category->id );

    if ( $articles['totalRows'] > 0 ) {
        header( "Location: admin.php?action=listCategories&error=categoryContainsArticles" );
        return;
    }

    $category->delete();
    header( "Location: admin.php?action=listCategories&status=categoryDeleted" );
}

function listUsers() {
    $results = array();
    $data = User::getList();
    $results['users'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    $results['pageTitle'] = "Users";

    if ( isset( $_GET['error'] ) ) {
        if ( $_GET['error'] == "userNotFound" ) $results['errorMessage'] = "Error: User not found.";
    }

    if ( isset( $_GET['status'] ) ) {
        if ( $_GET['status'] == "changesSaved" ) $results['statusMessage'] = "Your changes have been saved.";
        if ( $_GET['status'] == "userDeleted" ) $results['statusMessage'] = "User deleted.";
    }

    require( TEMPLATE_PATH . "/admin/listUsers.php" );
}

function newUser() {
    $results = array();
    $results['pageTitle'] = "New User";
    $results['formAction'] = "newUser";

    if ( isset( $_POST['saveChanges'] ) ) {
        // User has posted the user edit form: save the new user
        $user = new User;
        $user->storeFormValues( $_POST );
        $user->insert();
        header( "Location: admin.php?action=listUsers&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {
        // User has cancelled their edits: return to the user list
        header( "Location: admin.php?action=listUsers" );
    } else {
        // User has not posted the user edit form yet: display the form
        $results['user'] = new User;
        require( TEMPLATE_PATH . "/admin/editUser.php" );
    }
}

function editUser() {
    $results = array();
    $results['pageTitle'] = "Edit User";
    $results['formAction'] = "editUser";

    if ( isset( $_POST['saveChanges'] ) ) {
        // User has posted the user edit form: save the user changes
        if ( !$user = User::getById( (int)$_POST['userId'] ) ) {
          header( "Location: admin.php?action=listUsers&error=userNotFound" );
          return;
        }

        $user->storeFormValues( $_POST );
        $user->update();
        header( "Location: admin.php?action=listUsers&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {
        // User has cancelled their edits: return to the user list
        header( "Location: admin.php?action=listUsers" );
    } else {
        // User has not posted the user edit form yet: display the form
        $results['user'] = User::getById( (int)$_GET['userId'] );
        require( TEMPLATE_PATH . "/admin/editUser.php" );
    }
}

function deleteUser() {
    if ( !$user = User::getById( (int)$_GET['userId'] ) ) {
        header( "Location: admin.php?action=listUsers&error=userNotFound" );
        return;
    }

    $user->delete();
    header( "Location: admin.php?action=listUsers&status=userDeleted" );
}        
function listSubcategories() {
    $results = array();
    $data = Subcategory::getList();
    $results['subcategories'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    $results['pageTitle'] = "Article Subcategories";

    if ( isset( $_GET['error'] ) ) {
        if ( $_GET['error'] == "subcategoryNotFound" ) $results['errorMessage'] = "Error: Subcategory not found.";
        if ( $_GET['error'] == "subcategoryContainsArticles" ) $results['errorMessage'] = "Error: Subcategory contains articles. Delete the articles, or assign them to another subcategory, before deleting this subcategory.";
    }

    if ( isset( $_GET['status'] ) ) {
        if ( $_GET['status'] == "changesSaved" ) $results['statusMessage'] = "Your changes have been saved.";
        if ( $_GET['status'] == "subcategoryDeleted" ) $results['statusMessage'] = "Subcategory deleted.";
    }

    require( TEMPLATE_PATH . "/admin/listSubcategories.php" );
}
	  
function newSubcategory() {

    $results = array();
    $results['pageTitle'] = "New Article Subcategory";
    $results['formAction'] = "newSubcategory";

    if ( isset( $_POST['saveChanges'] ) ) {

        // User has posted the subcategory edit form: save the new subcategory
        $subcategory = new Subcategory;
        $subcategory->storeFormValues( $_POST );
        $subcategory->insert();
        header( "Location: admin.php?action=listSubcategories&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // User has cancelled their edits: return to the subcategory list
        header( "Location: admin.php?action=listSubcategories" );
    } else {

        // User has not posted the subcategory edit form yet: display the form
        $results['subcategory'] = new Subcategory;
        $data = Category::getList();
        $results['categories'] = $data['results'];
        require( TEMPLATE_PATH . "/admin/editSubcategory.php" );
    }

}

function editSubcategory() {

    $results = array();
    $results['pageTitle'] = "Edit Article Subcategory";
    $results['formAction'] = "editSubcategory";
    if(true){
        require( TEMPLATE_PATH . "/admin/listSubcategories.php" );
    }
    if ( isset( $_POST['saveChanges'] ) ) {

        // User has posted the subcategory edit form: save the subcategory changes

        if ( !$subcategory = Subcategory::getById( (int)$_POST['subcategoryId'] ) ) {
          header( "Location: admin.php?action=listSubcategories&error=subcategoryNotFound" );
          return;
        }

        $subcategory->storeFormValues( $_POST );
        $subcategory->update();
        header( "Location: admin.php?action=listSubcategories&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // User has cancelled their edits: return to the subcategory list
        header( "Location: admin.php?action=listSubcategories" );
    } else {

        // User has not posted the subcategory edit form yet: display the form
        $results['subcategory'] = Subcategory::getById( (int)$_GET['subcategoryId'] );
        $data = Category::getList();
        $results['categories'] = $data['results'];
        require( TEMPLATE_PATH . "/admin/editSubcategory.php" );
    }

}


function deleteSubcategory() {

    if ( !$subcategory = Subcategory::getById( (int)$_GET['subcategoryId'] ) ) {
        header( "Location: admin.php?action=listSubcategories&error=subcategoryNotFound" );
        return;
    }

    // Проверяем, есть ли статьи в этой подкатегории
    $articles = Article::getListBySubcategory( $subcategory->id );

    if ( $articles['totalRows'] > 0 ) {
        header( "Location: admin.php?action=listSubcategories&error=subcategoryContainsArticles" );
        return;
    }

    $subcategory->delete();
    header( "Location: admin.php?action=listSubcategories&status=subcategoryDeleted" );
}