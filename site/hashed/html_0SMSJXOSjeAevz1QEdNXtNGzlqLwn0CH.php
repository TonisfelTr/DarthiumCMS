
<?php if(\Users\Services\FlashSession::hasErrors()) { ?>

  	<div class="profile-reg-error-div" id="profile-reg-error-div" hidden>  		<p>Во время регистрации произошли следующие ошибки:</p>  		<ol>  			 <?php foreach (\Users\Services\FlashSession::hasErrors() as $msg) { ?>    				<li><?= $msg ?></li>  			<?php } ?>  		</ol>  	</div>  <?php } ?>