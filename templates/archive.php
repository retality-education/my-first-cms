<?php include "templates/include/header.php" ?>
	  
    <h1><?php echo htmlspecialchars( $results['pageHeading'] ) ?></h1>
    
    <?php if ( $results['category'] ) { ?>
    <h3 class="categoryDescription"><?php echo htmlspecialchars( $results['category']->description ) ?></h3>
    <?php } ?>

    <ul id="headlines" class="archive">

    <?php foreach ( $results['articles'] as $article ) { 
        // Получаем авторов статьи
        $authors = Article::getArticleAuthors($article->id);
        $authorNames = array();
        foreach ($authors as $author) {
            $authorNames[] = $author->username;
        }
    ?>

            <li>
                <h2>
                    <span class="pubDate">
                        <?php echo date('j F Y', $article->publicationDate)?>
                    </span>
                    <a href=".?action=viewArticle&amp;articleId=<?php echo $article->id?>">
                        <?php echo htmlspecialchars( $article->title )?>
                    </a>
                </h2>
                
                <div class="article-meta">
                    <?php if ( !$results['category'] && isset($article->categoryId) && $article->categoryId && isset($results['categories'][$article->categoryId]) ) { ?>
                    <span class="category">
                        Category: 
                        <a href=".?action=archive&amp;categoryId=<?php echo $article->categoryId?>">
                            <?php echo htmlspecialchars( $results['categories'][$article->categoryId]->name ) ?>
                        </a>
                    </span>
                    
                    <?php if ( isset($article->subcategory_id) && $article->subcategory_id ) { ?>
                        <span class="subcategory">
                            | Subcategory: 
                            <a href=".?action=archiveBySubcategory&amp;subcategoryId=<?php echo $article->subcategory_id?>">
                                <?php 
                                $subcategory = Subcategory::getById($article->subcategory_id);
                                echo $subcategory ? htmlspecialchars($subcategory->name) : 'Unknown';
                                ?>
                            </a>
                        </span>
                    <?php } ?>
                    <?php } ?>
                    
                    <?php if (!empty($authorNames)) { ?>
                    <span class="authors">
                        | Authors: <?php echo htmlspecialchars(implode(', ', $authorNames)); ?>
                    </span>
                    <?php } ?>
                </div>
                
                <p class="summary"><?php echo htmlspecialchars( $article->summary )?></p>
            </li>

    <?php } ?>

    </ul>

    <p><?php echo $results['totalRows']?> article<?php echo ( $results['totalRows'] != 1 ) ? 's' : '' ?> in total.</p>

    <p><a href="./">Return to Homepage</a></p>
	  
<?php include "templates/include/footer.php" ?>