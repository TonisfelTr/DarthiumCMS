
<?php $onlineUsers = \Users\UserAgent::Get10OnlineUsers(); ?>

<?php if(!empty($onlineUsers)) { ?>

      <ul>         <?php foreach ($onlineUsers as $user) { ?>            <li><a class="alert-link" href="<?= \Engine\RouteAgent::buildRoute('profile-page-defined', [$user['id']]) ?>"><?= $user['nickname'] ?></a></li>        <?php } ?>      </ul>    <?php } ?>    
<?php if(empty($onlineUsers)) { ?>

      Нет онлайн пользователей    <?php } ?><?php $onlineUsers = false;  ?>