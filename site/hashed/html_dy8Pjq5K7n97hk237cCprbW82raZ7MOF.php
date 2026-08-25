
<?php foreach (array_merge(\Builder\Controllers\NavbarAgent::GetElements(), \Engine\PluginAgent::GetNavbarBtns()) as $navbtnbbh) { ?>

<?php if(isset($navbtnbbh['visibility'])) { ?>

<?php if(($navbtnbbh['visibility'] == 'only_authorized' && \Users\UserAgent::isAuthorized() === false) ||  						($navbtnbbh['visibility'] == 'only_guest' && \Users\UserAgent::isAuthorized() !== false)) { ?>

              <?php continue(1); ?>          <?php } ?><?php } ?>      
<?php if($navbtnbbh['type'] == 'end-nav-btn') { ?>

          <li><a href="<?= $navbtnbbh['action'] ?>"><?= $navbtnbbh['content'] ?></a></li>      <?php } ?>      
<?php if($navbtnbbh['type'] == 'end-nav-list') { ?>
<li class="dropdown">              <a aria-expanded="false" aria-haspopup="true" role="button" data-toggle="dropdown" class="dropdown-toggle" href="#">                  <?= $navbtnbbh['content'] ?> <span class="caret"></span>              </a>              
<?php if($navbtnbbh['action'] != '') { ?>

                  <ul class="dropdown-menu"><?= $navbtnbbh['action'] ?></ul>              <?php } ?>              
<?php if($navbtnbbh['action'] == '') { ?>

                  <ul class="dropdown-menu">                       <?php foreach (\Builder\Controllers\NavbarAgent::GetElementsOfList($navbtnbbh['id']) as $child) { ?>                            <li><a href="<?= $child['action'] ?>"><?= $child['content'] ?></a></li>                      <?php } ?>                  </ul>              <?php } ?>          </li><?php } ?><?php } ?>