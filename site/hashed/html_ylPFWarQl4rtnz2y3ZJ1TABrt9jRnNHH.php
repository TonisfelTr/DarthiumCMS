<div class="profile-reg-message-div">      
<?php if(Users\Services\FlashSession::hasInfos()) { ?>

  	    <div class="profile-reg-error-div">  			<ol>  				 <?php foreach (Users\Services\FlashSession::getInfos() as $message) { ?>    					<li><?= $message ?></li>  				<?php } ?>  			</ol>  		</div>      <?php } ?>  	
<?php if(Users\Services\FlashSession::hasErrors()) { ?>

  		<div class="profile-reg-error-div">  			<ol>  				 <?php foreach (Users\Services\FlashSession::getErrors() as $message) { ?>    					<li><?= $message ?></li>  				<?php } ?>  			</ol>  		</div>  	<?php } ?>      <hr>      <p style="color: lightblue; text-decoration: underline;" onclick="$(this).parent('div').hide();">Закрыть сообщение</p>  </div>