<?php declare(strict_types=1);

if(!isset($this) && !($this instanceof \LupusMichaelis\PHPHood\Page))
	throw new \LupusMichaelis\PHPHood\TemplateContextError;

$app = $this;
$state = &$this->state;
$page_list = &$this->page_list;
$errors = $this->errors;

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Under the hood of <?= htmlentities($_SERVER['SERVER_NAME']) ?></title>
    <meta name='viewport' content='width=device-width' />
    <link rel='stylesheet'
          type='text/css'
          href='hood.css'
          defer
          />
    <script type='application/javascript'>
      'use strict';
      const state = <?= json_encode($state) ?>;
      window.addEventListener('load', () => { hood(state); });
    </script>
  </head>
  <body>
    <nav>
      <ol>
<?php foreach($page_list as $page_id => $page_config): ?>
        <li id='<?= htmlentities($page_id, ENT_QUOTES) ?>'
            class='handle
<?php   if($state['current_page'] === $page_id): ?>
                   selected
<?php   endif ?>
            '
            ><a href='?current=<?= htmlentities($page_id) ?>'>
              <i class='remover'>&cross;</i>
              <?= htmlentities($page_config['title']) ?>
<?php   if(!empty($page_config['feature_list'])): ?>
<?php     foreach($page_config['feature_list'] as $feature): ?>
<?php       if('reloader' === $feature): ?>
              <i class='reloader'>&#128472;</i>
<?php       endif ?>
<?php       if('inspector' === $feature): ?>
          <i class='inspector'>&neArr;</i>
<?php       endif ?>
<?php     endforeach ?>
<?php   endif ?>
          </a>
        </li>
<?php endforeach ?>
      </ol>
    </nav>

<?php foreach($page_list as $page_id => $page_config): ?>
    <iframe src='?page=<?= htmlentities($page_id, ENT_QUOTES) ?>'
<?php   if(@$$state['current_page'] !== $page_id): ?>
            class='hidden'
<?php   endif ?>
            ></iframe>
<?php endforeach ?>

    <div class='hidden'>
      <script defer
              type='application/javascript'
              src='hood.js'
              ></script>
    </div>
<?php if(count($errors)): ?>
    <div class='error'>
      <span>Errors occurred:</span>
<?php   foreach($errors as $error): ?>
      <ul>
        <li><?= htmlentities($error) ?></li>
      </ul>
    </div>
<?php   endforeach ?>
<?php endif ?>
  </body>
</html>
