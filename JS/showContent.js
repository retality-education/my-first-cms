$(function(){
    
    console.log('Привет, это страый js ))');
    init_get();
    init_post();
});

function init_get() 
{
    $('a.ajaxArticleBodyByGet').one('click', function(){
        var contentId = $(this).attr('data-contentId');
        console.log('ID статьи = ', contentId); 
        showLoaderIdentity();
        $.ajax({
            url:'/ajax/showContentsHandler.php?articleId=' + contentId, 
            dataType: 'json'
        })
        .done (function(response){
            hideLoaderIdentity();
            console.log('Ответ получен', response);
            if (response.success) {
                var fullContent = '<div>' + response.content + '</div>';
                $('li.' + contentId).append(fullContent);
            } else {
                alert('Ошибка: ' + response.error);
        }})
        .fail(function(xhr, status, error){
            hideLoaderIdentity();
    
            console.log('ajaxError xhr:', xhr); // выводим значения переменных
            console.log('ajaxError status:', status);
            console.log('ajaxError error:', error);
    
            console.log('Ошибка соединения при получении данных (GET)');
        });
        
        return false;
        
    });  
}

function init_post() 
{
    $('a.ajaxArticleBodyByPost').one('click', function(){
        var contentId = $(this).attr('data-contentId');
        console.log('POST - ID статьи = ', contentId);
        showLoaderIdentity();
        $.ajax({
            url: '/ajax/showContentsHandler.php', 
            method: 'POST',
            data: JSON.stringify({ articleId: contentId }),
            contentType: 'application/json',
            dataType: 'json'
        })
        .done (function(response){
            hideLoaderIdentity();
            console.log('POST - ID статьи = ');
            console.log('Ответ получен', response);
            if (response.success) {
                var fullContent = '<div>' + response.content + '</div>';
                $('li.' + contentId).append(fullContent);
            } else {
                alert('Ошибка: ' + response.error);
        }})
        .fail(function(xhr, status, error){
            hideLoaderIdentity();
    
    
            console.log('Ошибка соединения с сервером (POST)');
            console.log('ajaxError xhr:', xhr); // выводим значения переменных
            console.log('ajaxError status:', status);
            console.log('ajaxError error:', error);
        });
        
        return false;
        
    });  
}
