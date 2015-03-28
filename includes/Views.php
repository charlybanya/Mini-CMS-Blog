<?php

/**
 * Description of Views
 *
 * @author Cristopher Mendoza
 */
session_start();
require 'Database.php';

class Views {

    static function loadView($requestArray, $blogData) {

        switch ($requestArray[0]) {
            case 'autor':
                if (isset($requestArray[1])) {
                    print self::createAuthorDetailView($requestArray[1], $blogData);
                } else {
                    header("Location: /");
                }
                break;

            case 'categoria':
                if (isset($requestArray[1])) {
                    print self::createCategoryView($requestArray[1], $blogData);
                } else {
                    header("Location: /");
                }
                break;

            case 'new':
                if ($_SESSION['sessionActive'] == TRUE) {
                    print self::createEditorView($blogData);
                } else {
                    header("Location: /login");
                }
                break;
            case 'login':
                if ($_SESSION['sessionActive'] == TRUE) {
                    header('Location: /new');
                } else {
                    print self::createLoginView();
                }
                break;
                
            case 'logout' :
                if ($_SESSION['sessionActive'] == TRUE) {
                    session_destroy();
                    header("Location: /");
                } else {
                   header("Location: /login");
                }
                break;

            case 'publish':
                if ($_SESSION['sessionActive'] && $_POST['title'] != '') {
                    self::createPost();
                } else {
                    header('Location: /new');
                }
                break;

            case '':
                print self::createIndexView($blogData);
                break;


            default:
                if (self::searchPost($requestArray[0])) {
                    print self::createPostDetailView($requestArray[0], $blogData);
                } else {
                    header("Location: / ");
                }
                break;
        }
    }

    static function createIndexView($blogData) {
        $body = file_get_contents('Templates/index.html');
        $body = str_replace('%content', self::getIndexContent(), $body);
        $body = str_replace('%subtitle', $blogData['subtitle'], $body);

        if ($_SESSION['sessionActive'] == TRUE) {
            $body = str_replace('%sesion', '/logout', $body);
            $body = str_replace('%accion', 'Cerrar', $body);
        } else {
            $body = str_replace('%sesion', '/login', $body);
            $body = str_replace('%accion', 'Iniciar', $body);
        }

        $view = str_replace('%title', $blogData['title'], $body);
        return $view;
    }

    static function createPostDetailView($slug, $blogData) {
        $body = file_get_contents('Templates/post.html');
        $articles = new Database('Articles');
        $authors = new Database('Authors');
        $articleData = $articles->getAllDataByField('slug', $slug);
        $authorData = $authors->getDataById(array('authorName', 'idAuthors'), $articleData[0]['idAuthors']);
        $cambiar = array('%authorName', '%date', '%text', '%idAuthors');
        $por = array($authorData['authorName'], $articleData[0]['date'], $articleData[0]['text'], $authorData['idAuthors']);
        $body = str_replace($cambiar, $por, $body);

        if ($_SESSION['sessionActive'] == TRUE) {
            $body = str_replace('%sesion', '/logout', $body);
            $body = str_replace('%accion', 'Cerrar', $body);
        } else {
            $body = str_replace('%sesion', '/login', $body);
            $body = str_replace('%accion', 'Iniciar', $body);
        }

        $view = str_replace('%title', $blogData['title'], $body);
        return $view;
    }

    static function createAuthorDetailView($id, $blogData) {
        $body = file_get_contents('Templates/author.html');
        $author = new Database('Authors');
        $body = str_replace('%content', self::getAuthorContent('Authors', $id), $body);
        $fields = array('authorName', 'authorBio');
        $data = $author->getDataById($fields, $id);
        $body = str_replace(array('%authorName', '%authorBio'), array($data[0], $data[1]), $body);
        if ($_SESSION['sessionActive'] == TRUE) {
            $body = str_replace('%sesion', '/logout', $body);
            $body = str_replace('%accion', 'Cerrar', $body);
        } else {
            $body = str_replace('%sesion', '/login', $body);
            $body = str_replace('%accion', 'Iniciar', $body);
        }

        $view = str_replace('%title', $blogData['title'], $body);
        return $view;
    }

    static function createEditorView($blogData) {
        $body = file_get_contents('Templates/new.html');
        if ($_SESSION['sessionActive'] == TRUE) {
            $body = str_replace('%sesion', '/logout', $body);
            $body = str_replace('%accion', 'Cerrar', $body);
        } else {
            $body = str_replace('%sesion', '/login', $body);
            $body = str_replace('%accion', 'Iniciar', $body);
        }
        $view = str_replace('%title', $blogData['title'], $body);
        return $view;
    }

    static function createCategoryView($categoryName, $blogData) {
        $body = file_get_contents('Templates/category.html');
        $body = str_replace('%content', self::getCategoryContent($categoryName), $body);
        $body = str_replace('%name', $categoryName, $body);

        if ($_SESSION['sessionActive'] == TRUE) {
            $body = str_replace('%sesion', '/logout', $body);
            $body = str_replace('%accion', 'Cerrar', $body);
        } else {
            $body = str_replace('%sesion', '/login', $body);
            $body = str_replace('%accion', 'Iniciar', $body);
        }

        $view = str_replace('%title', $blogData['title'], $body);
        return $view;
    }

    static function createLoginView() {
        $authors = new Database('Authors');
        $body = file_get_contents('Templates/login.html');

        if ($_POST['boton'] == 'Iniciar Sesión') {
            $mail = filter_var($_POST['mail'], FILTER_SANITIZE_EMAIL, FILTER_SANITIZE_MAGIC_QUOTES);
            $authorData = $authors->getAllDataByField('mail', $mail);
            if ($authorData[0]['pass'] == md5($_POST['pass'])) {
                $_SESSION['sessionActive'] = TRUE;
                $_SESSION['userMail'] = $mail;
                header('Location: /new');
            } else {
                $body = str_replace('%error', 'Verifique sus datos, Usuario o Contraseña erroneos', $body);
                return $body;
            }
        } else {
            $body = str_replace('%error', '', $body);
            return $body;
        }
    }

    static function searchPost($slug) {
        $articles = new Database('Articles');
        $article = $articles->getAllDataByField('slug', $slug);
        return $article;
    }

    static function getAuthorContent($viewType, $id) {
        $previewPost = '<div class="post-preview">
                        <a href="/%slug">
                        <h2 class="post-title">%title</h2>
                        <h3 class="post-subtitle">%text</h3>
                        </a>
                        <p class="post-meta">Publicado por <a href="/autor/%idAuthors">%authorName</a> el %date</p>
                        </div>
                        <hr>';
        $articles = new Database('Articles');
        $field = 'id' . $viewType;
        $articlesData = $articles->getAllDataByField($field, $id);
        $content = '';
        foreach ($articlesData as $key => $value) {
            $cambiar = array('%slug', '%title', '%text', '%idAuthors', '%date');
            $por = array($value['slug'], $value['title'], substr($value['text'], 0, 139) . '...', $value['idAuthors'], $value['date']);
            $content .= str_replace($cambiar, $por, $previewPost) . "\n";
        }
        return $content;
    }

    static function getCategoryContent($categoryName) {
        $previewPost = '<div class="post-preview">
                        <a href="/%slug">
                        <h2 class="post-title">%title</h2>
                        <h3 class="post-subtitle">%text</h3>
                        </a>
                        <p class="post-meta">Publicado por <a href="/autor/%idAuthors">%authorName</a> el %date</p>
                        </div>
                        <hr>';
        $categories = new Database('Categories');
        $categoriesArticles = new Database('ArticleCategories');
        $articles = new Database('Articles');
        $author = new Database('Authors');
        $categoryID = $categories->getAllDataByField('name', strtolower($categoryName));
        $categoryArticlesIds = $categoriesArticles->getAllDataByField('idCategories', $categoryID[0]['idCategories']);
        $content = '';
        foreach ($categoryArticlesIds as $key => $value) {
            $articlesData = $articles->getAllDataByField('idArticles', $categoryArticlesIds[$key]['idArticles']);
            foreach ($articlesData as $key => $value) {
                $fields = array('authorName');
                $data = $author->getDataById($fields, $value['idAuthors']);
                $cambiar = array('%slug', '%title', '%text', '%idAuthors', '%date', '%authorName');
                $por = array($value['slug'], $value['title'], substr($value['text'], 0, 139) . '...', $value['idAuthors'], $value['date'], $data[0]);
                $content .= str_replace($cambiar, $por, $previewPost) . "\n";
            }
        }
        return $content;
    }

    static function getIndexContent() {
        $previewPost = '<div class="post-preview">
                        <a href="/%slug">
                        <h2 class="post-title">%title</h2>
                        <h3 class="post-subtitle">%text</h3>
                        </a>
                        <p class="post-meta">Publicado por <a href="/autor/%idAuthors">%authorName</a> el %date</p>
                        </div>
                        <hr>';
        $categories = new Database('Categories');
        $categoriesArticles = new Database('ArticleCategories');
        $articles = new Database('Articles');
        $author = new Database('Authors');
        $categoryArticlesIds = $categoriesArticles->getAllData();
        $content = '';
        foreach ($categoryArticlesIds as $key => $value) {
            $articlesData = $articles->getAllDataByField('idArticles', $categoryArticlesIds[$key]['idArticles']);
            foreach ($articlesData as $key => $value) {
                $fields = array('authorName');
                $data = $author->getDataById($fields, $value['idAuthors']);
                $cambiar = array('%slug', '%title', '%text', '%idAuthors', '%date', '%authorName');
                $por = array($value['slug'], $value['title'], substr($value['text'], 0, 139) . '...', $value['idAuthors'], $value['date'], $data[0]);
                $content .= str_replace($cambiar, $por, $previewPost) . "\n";
            }
        }
        return $content;
    }

    static function createPost() {
        $inputClean = filter_var_array($_POST, FILTER_SANITIZE_MAGIC_QUOTES);
        $authors = new Database('Authors');
        $categories = new Database('Categories');
        $articleCategories = new Database('ArticleCategories');
        $articles = new Database('Articles');
        $idAuthors = $authors->getAllDataByField('mail', $_SESSION['userMail']);
        $articleData = array(
            'idArticles' => NULL,
            'title' => utf8_encode($inputClean['title']),
            'text' => utf8_encode($inputClean['text']),
            'idAuthors' => $idAuthors[0]['idAuthors'],
            'date' => date('Y-m-d'),
            'slug' => substr(preg_replace('/[^A-Za-z0-9-]+/', '-', $inputClean['title']), 0, 9)
        );

        $articlesId = $articles->save($articleData);

        $postCategories = explode(',', $inputClean['categories']);
        $postCategories = array_map('trim', $postCategories);

        foreach ($postCategories as $value) {
            $categoriesData = array(
                'idCategories' => NULL,
                'name' => $value
            );
            $categories->save($categoriesData);

            $categoriesId = $categories->getAllDataByField('name', $value);
            $articleCategoriesData = array(
                'idArticleCategories' => NULL,
                'idArticles' => $articlesId,
                'idCategories' => $categoriesId[0]['idCategories']
            );

            $articleCategories->save($articleCategoriesData);
        }

        $slugFix = array(
            'slug' => $articleData['slug'] . '-' . $articlesId
        );

        $articles->update($slugFix, 'idArticles', $articlesId);

        header('Location: ' . $articleData['slug'] . '-' . $articlesId);
    }

}
