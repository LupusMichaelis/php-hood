<?php declare(strict_types=1);

if(!isset($this) && !($this instanceof \LupusMichaelis\PHPHood\Page))
	throw new \LupusMichaelis\PHPHood\TemplateContextError;

$app = $this;
$state = $this->getModel();
$page_list = $this->app->getPageList();

?>
<form action='?' method='POST'>
  <p>
<?php foreach($page_list as $page_id => $page_config): ?>
<?php   if(!in_array($page_id, $state['tab_list'])): ?>
    <label>
      <input type='radio'
             name='add-tab'
             value='<?= htmlentities($page_id) ?>'
             /><?= htmlentities($page_config['title']) ?></label>
<?php   endif ?>
<?php endforeach ?>
    <input type='submit' value='Add' />
  </p>
</form>
