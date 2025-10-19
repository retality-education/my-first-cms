<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>

        <h1><?php echo $results['pageTitle']?></h1>

        <form action="admin.php?action=<?php echo $results['formAction']?>" method="post">
            <input type="hidden" name="articleId" value="<?php echo $results['article']->id ?>">

    <?php if ( isset( $results['errorMessage'] ) ) { ?>
            <div class="errorMessage"><?php echo $results['errorMessage'] ?></div>
    <?php } ?>

            <ul>

              <li>
                <label for="title">Article Title</label>
                <input type="text" name="title" id="title" placeholder="Name of the article" required autofocus maxlength="255" value="<?php echo htmlspecialchars( $results['article']->title ?? '' )?>" />
              </li>

              <li>
                <label for="summary">Article Summary</label>
                <textarea name="summary" id="summary" placeholder="Brief description of the article" required maxlength="1000" style="height: 5em;"><?php echo htmlspecialchars( $results['article']->summary ?? '' )?></textarea>
              </li>

              <li>
                <label for="content">Article Content</label>
                <textarea name="content" id="content" placeholder="The HTML content of the article" required maxlength="100000" style="height: 30em;"><?php echo htmlspecialchars( $results['article']->content ?? '' )?></textarea>
              </li>

              <li>
                <label for="categoryId">Article Category</label>
                <select name="categoryId" id="categoryId" onchange="updateSubcategories()">
                  <option value="0"<?php echo !$results['article']->categoryId ? " selected" : ""?>>(none)</option>
                <?php foreach ( $results['categories'] as $category ) { ?>
                  <option value="<?php echo $category->id?>"<?php echo ( $category->id == $results['article']->categoryId ) ? " selected" : ""?>><?php echo htmlspecialchars( $category->name )?></option>
                <?php } ?>
                </select>
              </li>

              <li>
                <label for="subcategory_id">Article Subcategory</label>
                <select name="subcategory_id" id="subcategory_id">
                  <option value="">(none)</option>
                  <?php 
                  // Группируем подкатегории по категориям
                  $groupedSubcategories = [];
                  foreach ($results['subcategories'] as $subcat) {
                      $groupedSubcategories[$subcat->category_id][] = $subcat;
                  }
                  
                  foreach ($groupedSubcategories as $categoryId => $subcats) {
                      $categoryName = '';
                      foreach ($results['categories'] as $cat) {
                          if ($cat->id == $categoryId) {
                              $categoryName = $cat->name;
                              break;
                          }
                      }
                      echo '<optgroup label="' . htmlspecialchars($categoryName) . '">';
                      foreach ($subcats as $subcat) {
                          $selected = (isset($results['article']->subcategory_id) && $results['article']->subcategory_id == $subcat->id) ? ' selected' : '';
                          echo '<option value="' . $subcat->id . '" data-category="' . $subcat->category_id . '"' . $selected . '>' . htmlspecialchars($subcat->name) . '</option>';
                      }
                      echo '</optgroup>';
                  }
                  ?>
                </select>
              </li>
              <li>
                <label for="authors">Article Authors</label>
                <select name="authors[]" id="authors" multiple="multiple" size="6" style="min-height: 120px;">
                <?php 
                // Загружаем текущих авторов статьи
                $currentAuthors = isset($results['article']->authors) ? $results['article']->authors : array();
                foreach ( $results['users'] as $user ) { 
                    $selected = in_array($user->id, $currentAuthors) ? ' selected="selected"' : '';
                ?>
                  <option value="<?php echo $user->id?>"<?php echo $selected?>><?php echo htmlspecialchars( $user->username )?></option>
                <?php } ?>
                </select>
                <div style="margin-top: 5px; font-size: 0.9em; color: #666;">
                    <strong>How to select multiple authors:</strong><br>
                    - Windows: Hold <kbd>Ctrl</kbd> and click authors<br>
                    - Mac: Hold <kbd>Cmd</kbd> and click authors<br>
                    - To select range: Click first author, hold <kbd>Shift</kbd>, click last author
                </div>
              </li>

              <li>
                <label for="publicationDate">Publication Date</label>
                <input type="date" name="publicationDate" id="publicationDate" placeholder="YYYY-MM-DD" required maxlength="10" value="<?php echo $results['article']->publicationDate ? date( "Y-m-d", $results['article']->publicationDate ) : "" ?>" />
              </li>

              <li>
                  <label for="activity">
                      <input type="hidden" name="activity" value="0">
                      <input type="checkbox" name="activity" id="activity" value="1" 
                          <?php echo (isset($results['article']->activity) && $results['article']->activity == 1) ? 'checked' : 'checked'; ?> 
                      />
                      Article is active (visible)
                  </label>
                  <small>If unchecked, the article will be hidden from the site</small>
              </li>

            </ul>

            <div class="buttons">
              <input type="submit" name="saveChanges" value="Save Changes" />
              <input type="submit" formnovalidate name="cancel" value="Cancel" />
            </div>

        </form>

    <?php if ($results['article']->id) { ?>
          <p><a href="admin.php?action=deleteArticle&amp;articleId=<?php echo $results['article']->id ?>" onclick="return confirm('Delete This Article?')">
                  Delete This Article
              </a>
          </p>
    <?php } ?>

<script>
function updateSubcategories() {
    const categorySelect = document.getElementById('categoryId');
    const subcategorySelect = document.getElementById('subcategory_id');
    const selectedCategoryId = categorySelect.value;
    
    // Показываем/скрываем подкатегории в зависимости от выбранной категории
    for (let i = 0; i < subcategorySelect.options.length; i++) {
        const option = subcategorySelect.options[i];
        if (option.value === '') {
            continue; // Пропускаем опцию "(none)"
        }
        
        const optionCategory = option.getAttribute('data-category');
        if (selectedCategoryId === '0' || optionCategory !== selectedCategoryId) {
            option.style.display = 'none';
            option.disabled = true;
        } else {
            option.style.display = 'block';
            option.disabled = false;
        }
    }
    
    // Также обновляем optgroup
    const optgroups = subcategorySelect.getElementsByTagName('optgroup');
    for (let i = 0; i < optgroups.length; i++) {
        const optgroup = optgroups[i];
        if (selectedCategoryId === '0' || optgroup.label !== document.querySelector('option[value="' + selectedCategoryId + '"]')?.textContent) {
            optgroup.style.display = 'none';
        } else {
            optgroup.style.display = 'block';
        }
    }
}

// Инициализируем при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    updateSubcategories();
});
</script>
	  
<?php include "templates/include/footer.php" ?>