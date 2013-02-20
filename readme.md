Phrame2 alpha2
==============

Phrame is a simple PHP-MVC-framework. This means it divides an application into three parts:

- **Controllers** handle user input and patches data from the model to the view.
- **Models** allow abstracted access to the database and perform validity checks, conversion, sanatation and so on
- **Views** provide the user interface.

<h2 id="Controllers">Controllers</h2>
Controllers are defined as classes. There methods are called actions.

    class Page_Controller extends Application_Controller {
      var $title;
    
      function index () {
        $this->title = 'Hallo Welt';
        render();
      }
    }

Per default, the `index`-function is called if the user accesses /index.php/page/ (see [Routing](#Routing))
For `render`, see [Views](#Views).

<h2 id="Models">Models</h2>
Models are also defined as classes. This class describes the layout of the table, and function for accessing data.

    class Page extends Model {
      static $table_name = 'pages';
      static $index = array('name');
      static $struct = array(
          'name'        => 'string',
          'content'     => 'text',
          'updatedAt'   => 'timestamp',
          'createdAt'   => 'timestamp'
      );
    }

Note here that the ID field is automaticly generated.
There are several default methods for accessing Models:

    $page = Page::find(1);  // Finds page with id = 1
    $page['name'] = 'Testseite';
    $page.save();    // Saves pages to database
    $page.delete();  // Removes page from database


<h2 id="Views">Views</h2>
Views are simple php files. (As php already is its on templating-engine)
There are multiple layers of views (Using the page controller example):

- `views/layout/global.php` wraps every view
- `views/layout/page.php` wraps all views for the page controller
- `views/page/index.php` contains the view for `Page_Controllers` `index` method

If the layout pages don't exist, they will be skiped

Higher level views are able to access lower level view using the `$yield` variable (This is adopted from
Rails, we will most certainly change this)

A `views/layout/global.php` could look like this:

    <html>
      <head>
        <title><?php echo $this->title ?></title>
      </head>
      <body>
        <?php echo $yield ?>
      </body>
    </html>
    
A `views/page/index` could look like this:

    List of Pages:
    <?php while ($page = Page.each()) { ?>
    <p>
      <a href="/index.php/page/<?php echo $page['ID'] ?>"><?php echo $page['name'] ?></a>
    </p>
    <?php } ?>

Inside the Controller, we can use the `render` method.

`render()` inside the `index` method automaticly sends the index-view to the user.

`render('show')` sends the show-view to the user, regardless of the action you are in.

<h2 id="Routing">Routing</h2>
Routing is right now done by the function `phrame_routing` in `/config/routing.php`. It returns an
hash the attributes `controller`, `action` and additional attributes extracted from the path (for example
`id`)

The default routing looks like this:

<table>
  <tr><th>HTTP Method</th> <th>Path</th>                  <th>Action Called</th></tr>
  <tr><td>GET</td>         <td>/controller/</td>          <td>controller_Controller#index</td></tr>
  <tr><td>POST</td>        <td>/controller/</td>          <td>controller_Controller#create</td></tr>
  <tr><td>GET</td>         <td>/controller/new</td>       <td>controller_Controller#new</td></tr>
  <tr><td>GET</td>         <td>/controller/id</td>        <td>controller_Controller#view</td></tr>
  <tr><td>POST</td>        <td>/controller/id</td>        <td>controller_Controller#update</td></tr>
  <tr><td>DELETE</td>      <td>/controller/id</td>        <td>controller_Controller#destroy</td></tr>
  <tr><td>any</td>         <td>/controller/id/action</td> <td>controller_Controller#action</td></tr>
</table>

(`controller_Controller` means `controller` from URL with `_Controller` appended, e.g. Page_Controller )

<h2 id="Tools">Tools</h2>
Phrame contains to admin tools in /phrame/tool

- db_up.php creates the database and tables
- db_down.php destroys the database

NOTE: Make sure to delete this folder in a production enviroment (Or at least passwort protect it using .htaccess)

<h2 id="Security">Security</h2>
Phrame has several features aim to make your application more secure

- All standart folders, except /phrame are hidden using `.htaccess`
- The database adapter automaticly sanatizes the input to prevent SQL-Injections
- The public folder uses `.htaccess` to delete the php file handler. That way, uploaded files cannot be executed
- Protection against CSRF

This features are TODO:

- A better way of sanatizing HTML-Input then `htmlentities`