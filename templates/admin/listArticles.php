<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>
	  
    <h1>All Articles</h1>

    <?php if ( isset( $results['errorMessage'] ) ) { ?>
            <div class="errorMessage"><?php echo $results['errorMessage'] ?></div>
    <?php } ?>

    <?php if ( isset( $results['statusMessage'] ) ) { ?>
            <div class="statusMessage"><?php echo $results['statusMessage'] ?></div>
    <?php } ?>

          <table>
            <tr>
              <th>Publication Date</th>
              <th>Article</th>
              <th>Category</th>
              <th>Subcategory</th>
              <th>Authors</th>
              <th>Activity</th>
            </tr>

    <?php foreach ( $results['articles'] as $article ) { 
        $authors = Article::getArticleAuthors($article->id);
        $authorNames = array();
        foreach ($authors as $author) {
            $authorNames[] = $author->username;
        }
    ?>

            <tr onclick="location='admin.php?action=editArticle&amp;articleId=<?php echo $article->id?>'">
              <td><?php echo date('j M Y', $article->publicationDate)?></td>
              <td>
                <?php echo $article->title?>
              </td>
              <td>
                <?php 
                if(isset($article->categoryId) && $article->categoryId && isset($results['categories'][$article->categoryId])) {
                    echo $results['categories'][$article->categoryId]->name;                        
                }
                else {
                    echo "Без категории";
                }?>
              </td>
              <td>
                <?php 
                if(isset($article->subcategory_id) && $article->subcategory_id) {
                    $subcategory = Subcategory::getById($article->subcategory_id);
                    echo $subcategory ? $subcategory->name : 'Unknown';
                }
                else {
                    echo "Без подкатегории";
                }?>
              </td>
              <td>
                <?php echo !empty($authorNames) ? implode(', ', $authorNames) : 'No authors'; ?>
              </td>
              <td>
                <?php echo $article->activity == 1 ? 'активен' : 'не активен'; ?>
              </td>
            </tr>

    <?php } ?>

          </table>

          <p><?php echo $results['totalRows']?> article<?php echo ( $results['totalRows'] != 1 ) ? 's' : '' ?> in total.</p>

          <p><a href="admin.php?action=newArticle">Add a New Article</a></p>

<?php include "templates/include/footer.php" ?>