<?php include "templates/include/header.php" ?>

      <h1><?php echo htmlspecialchars( $results['article']->title ?? '' )?></h1>
      
      <div class="article-meta">
          <span class="pubDate">Publication date: <?php echo date('j F Y', $results['article']->publicationDate)?></span>
          
          <?php if ( isset($results['article']->categoryId) && $results['article']->categoryId && isset($results['categories'][$results['article']->categoryId]) ) { ?>
            <span class="category">
                | Category: 
                <a href=".?action=archive&amp;categoryId=<?php echo $results['article']->categoryId?>">
                    <?php echo htmlspecialchars( $results['categories'][$results['article']->categoryId]->name ?? '' )?>
                </a>
            </span>
            
            <?php if ( isset($results['article']->subcategory_id) && $results['article']->subcategory_id ) { ?>
                <span class="subcategory">
                    | Subcategory: 
                    <a href=".?action=archiveBySubcategory&amp;subcategoryId=<?php echo $results['article']->subcategory_id?>">
                        <?php 
                        $subcategory = Subcategory::getById($results['article']->subcategory_id);
                        echo $subcategory ? htmlspecialchars($subcategory->name) : 'Unknown';
                        ?>
                    </a>
                </span>
            <?php } ?>
          <?php } 
          else { ?>
            <span class="category">| Без категории</span>
          <?php } ?>
          
          <?php 
          $authors = Article::getArticleAuthors($results['article']->id);
          if (!empty($authors)) { 
              $authorNames = array();
              foreach ($authors as $author) {
                  $authorNames[] = $author->username;
              }
          ?>
            <span class="authors">| Authors: <?php echo htmlspecialchars(implode(', ', $authorNames)); ?></span>
          <?php } ?>
      </div>
      
      <div class="summary"><?php echo htmlspecialchars( $results['article']->summary ?? '' )?></div>
      <div class="content"><?php echo $results['article']->content ?? '' ?></div>

      <p><a href="./">Return to Homepage</a></p>

<?php include "templates/include/footer.php" ?>