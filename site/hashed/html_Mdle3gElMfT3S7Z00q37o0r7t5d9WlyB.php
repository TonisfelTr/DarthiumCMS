
<?php if(\Users\Services\FlashSession::hasInfos()) { ?>

      <div class="alert alert-success">          <ul>               <?php foreach (\Users\Services\FlashSession::getInfos() as $info) { ?>                    <li><?= $info ?></li>              <?php } ?>          </ul>      </div>  <?php } ?>  
<?php if(\Users\Services\FlashSession::hasWarnings()) { ?>

      <div class="alert alert-warning">          <ul>               <?php foreach (\Users\Services\FlashSession::getWarnings() as $warning) { ?>                    <li><?= $warning ?></li>              <?php } ?>          </ul>      </div>  <?php } ?>  
<?php if(\Users\Services\FlashSession::hasErrors()) { ?>

      <div class="alert alert-danger">          <ul>               <?php foreach (\Users\Services\FlashSession::getErrors() as $error) { ?>                    <li><?= $error ?></li>              <?php } ?>          </ul>      </div>  <?php } ?>