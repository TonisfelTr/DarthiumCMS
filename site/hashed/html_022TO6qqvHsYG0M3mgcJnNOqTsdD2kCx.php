
<?php if(\Users\Services\FlashSession::readFromSystemContainer('profile-edit')) { ?>

<?php if(\Users\Services\FlashSession::hasErrors()) { ?>

          <div class="profile-reg-error-div" id="profile-reg-error-div">              <p>Во время редактирования обнаружены следующие ошибки:</p>              <ol>                   <?php foreach (\Users\Services\FlashSession::getErrors() as $msg) { ?>                        <li><?= $msg ?></li>                  <?php } ?>              </ol>          </div>      <?php } ?>      
<?php if(\Users\Services\FlashSession::hasInfos()) { ?>

          <div class="profile-reg-success-div" id="profile-reg-success-div">              <ol>                   <?php foreach (\Users\Services\FlashSession::getInfos() as $msg) { ?>                        <li><?= $msg ?></li>                  <?php } ?>              </ol>          </div>      <?php } ?><?php } ?>