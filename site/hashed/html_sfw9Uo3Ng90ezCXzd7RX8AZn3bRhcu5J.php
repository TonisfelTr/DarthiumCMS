
<?php foreach (\Forum\ForumAgent::GetCategoryList(true) as $category) { ?>

      <li><a href="<?= \Engine\RouteAgent::buildRoute('category-topics', [$category['id']]) ?>"><?= $category['name'] ?></a></li>  <?php } ?>