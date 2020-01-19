<?php
if (!defined("TT_AP")){ header("Location: ../adminapanel.php?p=forbidden"); exit; }
//Проверка на наличие прав.
if (!$user->UserGroup()->getPermission("rules_edit")){ header("Location: ../../adminpanel.php?res=1"); exit; }
else {
$rulesText = file_get_contents("./engine/config/rules.sfc", FILE_USE_INCLUDE_PATH);
if (isset($_SESSION["result"])) $resultSave = $_SESSION["result"]; ?>
<div class="inner cover">
    <?php if (isset($_REQUEST["rules_save"])){
        if ($resultSave == true) echo "<div class=\"alert alert-success\"><span class=\"glyphicon glyphicon-ok\"></span> " . \Engine\LanguageManager::GetTranslation("rules_panel.rules_has_been_saved") . "</div>";
        else echo "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-remove\"></span> " . \Engine\LanguageManager::GetTranslation("rules_panel.rules_has_not_been_saved") . "</div>";
    } ?>
    <h1 class="cover-heading"><?= \Engine\LanguageManager::GetTranslation("rules_panel.panel_name") ?></h1>
    <p class="lead"><?= \Engine\LanguageManager::GetTranslation("rules_panel.panel_description") ?></p>
    <form action="./adminpanel/scripts/ruleser.php" method="post">
        <div class="linker">
            <div class="btn-group">
                <button class="btn btn-default" type="button" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.bold") ?>" id="bb_b"><strong>B</strong></button>
                <button class="btn btn-default" type="button" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.italic") ?>" id="bb_i"><i>I</i></button>
                <button class="btn btn-default" type="button" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.underline") ?>" id="bb_u"><u>U</u></button>
                <button class="btn btn-default" type="button" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.strike") ?>" id="bb_s"><s>S</s></button>
            </div>
            <div class="btn-group">
                <button class="btn btn-default" type="button" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.align_left") ?>" id="bb_left"><span class="glyphicon glyphicon-align-left"></span></button>
                <button class="btn btn-default" type="button" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.align_center") ?>" id="bb_center"><span class="glyphicon glyphicon-align-center"></span></button>
                <button class="btn btn-default" type="button" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.align_right") ?>" id="bb_right"><span class="glyphicon glyphicon-align-right"></span></button>
            </div>
            <div class="btn-group">
                <button class="btn btn-default" type="button" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.insert_hr") ?>" id="bb_hr"><span class="glyphicon glyphicon-minus"></span></button>
                <button class="btn btn-default" type="button" title="<?= \Engine\LanguageManager::GetTranslation("rules_panel.enumeration") ?>" id="bb_ol"><span class="glyphicon glyphicon-th-list"></span></button>
                <button class="btn btn-default" type="button" title="<?= \Engine\LanguageManager::GetTranslation("rules_panel.list_element") ?>" id="bb_item" style="background: #c0ffb4;"><span class="glyphicon glyphicon-star"></span></button>
            </div>
            <div class="btn-group">
                <select class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.color") ?>" id="bb_color">
                    <option value="black" style="color: black;"><?= \Engine\LanguageManager::GetTranslation("editor_functions.black") ?></option>
                    <option value="red" style="color: red;"><?= \Engine\LanguageManager::GetTranslation("editor_functions.red") ?></option>
                    <option value="green" style="color: green;"><?= \Engine\LanguageManager::GetTranslation("editor_functions.green") ?></option>
                    <option value="yellow" style="color: yellow;"><?= \Engine\LanguageManager::GetTranslation("editor_functions.yellow") ?></option>
                    <option value="orange" style="color: orange;"><?= \Engine\LanguageManager::GetTranslation("editor_functions.orange") ?></option>
                    <option value="blue" style="color: blue;"><?= \Engine\LanguageManager::GetTranslation("editor_functions.blue") ?></option>
                    <option value="grey" style="color: grey;"><?= \Engine\LanguageManager::GetTranslation("editor_functions.grey") ?></option>
                    <option value="darkgrey" style="color: #545454;"><?= \Engine\LanguageManager::GetTranslation("editor_functions.dark_grey") ?></option>
                    <option value="white" style="color: white; text-shadow: 1px 1px 1px black;"><?= \Engine\LanguageManager::GetTranslation("editor_functions.white") ?></option>
                </select>
                <select class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.font-size") ?>" id="bb_size">
                    <option value="12">12</option>
                    <option value="14">14</option>
                    <option value="16">16</option>
                    <option value="18">18</option>
                    <option value="20">20</option>
                </select>
            </div>
            <hr/>
            <textarea class="form-control" placeholder="<?= \Engine\LanguageManager::GetTranslation("editor_functions.enter_rules_text") ?>" style="width: 100%; min-height: 250px; resize: vertical; " id="rules_texter" name="rules_texter"><?php echo $rulesText; ?></textarea>
            <hr/>
            <div class="center">
                <div class="btn-group">
                    <button class="btn btn-default" type="submit" name="rules_save"><span class="glyphicon glyphicon-ok"></span> <?= \Engine\LanguageManager::GetTranslation("rules_panel.save") ?></button>
                    <a class="btn btn-default" href="../adminpanel.php"><span class="glyphicon glyphicon-arrow-left"></span> <?= \Engine\LanguageManager::GetTranslation("rules_panel.back") ?></a>
                </div>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    function insertBBCode(openTag, notNeedClose){
        var
            texter = document.getElementById("rules_texter");
            startText = texter.value.substring(0, texter.selectionStart);
            endText = texter.value.substring(texter.selectionEnd, texter.value.length);
            tagingText = texter.value.substring(texter.selectionStart, texter.selectionEnd);
            startPos = texter.selectionStart;
            endPos = texter.selectionEnd;
            startText += '[' + openTag + ']';
            if( notNeedClose != null) {
                if (!notNeedClose) endText = '[\/' + openTag + ']' + endText;
                else endText = '[\/' + notNeedClose + ']' + endText;
            }
            texter.value = startText + tagingText + endText;
            texter.focus();
            texter.setSelectionRange(startPos + (2 + openTag.length), endPos + (2 + openTag.length));

            //texter.value.insert
    }

    $('#bb_b').click(function(){
        insertBBCode('b', false);
    });
    $('#bb_s').click(function(){
        insertBBCode('s', false);
    });
    $('#bb_u').click(function(){
        insertBBCode('u', false);
    });
    $('#bb_i').click(function(){
        insertBBCode('i', false);
    });
    $('#bb_ol').click(function(){
        insertBBCode('ol', false);
    });
    $('#bb_item').click(function(){
        insertBBCode('*', null);
    });
    $('#bb_size').on("change",function(){
        insertBBCode('size='+this.options[this.selectedIndex].value, 'size');
    });
    $('#bb_color').on("change",function(){
        insertBBCode('color='+this.options[this.selectedIndex].value, 'color');
    });
</script>
<?php } ?>