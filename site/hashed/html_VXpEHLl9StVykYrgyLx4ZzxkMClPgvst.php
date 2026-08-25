
<?php foreach (array_merge(\Builder\Controllers\NavbarAgent::GetElements(), \Engine\PluginAgent::GetNavbarBtns()) as $navbtnbb) { ?>

<?php if(isset($navbtnbb['visibility'])) { ?>

<?php if(($navbtnbb['visibility'] == 'only_authorized' && \Users\UserAgent::isAuthorized() === false) ||                         $navbtnbb['visibility'] == 'only_guest' && \Users\UserAgent::isAuthorized() !== false) { ?>

              <?php continue(1); ?>          <?php } ?><?php } ?>      
<?php if($navbtnbb['type'] == 'nav-btn') { ?>

          <li><a href="<?= $navbtnbb['action'] ?>"><?= $navbtnbb['content'] ?></a></li>      <?php } ?>      
<?php if($navbtnbb['type'] == 'nav-list') { ?>
<li class="dropdown">              <a aria-expanded="false" aria-haspopup="true" role="button" data-toggle="dropdown" class="dropdown-toggle" href="#">                  <?= $navbtnbb['content'] ?> <span class="caret"></span>              </a>              
<?php if($navbtnbb['action'] != '') { ?>

                  <ul class="dropdown-menu"><?= $navbtnbb['action'] ?></ul>              <?php } ?>              
<?php if($navbtnbb['action'] == '') { ?>

                  <ul class="dropdown-menu">                       <?php foreach (\Builder\Controllers\NavbarAgent::GetElementsOfList($navbtnbb['id']) as $child) { ?>                            <li><a href="<?= $child['action'] ?>"><?= $child['content'] ?></a></li>                      <?php } ?>                  </ul>              <?php } ?>          </li><?php } ?><?php } ?>