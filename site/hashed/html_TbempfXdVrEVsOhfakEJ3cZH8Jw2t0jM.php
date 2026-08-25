
<?php if(count(\Forum\ForumAgent::GetLastTopicsId()) > 0) { ?>

      <ul>           <?php foreach (\Forum\ForumAgent::GetLastTopicsId() as $topic) { ?>                <li><a class="alert-link" href="<?= \Engine\RouteAgent::buildRoute('topic-view', [$topic['id']]) ?>"><?= $topic['name'] ?></a></li>          <?php } ?>      </ul>  <?php } ?>  
<?php if(count(\Forum\ForumAgent::GetLastTopicsId()) <= 0) { ?>

      Список пуст :(  <?php } ?>