<?php
if (!defined("TT_AP")){ header("Location: ../adminpanel.php?p=forbidden"); exit; }
if (!$user->UserGroup()->getPermission("sc_edit_pages") &&
    !$user->UserGroup()->getPermission("sc_create_pages") &&
    !$user->UserGroup()->getPermission("sc_remove_pages") &&
    !$user->UserGroup()->getPermission("sc_design_edit")){
    header("Location: ../adminpanel.php?res=1");
    exit;
}

$editPPerm = $user->UserGroup()->getPermission("sc_edit_pages");
$createPPerm = $user->UserGroup()->getPermission("sc_create_pages");
$removePPerm = $user->UserGroup()->getPermission("sc_remove_pages");
$editSContentPerm = $user->UserGroup()->getPermission("sc_design_edit");

if ($editPPerm || $createPPerm || $removePPerm) {
    $pageNumber = (!empty($_REQUEST["pl"])) ? $_REQUEST["pl"] : 1;
    if (isset($_REQUEST["search-author"]))
        $tablePage = \Forum\StaticPagesAgent::GetPagesListOfAuthor($_REQUEST["search-author"], $pageNumber);
    elseif (isset($_REQUEST["search-name"]))
        $tablePage = \Forum\StaticPagesAgent::GetPagesListOfName($_REQUEST["search-name"], $pageNumber);
    else
        $tablePage = \Forum\StaticPagesAgent::GetPagesList($pageNumber);
    $tablePageCount = count($tablePage);
    $previousPage = (!isset($_REQUEST["pl"])) ? "#" : "#&pl=" . $_REQUEST["pl"] - 1;
    $nextPage = (isset($_REQUEST["pl"]) && $_REQUEST["pl"] != $tablePageCount) ? "#&pl=" . ($_REQUEST["pl"] + 1) : "#";
    $page = "";
    if (isset($_REQUEST["search-author"])) {
        $value = $_REQUEST["search-author"];
        $label = "Автор:";
    }
    if (isset($_REQUEST["search-name"])) {
        $value = $_REQUEST["search-name"];
        $label = "Название страницы:";
    }
}

if ($editPPerm && isset($_GET["editpage"]) && \Forum\StaticPagesAgent::isPageExists($_GET["editpage"])){
    $page = new \Forum\StaticPage($_GET["editpage"]);
    $isEditMode = true;
} else {
    $isEditMode = false;
}

if ($editSContentPerm){
    $firstSmallBannerContent = \SiteBuilders\BannerAgent::GetBannersByName("firstbanner")["firstbanner"]["content"];
    $secondSmallBannerContent = \SiteBuilders\BannerAgent::GetBannersByName("smallbanner")["smallbanner"]["content"];
    $bigbanners = \SiteBuilders\BannerAgent::GetBanners("banner");
    $buttons = array();
    foreach ($bigbanners as $bigbanner){
        $bannerId = $bigbanner["id"];
        $bannerName = $bigbanner["name"];
        $class = ($bigbanner["isVisible"] == 1) ? "btn-success" : "btn-danger";
        array_push($buttons, "<button class=\"btn $class\" type=\"button\" data-banner-id=\"$bannerId\">$bannerName</button>");
    }
    $panels = \SiteBuilders\SidePanelsAgent::GetPanelsList();
    $panelsList = [];
    foreach ($panels as $panel){
        $id = $panel["id"];
        $panel = \SiteBuilders\SidePanelsAgent::GetPanel($id);
        $side = ($panel["type"] == "leftside") ? \Engine\LanguageManager::GetTranslation("staticc_panel.left") : \Engine\LanguageManager::GetTranslation("staticc_panel.right");
        $panelsList[] = "<option value=\"$id\">[$side] " . $panel["name"] . "</option>";
    }
}

?>

<div class="inner cover">
    <h1 class="cover-heading"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.page_name")?></h1>
    <p class="lead"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.page_description")?></p>
    <div class="btn-group" id="staticc-btn-panel">
        <?php if ($editPPerm || $removePPerm){ ?><button class="btn btn-default" type="button" id="staticc-pages-btn" data-div="staticc-pages-div"><span class="glyphicons glyphicons-pencil"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.page_name")?></button><?php } ?>
        <?php if ($editSContentPerm) { ?>        <button class="btn btn-default" type="button" id="staticc-content-edit-btn" data-div="staticc-content-edit-div"><span class="glyphicons glyphicons-puzzle-2"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.panel_name")?></button><?php } ?>
        <?php if ($isEditMode && $editPPerm) { ?><button class="btn btn-info" type="button" id="staticc-page-edit-btn" data-div="staticc-page-edit-div"><span class="glyphicons glyphicons-edit"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.edit_page")?> - "<?php echo $page->getPageName(); ?>"</button><?php } ?>
    </div>
    <form enctype="multipart/form-data" action="adminpanel/scripts/staticc.php" method="post">
        <div class="custom-group" id="staticc-panel">
            <?php if ($editPPerm || $removePPerm) { ?>
                <div class="div-border" id="staticc-pages-div" hidden>
                    <h2><?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.panel_name")?></h2>
                    <p class="helper"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.panel_description")?></p>
                    <hr>
                    <p><?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.panel_tip")?></p>
                    <input type="hidden" id="staticc-search-type" name="staticc-search-type" value="name">
                    <div class="input-group">
                        <input class="form-control" type="text" id="staticc-search-input" name="staticc-search-input" placeholder="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.page_name")?>" value="<?php echo $value; ?>">
                        <div class="input-group-btn" id="staticc-page-search-btns">
                            <button class="btn btn-default active" type="button" id="staticc-search-byname-btn" title="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.search_by_name")?>"><span class="glyphicons glyphicons-subtitles"></span></button>
                            <button class="btn btn-default" type="button" id="staticc-search-byauthor-btn" title="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.search_by_author")?>"><span class="glyphicons glyphicons-nameplate"></span></button>
                        </div>
                    </div>
                    <br>
                    <div class="btn-group">
                        <button class="btn btn-default" type="submit" name="staticc-search-btn"><span class="glyphicons glyphicons-search"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.search")?></button>
                        <a class="btn btn-default" href="?p=staticc" name="staticc-search-reset-btn"><span class="glyphicons glyphicons-book"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.cancel_filter")?></a>
                        <?php if ($removePPerm) { ?><button class="btn btn-default alert-danger" type="submit" name="staticc-search-remove-btn" id="staticc-search-remove-btn" disabled><span class="glyphicons glyphicons-bin"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.remove_selected_pages")?></button><?php }?>
                    </div>
                    <h3><?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.static_pages_list")?></h3>
                    <?php if (isset($_REQUEST["search-author"]) || isset($_REQUEST["search-name"])) { ?>
                        <div class="alert alert-info">
                        <?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.filters")?>:
                        <hr>
                        <strong><?php echo $label; ?></strong> <?php echo $value; ?>
                        </div><?php } ?>
                    <div class="alert alert-info" id="staticc-selected-div" style="display: none;"><strong><?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.selected_pages")?></strong> <span>0</span></div>
                    <div class="table-responsive">
                        <table class="table" id="staticc-pages-table">
                            <thead>
                            <tr class="staticc-table-header">
                                <td><input type="checkbox" id="staticc-table-select-all-checkbox"></td>
                                <td><?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.table_name")?></td>
                                <td><?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.table_description")?></td>
                                <td><?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.table_author")?></td>
                                <td><?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.table_time_creation")?></td>
                                <td><?php if ($createPPerm) { ?><button class="btn btn-default" type="button" id="staticc-page-create-btn" data-div="staticc-page-create-div" style="width: 100%;"><span class="glyphicons glyphicons-file-plus"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.create_new_static_page")?></button><?php } ?></td>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($tablePageCount == 0) { ?>
                                <tr>
                                    <td colspan="6" class="alert-info" style="text-align: center;"><span class="glyphicons glyphicons-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.no_static_pages")?></td>
                                </tr>
                            <?php } else ?>
                            <?php foreach($tablePage as $item){
                                $p = new \Forum\StaticPage($item["id"]); ?>
                                <tr>
                                    <td><input type="checkbox" data-spi="<?= $p->getPageID(); ?>"></td>
                                    <td><a href="/?sp=<?= $p->getPageID(); ?>"><?= $p->getPageName(); ?></a></td>
                                    <td><?= $p->getPageDescription(); ?></td>
                                    <?php $nickname = (!\Users\UserAgent::IsUserExist($p->getPageAuthorId())) ?
                                        \Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.deleted_author") :
                                        \Users\UserAgent::GetUserNick($p->getPageAuthorId()); ?>
                                    <td><?=$nickname?></td>
                                    <td><?= \Engine\Engine::DateFormatToRead($p->getPageCreateDate()); ?></td>
                                    <td><button class="btn btn-default alert-info" name="staticc-page-edit-btn" type="submit" formaction="adminpanel/scripts/staticc.php?id=<?php echo $p->getPageID(); ?>" style="width: 100%;"><?=\Engine\LanguageManager::GetTranslation("edit")?></button></td>
                                </tr>
                            <?php } ?>

                            </tbody>
                        </table>
                    </div>
                    <input type="hidden" id="staticc-page-delete" name="staticc-page-delete">
                    <?php if (\Forum\StaticPagesAgent::GetPagesCount() > 0) { ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <li <?php if ($previousPage == "#") echo "class=\"disabled\""; ?>>
                                    <a href="<?php echo $previousPage; ?>" aria-label="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.prev_page")?>">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= \Forum\StaticPagesAgent::GetPagesCount(); $i++){ ?>
                                    <li <?php if (!isset($_REQUEST["pl"]) || $_REQUEST["pl"] == $i) echo "class=\"active\""; ?>><a href="#&pl=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                                <?php } ?>
                                <li <?php if ($nextPage == "#") echo "class=\"disabled\""; ?>>
                                    <a href="<?php echo $nextPage; ?>" aria-label="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.next_page")?>">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php } ?>
                </div>
            <?php }
            if ($createPPerm) { ?>
                <div class="div-border" id="staticc-page-create-div" hidden>
                    <h2><?=\Engine\LanguageManager::GetTranslation("staticc_panel.creator_pages.panel_name")?></h2>
                    <p class="helper"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.creator_pages.panel_description")?></p>
                    <hr>
                    <p><?=\Engine\LanguageManager::GetTranslation("staticc_panel.creator_pages.panel_tip")?></p>
                    <div class="alert alert-info">
                        <p><span class="glyphicons glyphicons-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.creator_pages.address")?></p>
                        <hr>
                        <input class="form-control" type="text" readonly value="http://<?php echo $_SERVER["HTTP_HOST"]; ?>/?sp=<?php echo \Forum\StaticPagesAgent::GetLastPageID()+1; ?>">
                        <hr>
                        <p><?=\Engine\LanguageManager::GetTranslation("staticc_panel.creator_pages.address_tip")?></p>
                    </div>
                    <input class="form-control" name="staticc-page-create-name-input" type="text" maxlength="25" placeholder="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.creator_pages.page_name")?>">
                    <br>
                    <input class="form-control" name="staticc-page-create-keywords" type="text" maxlength="255" placeholder="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.creator_pages.page_keyword")?>">
                    <br>
                    <input class="form-control" name="staticc-page-create-description-input" type="text" maxlength="100" placeholder="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.creator_pages.page_description")?>">
                    <br>
                    <div class="btn-group">
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.bold")?>" name="bb_b"><strong>B</strong></button>
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.italic")?>" name="bb_i"><i>I</i></button>
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.underline")?>" name="bb_u"><u>U</u></button>
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.strike")?>" name="bb_s"><s>S</s></button>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.align_left")?>" name="bb_left"><span class="glyphicon glyphicon-align-left"></span></button>
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.align_center")?>" name="bb_center"><span class="glyphicon glyphicon-align-center"></span></button>
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.align_right")?>" name="bb_right"><span class="glyphicon glyphicon-align-right"></span></button>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.insert_hr")?>" name="bb_hr"><span class="glyphicon glyphicon-minus"></span></button>
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.enumerator")?>" name="bb_ol"><span class="glyphicon glyphicon-th-list"></span></button>
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.list_element")?>" name="bb_item" style="background: #c0ffb4;"><span class="glyphicon glyphicon-star"></span></button>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.link")?>" name="bb_a"><span class="glyphicon glyphicon-link"></span></button>
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.image")?>" name="bb_img"><span class="glyphicon glyphicon-picture"></span></button>
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.youtube")?>" name="bb_youtube"><span class="glyphicon glyphicon-play"></span></button>
                    </div>
                    <div class="btn-group">
                        <select class="btn btn-default" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.color")?>" name="bb_color">
                            <option value="black" style="color: black;"><?=\Engine\LanguageManager::GetTranslation("editor_functions.black")?></option>
                            <option value="red" style="color: red;"><?=\Engine\LanguageManager::GetTranslation("editor_functions.red")?></option>
                            <option value="green" style="color: green;"><?=\Engine\LanguageManager::GetTranslation("editor_functions.green")?></option>
                            <option value="yellow" style="color: yellow;"><?=\Engine\LanguageManager::GetTranslation("editor_functions.yellow")?></option>
                            <option value="orange" style="color: orange;"><?=\Engine\LanguageManager::GetTranslation("editor_functions.orange")?></option>
                            <option value="blue" style="color: blue;"><?=\Engine\LanguageManager::GetTranslation("editor_functions.blue")?></option>
                            <option value="grey" style="color: grey;"><?=\Engine\LanguageManager::GetTranslation("editor_functions.grey")?></option>
                            <option value="darkgrey" style="color: #545454;"><?=\Engine\LanguageManager::GetTranslation("editor_functions.dark_grey")?></option>
                            <option value="white" style="color: white; text-shadow: 1px 1px 1px black;"><?=\Engine\LanguageManager::GetTranslation("editor_functions.white")?></option>
                        </select>
                        <select class="btn btn-default" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.font-size")?>" name="bb_size">
                            <option value="12">12</option>
                            <option value="14">14</option>
                            <option value="16">16</option>
                            <option value="18">18</option>
                            <option value="20">20</option>
                        </select>
                    </div>
                    <hr/>
                    <textarea class="form-control" placeholder="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.creator_pages.enter_page_content")?>" style="width: 100%; min-height: 250px; resize: vertical; " id="staticc-page-create-textarea" name="staticc-page-create-textarea"></textarea>
                    <hr/>
                    <div class="center">
                        <div class="btn-group">
                            <button class="btn btn-default" type="submit" name="staticc-page-create-create-btn"><span class="glyphicon glyphicon-ok"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.creator_pages.public_page")?></button>
                        </div>
                    </div>
                </div>
            <?php }
            if ($isEditMode && $editPPerm) { ?>
                <div class="div-border" id="staticc-page-edit-div" hidden>
                    <h2>"<?php echo $page->getPageName(); ?>"</h2>
                    <p class="helper"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.editor_pages.panel_description")?></p>
                    <input type="hidden" value="<?php echo $page->getPageID(); ?>" name="staticc-page-edit-id">
                    <div class="alert alert-info">
                        <p><span class="glyphicons glyphicons-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.editor_pages.address")?></p>
                        <hr>
                        <input class="form-control" type="text" readonly="" value="http://<?php echo $_SERVER["HTTP_HOST"] . "/?sp=" . $page->getPageID(); ?>">
                        <hr>
                        <p><?=\Engine\LanguageManager::GetTranslation("staticc_panel.editor_pages.address_tip")?></p>
                    </div>
                    <input class="form-control" type="text" maxlength="25" placeholder="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.editor_pages.page_name")?>" name="staticc-page-edit-name-input" value="<?php echo $page->getPageName(); ?>">
                    <br>
                    <input class="form-control" name="staticc-page-edit-keywords" type="text" maxlength="255" placeholder="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.editor_pages.page_keyword")?>" value="<?=$page->getKeyWords()?>">
                    <br>
                    <input class="form-control" type="text" maxlength="100" placeholder="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.editor_pages.page_description")?>" name="staticc-page-edit-description-input" value="<?php echo $page->getPageDescription(); ?>">
                    <br>
                    <div class="btn-group">
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.bold")?>" name="bb_b"><strong>B</strong></button>
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.italic")?>" name="bb_i"><i>I</i></button>
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.underline")?>" name="bb_u"><u>U</u></button>
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.strike")?>" name="bb_s"><s>S</s></button>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.align_left")?>" name="bb_left"><span class="glyphicon glyphicon-align-left"></span></button>
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.align_center")?>" name="bb_center"><span class="glyphicon glyphicon-align-center"></span></button>
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.align_right")?>" name="bb_right"><span class="glyphicon glyphicon-align-right"></span></button>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.insert_hr")?>" name="bb_hr"><span class="glyphicon glyphicon-minus"></span></button>
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.enumerator")?>" name="bb_ol"><span class="glyphicon glyphicon-th-list"></span></button>
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.list_element")?>" name="bb_item" style="background: #c0ffb4;"><span class="glyphicon glyphicon-star"></span></button>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.link")?>" name="bb_a"><span class="glyphicon glyphicon-link"></span></button>
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.image")?>" name="bb_img"><span class="glyphicon glyphicon-picture"></span></button>
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.youtube")?>" name="bb_youtube"><span class="glyphicon glyphicon-play"></span></button>
                    </div>
                    <div class="btn-group">
                        <select class="btn btn-default" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.color")?>" name="bb_color">
                            <option value="black" style="color: black;"><?=\Engine\LanguageManager::GetTranslation("editor_functions.black")?></option>
                            <option value="red" style="color: red;"><?=\Engine\LanguageManager::GetTranslation("editor_functions.red")?></option>
                            <option value="green" style="color: green;"><?=\Engine\LanguageManager::GetTranslation("editor_functions.green")?></option>
                            <option value="yellow" style="color: yellow;"><?=\Engine\LanguageManager::GetTranslation("editor_functions.yellow")?></option>
                            <option value="orange" style="color: orange;"><?=\Engine\LanguageManager::GetTranslation("editor_functions.orange")?></option>
                            <option value="blue" style="color: blue;"><?=\Engine\LanguageManager::GetTranslation("editor_functions.blue")?></option>
                            <option value="grey" style="color: grey;"><?=\Engine\LanguageManager::GetTranslation("editor_functions.grey")?></option>
                            <option value="darkgrey" style="color: #545454;"><?=\Engine\LanguageManager::GetTranslation("editor_functions.dark_grey")?></option>
                            <option value="white" style="color: white; text-shadow: 1px 1px 1px black;"><?=\Engine\LanguageManager::GetTranslation("editor_functions.white")?></option>
                        </select>
                        <select class="btn btn-default" title="<?=\Engine\LanguageManager::GetTranslation("editor_functions.font-size")?>" name="bb_size">
                            <option value="12">12</option>
                            <option value="14">14</option>
                            <option value="16">16</option>
                            <option value="18">18</option>
                            <option value="20">20</option>
                        </select>
                    </div>
                    <hr/>
                    <textarea class="form-control" placeholder="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.editor_pages.insert_page_content")?>" style="width: 100%; min-height: 250px; resize: vertical; " id="staticc-page-edit-textarea" name="staticc-page-edit-textarea"><?php echo $page->getContent(); ?></textarea>
                    <hr/>
                    <div class="center">
                        <div class="btn-group">
                            <button class="btn btn-default" type="submit" name="staticc-page-edit-edit-btn"><span class="glyphicon glyphicon-ok"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.editor_pages.save")?></button>
                            <button class="btn btn-default" type="reset"><span class="glyphicon glyphicon-erase"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.editor_pages.drop_changes")?></button>
                        </div>
                    </div>
                </div>
            <?php }
            if ($editSContentPerm) { ?>
                <div class="div-border" id="staticc-content-edit-div" hidden>
                    <h2><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.panel_name")?></h2>
                    <p class="helper"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.panel_description")?></p>
                    <hr>
                    <p><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.panel_tip")?></p>
                    <div class="btn-group" id="staticc-content-btn-panel">
                        <button class="btn btn-default active" type="button" data-subpanel-id="staticc-content-banners"><span class="glyphicons glyphicons-drop"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_btn")?></button>
                        <button class="btn btn-default" type="button" data-subpanel-id="staticc-content-sidepanels"><span class="glyphicons glyphicons-more-items"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.sidepanels_btn")?></button>
                        <button class="btn btn-default" type="button" data-subpanel-id="staticc-content-navbar"><span class="glyphicon glyphicon-option-horizontal"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_btn")?></button>
                    </div>
                    <hr>
                    <div id="staticc-content-error-div" hidden><span id="staticc-content-error-span"></span></div>
                    <div id="staticc-content-container">
                        <div id="staticc-content-banners" hidden>
                            <p><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.panel_tip")?></p>
                            <div class="input-group">
                                <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.first_banner")?></div>
                                <input class="form-control" type="text" id="staticc-firstsm-html-input" placeholder="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.placeholder_first")?>" value="<?php echo $firstSmallBannerContent; ?>">
                                <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.small_banner_size")?> 88х31</div>
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.actions")?> <span class="caret"></span></button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a id="staticc-smbanner-first-save" title="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.save_tip")?>"><span class="glyphicons glyphicons-ok"></span> <?=\Engine\LanguageManager::GetTranslation("save")?></a></li>
                                        <li><a id="staticc-smbanner-first-remove" title="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.remove_tip")?>"><span class="glyphicons glyphicons-remove"></span> <?=\Engine\LanguageManager::GetTranslation("remove")?></a></li>
                                        <li><a id="staticc-smbanner-first-clear" title="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.clear_tip")?>"><span class="glyphicons glyphicons-erase"></span> <?=\Engine\LanguageManager::GetTranslation("clear")?></a></li>
                                    </ul>
                                </div>
                            </div>
                            <br>
                            <div class="input-group">
                                <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.second_banner")?></div>
                                <input class="form-control" type="text" id="staticc-secondsm-html-input" placeholder="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.placeholder_second")?>" value="<?php echo $secondSmallBannerContent; ?>">
                                <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.small_banner_size")?> 88х31</div>
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.actions")?> <span class="caret"></span></button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a id="staticc-smbanner-second-save" title="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.save_tip")?>"><span class="glyphicons glyphicons-ok"></span> <?=\Engine\LanguageManager::GetTranslation("save")?></a></li>
                                        <li><a id="staticc-smbanner-second-remove" title="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.remove_tip")?>"><span class="glyphicons glyphicons-remove"></span> <?=\Engine\LanguageManager::GetTranslation("remove")?></a></li>
                                        <li><a id="staticc-smbanner-second-clear" title="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.clear_tip")?>"><span class="glyphicons glyphicons-erase"></span> <?=\Engine\LanguageManager::GetTranslation("clear")?></a></li>
                                    </ul>
                                </div>
                            </div>
                            <hr>
                            <div class="container-fluid">
                                <div class="btn-group-vertical col-lg-3 col-md-6 col-sm-6 col-xs-12" id="staticc-banner-btns">
                                    <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.big_banners_count")?><span id="staticc-banners-counter"><?php echo \SiteBuilders\BannerAgent::GetBigBannersCount(); ?></span>
                                    <button class="btn btn-default" type="button" id="staticc-create-banner-btn"><span class="glyphicons glyphicons-plus-sign"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.add_big_banner")?></button>
                                    <?php foreach($buttons as $b){
                                        echo $b;
                                    } ?>
                                </div>
                                <div class="div-border col-lg-9 col-md-6 col-sm-6 col-xs-12" id="staticc-create-banner-div" style="display: none;">
                                    <input type="hidden" id="staticc-banner-current-id">
                                    <p><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.add_banner_tip")?></p>
                                    <input class="form-control" type="text" id="staticc-create-banner-name-input" placeholder="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.placeholder_name")?>">
                                    <p class="alert alert-info"><span class="glyphicons glyphicons-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.name_tip")?></p>
                                    <input class="form-control" type="text" id="staticc-create-banner-link-input" placeholder="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.code")?>">
                                    <br>
                                    <label for="staticc-create-banner-visibility-input"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.turn_on_banner")?>: </label>
                                    <input type="checkbox" id="staticc-create-banner-visibility-input">
                                    <p class="alert alert-info"><span class="glyphicons glyphicons-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.status_banner_tip")?></p>
                                    <div class="btn-group">
                                        <button class="btn btn-default" type="button" id="staticc-create-banner-send-btn"><span class="glyphicons glyphicons-ok"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.banners_panel.create_banner")?></button>
                                        <button class="btn btn-default" type="button" id="staticc-remove-banner-send-btn"><span class="glyphicons glyphicons-erase"></span> <?=\Engine\LanguageManager::GetTranslation("remove")?></button>
                                        <button class="btn btn-default" type="button" id="staticc-create-banner-cancel-btn"><span class="glyphicons glyphicons-remove"></span> <?=\Engine\LanguageManager::GetTranslation("cancel")?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="staticc-content-sidepanels" hidden>
                            <p><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.sidepanels_panel.panel_description")?></p>
                            <div class="alert alert-info"><span class="glyphicons glyphicons-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.sidepanels_panel.panel_tip")?></div>
                            <div class="container-fluid">
                                <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2" id="staticc-left-panel-div" style="display: none;">
                                    <div class="side-block">
                                        <div class="side-block-header-left">{PANEL_TITLE}</div>
                                        <div class="side-block-body">
                                            {PANEL_CONTENT}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10">
                                    <div class="input-group">
                                        <select class="form-control" id="staticc-panels-selector">
                                            <option value="none" selected><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.sidepanels_panel.not_selected")?></option>
                                            <?php foreach ($panelsList as $panel){
                                                echo $panel;
                                            } ?>
                                        </select>
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.sidepanels_panel.actions")?> <span class="caret"></span></button>
                                            <ul class="dropdown-menu dropdown-menu-right">
                                                <li><a id="staticc-panels-add" title="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.sidepanels_panel.create_panel")?>"><span class="glyphicons glyphicons-plus"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.sidepanels_panel.create_panel")?></a></li>
                                                <li><a id="staticc-panels-remove" title="<?=\Engine\LanguageManager::GetTranslation("remove")?>"><span class="glyphicons glyphicons-erase"></span> <?=\Engine\LanguageManager::GetTranslation("remove")?></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <br>
                                    <div id="staticc-panel-editor-div" class="div-border">
                                        <div class="input-group">
                                            <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.sidepanels_panel.block_name")?></div>
                                            <input type="text" class="form-control" id="staticc-panel-editor-title" maxlength="150">
                                        </div>
                                        <br>
                                        <div class="alert alert-info">
                                            <span class="glyphicons glyphicons-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.sidepanels_panel.block_name_tip")?>
                                        </div>
                                        <div class="input-group">
                                            <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.sidepanels_panel.content")?></div>
                                            <textarea class="form-control non-resize" id="staticc-panel-editor-content"></textarea>
                                        </div>
                                        <br>
                                        <div class="alert alert-info">
                                            <span class="glyphicons glyphicons-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.sidepanels_panel.content_tip")?>
                                        </div>
                                        <label for="staticc-panel-editor-visibility"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.sidepanels_panel.visible")?>:</label>
                                        <input type="checkbox" id="staticc-panel-editor-visibility">
                                        <div class="input-group">
                                            <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.sidepanels_panel.visible_side")?></div>
                                            <select class="form-control" id="staticc-panel-editor-side">
                                                <option value="left"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.sidepanels_panel.vs_left")?></option>
                                                <option value="right"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.sidepanels_panel.vs_right")?></option>
                                            </select>
                                        </div>
                                        <br>
                                        <div class="btn-group">
                                            <button class="btn btn-default" id="staticc-panel-editor-send-btn" type="button"><span class="glyphicons glyphicons-ok"></span> <span id="staticc-panel-editor-send-btn-content"></span></button>
                                            <button class="btn btn-default" id="staticc-panel-editor-remove-btn" type="button" style="display: none;"><span class="glyphicons glyphicons-delete"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.sidepanels_panel.remove_panel")?></button>
                                            <button class="btn btn-default" id="staticc-panel-editor-erase-btn" type="button"><span class="glyphicons glyphicons-erase"></span> <?=\Engine\LanguageManager::GetTranslation("cancel")?></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2" id="staticc-right-panel-div" style="display: none;">
                                    <div class="side-block">
                                        <div class="side-block-header-right"></div>
                                        <div class="side-block-body">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="staticc-content-navbar" hidden>
                            <p><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.panel_description")?></p>
                            <div class="alert alert-info"><span class="glyphicons glyphicons-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.panel_tip")?></div>
                            <div class="btn-group" id="btn-manager-div">
                                <button class="btn btn-default" id="navbar-add-btn" type="button"><span class="glyphicons glyphicons-plus"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.create_btn")?></button>
                                <button class="btn btn-default" id="navbar-add-list-btn" type="button"><span class="glyphicons glyphicons-list"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.create_list")?></button>
                                <button class="btn btn-default" id="navbar-edit-btn" type="button"><span class="glyphicons glyphicons-edit"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.edit_button")?></button>
                                <button class="btn btn-default" id="navbar-remove-btn" type="button"><span class="glyphicon glyphicon-trash"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.remove_button")?></button>
                            </div>
                            <hr>
                            <div id="btn-operation-container">
                                <p><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.select_button")?></p>
                                <div class="div-border" id="create-btn-bar">
                                    <p><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.creating_button")?></p>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.text_button")?></div>
                                        <input class="form-control" type="text" id="name-create-input">
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.link_button")?></div>
                                        <input class="form-control" type="text" id="link-create-input">
                                    </div>
                                    <br>
                                    <div class="btn-group">
                                        <button class="btn btn-default" id="create-btn-btn" type="button"><?=\Engine\LanguageManager::GetTranslation("save")?></button>
                                    </div>
                                </div>
                                <div class="div-border" id="create-list-bar">
                                    <p><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.creating_list")?></p>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.list_name")?></div>
                                        <input class="form-control" type="text" id="list-name-create-input">
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.list_text")?></div>
                                        <input class="form-control" id="list-text-create-input" type="text">
                                        <div class="form-control alert-info"><span class="glyphicons glyphicons-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.list_text_tip")?></div>
                                    </div>
                                    <div class="btn-group">
                                        <button class="btn btn-default" id="create-list-btn-btn" type="button"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.save")?></button>
                                    </div>
                                </div>
                                <div class="div-border" id="edit-btn-bar">
                                    <p><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.editing_button")?></p>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.text_button")?></div>
                                        <input class="form-control" type="text" id="name-edit-input">
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.link_button")?></div>
                                        <input class="form-control" type="text" id="link-edit-input">
                                    </div>
                                    <div class="btn-group">
                                        <button class="btn btn-default" id="edit-btn-btn" type="button"><?=\Engine\LanguageManager::GetTranslation("save")?></button>
                                    </div>
                                </div>
                                <div class="div-border" id="edit-list-bar">
                                    <p><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.editing_list")?></p>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.list_name")?></div>
                                        <input class="form-control" type="text" id="list-name-edit-input">
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.list_text")?></div>
                                        <input class="form-control" id="list-text-edit-input" type="text">
                                        <div class="form-control alert-info"><span class="glyphicons glyphicons-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.list_text_tip")?></div>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.list_buttons")?></div>
                                        <select class="form-control" id="list-edit-btns-selected">
                                            <option><?=\Engine\LanguageManager::GetTranslation("not_setted")?></option>
                                        </select>
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="button" id="create-new-li-btn" title="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.add_button_to_list")?>"><span class="glyphicons glyphicons-plus"></span></button>
                                            <button class="btn btn-default" type="button" id="remove-li-btn" title="<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.remove_button_from_list")?>"><span class="glyphicons glyphicons-minus"></span></button>
                                        </span>
                                    </div>
                                    <div id="li-div-creator" hidden>
                                        <hr>
                                        <div class="alert alert-info"><span class="glyphicons glyphicons-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.element_tip")?></div>
                                        <div class="input-group">
                                            <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.text_button")?></div>
                                            <input class="form-control" type="text" id="li-text-input">
                                        </div>
                                        <div class="input-group">
                                            <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.link_button")?></div>
                                            <input class="form-control" type="text" id="li-link-input">
                                        </div>
                                        <button class="btn btn-default" type="button" id="edit-li"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.save_element")?></button>
                                        <hr>
                                    </div>
                                    <div id="li-div-editor" hidden>
                                        <hr>
                                        <div class="alert alert-info"><span class="glyphicons glyphicons-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.element_tip")?></div>
                                        <div class="input-group">
                                            <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.list_text")?></div>
                                            <input class="form-control" type="text" id="li-edit-text-input">
                                        </div>
                                        <div class="input-group">
                                            <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.link_text")?></div>
                                            <input class="form-control" type="text" id="li-edit-link-input">
                                        </div>
                                        <button class="btn btn-default" type="button" id="save-changes-btn"><?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.navbar_panel.save_element")?></button>
                                        <hr>
                                    </div>
                                    <div class="btn-group">
                                        <button class="btn btn-default" id="edit-list-btn-btn" type="button"><?=\Engine\LanguageManager::GetTranslation("save")?></button>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="navbar-group-box" id="navbar-group-box">
                                <div class="btn-group">
                                    <?php
                                    $navbarbtns = \SiteBuilders\NavbarAgent::GetElements();
                                    foreach ($navbarbtns as $navbarbtn){
                                        if ($navbarbtn["type"] == "nav-btn"){
                                            $data_href = $navbarbtn["action"];
                                            $content = $navbarbtn["content"];
                                            $id = $navbarbtn["id"];
                                            echo "<button class=\"btn btn-default\" type=\"button\" data-href=\"$data_href\" data-id=\"$id\">$content</button>";
                                        }
                                        if ($navbarbtn["type"] == "nav-list"){
                                            $children = \SiteBuilders\NavbarAgent::GetElementsOfList($navbarbtn["id"]);
                                            $data_content = $navbarbtn["action"];
                                            $content = $navbarbtn["content"];
                                            $id = $navbarbtns["id"]; ?>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default" data-id="<?=$id?>" data-content="<?=$data_content?>"><?=$content?></button>
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="caret"></span>
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <?php
                                                    foreach ($children as $kid){
                                                        $id = $kid[0];
                                                        $data_href = $kid[2];
                                                        $content = $kid[1];?>
                                                        <li data-href="<?=$data_href?>" data-id="<?=$id?>"><?=$content?></li>
                                                    <?php } ?>
                                                </ul>
                                            </div>

                                        <?php }
                                    } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(document).ready(function (){
        $("#btn-operation-container > p").hide();
    });

    $("#btn-manager-div > button").on("click", function () {
        $("#btn-manager-div > button").removeClass("active");
        $(this).addClass("active");
        $("#navbar-group-box").css("background", "white");
    });

    if ($("#navbar-group-box > div").children().length > 1)
        $("#navbar-group-box > div > span").hide();
    // Creation default button ///////////////////////////////////////////////////
    $("#link-create-input").on("click", function() {
        $("#link-create-input").removeClass("alert alert-danger");
        $("#link-create-input").attr("placeholder", "");
    });

    $("#name-create-input").on("click", function() {
        $("#name-create-input").removeClass("alert alert-danger");
        $("#name-create-input").attr("placeholder", "");
    });

    $("#create-btn-btn").on("click", function() {
        if ($("#name-create-input").val() == ""){
            $("#name-create-input").addClass("alert alert-danger");
            $("#name-create-input").attr("placeholder", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.enter_button_text")?>");
            return;
        }
        if ($("#link-create-input").val() == ""){
            $("#link-create-input").addClass("alert alert-danger");
            $("#link-create-input").attr("placeholder", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.enterlink")?>");
            return;
        }
        if ($("#navbar-group-box > div").children().length == 7){
            ShowSCErrorBox("error", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.button_limit")?>");
            return;
        }
        var btn = document.createElement("button");
        $(btn).addClass("btn");
        $(btn).addClass("btn-default");
        $(btn).attr("type", "button");
        $(btn).attr("data-href", $("#link-create-input").val());
        $(btn).append($("#name-create-input").val());
        $("#navbar-group-box > div").append(btn);
        $.ajax({
            url: "adminpanel/scripts/ajax/navbarajax.php",
            type: "POST",
            data: "create_btn&text=" + $("#name-create-input").val() +
                "&link=" + $("#link-create-input").val(),
            success: function(data){
                if (data === "okey")
                    ShowSCErrorBox("okey", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.button_creation_success")?>");
            }
        });
    });
    /////////////////////////////////////////////////////////////////////////////////////////
    /// Create list /////////////////////////////////////////////////////////////////////////
    $("#list-name-create-input").on("click", function() {
        $("#list-name-create-input").removeClass("alert alert-danger");
        $("#list-name-create-input").attr("placeholder", "");
    });

    $("#create-list-btn-btn").on("click", function () {
        if ($("#list-name-create-input").val() == ""){
            $("#list-name-create-input").addClass("alert alert-danger");
            $("#list-name-create-input").attr("placeholder", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.enter_list_name")?>");
            return;
        }
        if ($("#navbar-group-box > div > span").is(":hidden") == false)
            $("#navbar-group-box > div > span").hide();

        if ($("#navbar-group-box > div").children().length >= 7){
            ShowSCErrorBox("error", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.button_limit")?>");
            return;
        }
        var div_group = document.createElement("div");
        var div_btn = document.createElement("button");
        var div_btn_dropdown = document.createElement("button");
        var span = document.createElement("span");
        var span_sr = document.createElement("span");
        var ul = document.createElement("ul");
        $(span).addClass("caret");
        $(span_sr).addClass("sr-only");
        $(span_sr).append("Toggle Dropdown");
        $(div_group).addClass("btn-group");
        $(div_btn).addClass("btn btn-default");
        if ($("#list-text-create-input").val() != "")
            $(div_btn).attr("data-content", $("#list-text-create-input").val());
        $(div_btn).attr("type", "button");
        $(div_btn_dropdown).addClass("btn btn-default dropdown-toggle");
        $(div_btn_dropdown).attr("data-toggle", "dropdown");
        $(ul).addClass("dropdown-menu");
        $(div_btn_dropdown).append(span);
        $(div_btn_dropdown).append(span_sr);
        $(div_btn).append($("#list-name-create-input").val());
        $(div_group).append(div_btn);
        $(div_group).append(div_btn_dropdown);
        $(div_group).append(ul);
        $("#navbar-group-box > div").append(div_group);
        $.ajax({
            url: "adminpanel/scripts/ajax/navbarajax.php",
            type: "POST",
            data: "create_list&text=" + $("#list-name-create-input").val() +
                "&action=" + $("#list-text-create-input").val(),
            success: function(data){
                if (data === "okey")
                    ShowSCErrorBox("okey", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.list_created_success")?>");
            }
        });
    });
    /////////////////////////////////////////////////////////////////////////////////////////
    /// Create editor ///////////////////////////////////////////////////////////////////////
    $("#navbar-edit-btn").on("click", function() {
        if ($(this).hasClass("active")) {
            $("#btn-operation-container > div").hide();
            $("#btn-operation-container > p").show();
            $("#navbar-group-box").css("background", "darkgrey");
            $("#navbar-group-box > div > button").on("click", function() {
                if (!$("#navbar-edit-btn").hasClass("active"))
                    return;

                $("#edit-list-bar").hide();
                $("#edit-btn-bar").show();
                $("#name-edit-input").val($(this).text());
                $("#link-edit-input").val($(this).attr("data-href"));
                $("#btn-operation-container > p").hide();
                var btn = $(this);
                $("#edit-btn-btn").on("click", function() {
                    if ($("#name-edit-input").val() == "")
                        return;
                    btn.text($("#name-edit-input").val());
                    btn.attr("data-href", $("#link-edit-input").val());
                });
            });
            $("#navbar-group-box > div > div.btn-group > :first-child").on("click", function() {
                if (!$("#navbar-edit-btn").hasClass("active"))
                    return;

                $("#edit-btn-bar").hide();
                $("#edit-list-bar").show();
                $("#list-name-edit-input").val($(this).text());
                $("#list-text-edit-input").val($(this).attr("data-content"));
                //Clear selector
                $("#list-edit-btns-selected").find("option").remove();
                var std_option = document.createElement("option");
                $(std_option).append("Не указано");
                //Add standart option to selector
                $("#list-edit-btns-selected").append(std_option);

                var ul = $(this).parent("div").children("ul");
                $(ul).children("li").each(function() {
                    var option = document.createElement("option");
                    $(option).attr("data-href", $(this).attr("data-href"));
                    $(option).append($(this).text());
                    $("#list-edit-btns-selected").append(option);
                });

                var edit_btn = $(this);
                $("#edit-list-btn-btn").on("click", function() {
                    if ($("#list-name-edit-input").val() == "")
                        return;
                    $(edit_btn).text($("#list-name-edit-input").val());
                    $(edit_btn).attr("data-content", $("#list-text-edit-input").val());
                    $.ajax({
                        url: "adminpanel/scripts/ajax/navbarajax.php",
                        type: "POST",
                        data: "change_list_param&content=" + $("#list-name-edit-input").val() +
                            "&action=" + $("#list-text-edit-input").val() +
                            "&id=" + $(edit_btn).attr("data-id")
                    });
                });

                $("#edit-li").on("click", function() {
                    if ($("#li-text-input").val() == ""){
                        $("#li-text-input").attr("placeholder", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.enter_button_name")?>");
                        return;
                    }
                    if ($("#li-link-input").val() == ""){
                        $("#li-link-input").attr("placeholder", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.enter_link")?>");
                        return;
                    }
                    var li = document.createElement("li");
                    var option = document.createElement("option");
                    $.ajax({
                        url: "adminpanel/scripts/ajax/navbarajax.php",
                        type: "POST",
                        data: "create_list_element&text=" + $("#li-text-input").val() +
                            "&action=" + $("#li-link-input").val() +
                            "&id=" + $(edit_btn).attr("data-id"),
                        success: function(data){
                            $(li).attr("data-id", data);
                        }
                    });
                    $(li).append($("#li-text-input").val());
                    $(option).append($("#li-text-input").val());
                    $(option).attr("data-href", $("#li-link-input").val());
                    $(li).attr("data-href", $("#li-link-input").val());
                    $(ul).append(li);
                    $("#list-edit-btns-selected").append(option);
                    $("#li-text-input").val("");
                    $("#li-link-input").val("");
                    $("#li-div-creator").hide();
                });

                $("#list-edit-btns-selected").on("change", function() {
                    if ($("#list-edit-btns-selected option:selected").text() == "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.not_setted")?>"){
                        $("#li-div-editor").hide();
                        return;
                    }
                    $("#li-div-editor").show();
                    $("#li-div-creator").hide();
                    $("#li-edit-text-input").val($(this).val());
                    $("#li-edit-link-input").val($("#list-edit-btns-selected option:selected").attr("data-href"));
                });

                $("#save-changes-btn").on("click", function () {
                    if ($("#li-edit-text-input").val() == ""){
                        $("#li-edit-text-input").attr("placeholder", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.enter_button_name")?>");
                        return;
                    }
                    if ($("#li-edit-link-input").val() == ""){
                        $("#li-edit-link-input").attr("placeholder", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.enter_link")?>");
                        return;
                    }
                    //Change text
                    var id_link = 0;
                    $(ul).children("li").each(function() {
                        if ($(this).text() == $("#list-edit-btns-selected").val()) {
                            id_link = $(this).attr("data-id");
                        }
                    });
                    $.ajax({
                        url: "adminpanel/scripts/ajax/navbarajax.php",
                        type: "POST",
                        data: "change_list_element&content=" + $("#li-edit-text-input").val() +
                            "&action=" + $("#li-edit-link-input").val() +
                            "&id=" + id_link,
                        success: function(data){
                            if (data === "okey")
                                ShowSCErrorBox("okey", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.list_updated_success")?>");
                        }
                    });
                    //End change text.
                    $("#li-div-editor").hide();
                    $("#list-edit-btns-selected option:selected").text($("#li-edit-text-input").val());
                    $("#list-edit-btns-selected option:selected").attr("data-href", $("#li-edit-link-input").val());
                    $("#li-edit-text-input").val("");
                    $("#li-edit-link-input").val("");
                    $("#list-edit-btns-selected").val("<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.not_setted")?>");
                });

                $("#create-new-li-btn").on("click", function() {
                    if ($(ul).children("li").length >= 10){
                        ShowSCErrorBox("error", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.list_limit")?>");
                        return;
                    }
                    $("#li-div-creator").show();
                    $("#li-text-input").val("");
                    $("#li-link-input").val("");
                    $("#li-div-editor").hide();
                });

                $("#remove-li-btn").on("click", function() {
                    if ($("#list-edit-btns-selected").val() != "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.not_setted")?>"){
                        var id_link = 0;
                        $(ul).children("li").each(function() {
                            if ($(this).text() == $("#list-edit-btns-selected").val()) {
                                id_link = $(this).attr("data-id");
                            }
                        });
                        $.ajax({
                            url: "adminpanel/scripts/ajax/navbarajax.php",
                            type: "POST",
                            data: "remove_list_element&id=" + id_link,
                            success: function(data){
                                if (data === "okey")
                                    ShowSCErrorBox("okey", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.element_removed")?>");
                            }
                        });
                        $(ul).children("li").each(function() {
                            if ($(this).text() == $("#list-edit-btns-selected").val())
                                $(this).remove();
                        });
                        $("#list-edit-btns-selected option:selected").remove();
                        $("#li-div-editor").hide();
                        $("#li-div-creator").hide();
                    }
                });
            });

        }
        else {
            $("#btn-operation-container > div").hide();
            $("#btn-operation-container > p").hide();
            $("#navbar-group-box").css("background", "white");
        }
    });
    ////////////////////////////////////////////////////////////////////////////////////////
    /// Create removing.
    $("#navbar-remove-btn").on("click", function (){
        $("#btn-operation-container > p").show();
        $("#btn-operation-container > div").hide();
        $("#navbar-group-box").css("background", "gray");
        $("#btn-manager-div > button").removeClass("active");
        $(this).addClass("active");
        $("#navbar-group-box > div > button").on("click", function() {
            if ($("#navbar-remove-btn").hasClass("active")) {
                var id_link = $(this).attr("data-id");
                $.ajax({
                    url: "adminpanel/scripts/ajax/navbarajax.php",
                    type: "POST",
                    data: "remove_list_element&id=" + id_link,
                    success: function(data){
                        if (data === "okey")
                            ShowSCErrorBox("okey", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.button_removed")?>");
                    }
                });
                $(this).remove();
            }
        });
        $("#navbar-group-box > div > div > button").on("click", function() {
            if ($("#navbar-remove-btn").hasClass("active")) {
                var id_link = $(this).attr("data-id");
                $.ajax({
                    url: "adminpanel/scripts/ajax/navbarajax.php",
                    type: "POST",
                    data: "remove_list_element&id=" + id_link,
                    success: function(data){
                        if (data === "okey")
                            ShowSCErrorBox("okey", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.button_removed")?>");
                    }
                });
                $(this).parent().remove();
            }
        });
    });



    ////////////////////////////////////////////////////////////////////////////////////////
    $("#btn-operation-container > div").hide();

    $("#navbar-add-btn").on("click", function() {
        $("#btn-operation-container > div").hide();
        $("#btn-operation-container > p").hide();
        $("#create-btn-bar").show();
    });

    $("#navbar-add-list-btn").on("click", function(){
        $("#btn-operation-container > div").hide();
        $("#btn-operation-container > p").hide();
        $("#create-list-bar").show();
    });

    $("#staticc-panel :first-child").show();
    $("#staticc-btn-panel :first-child").addClass("active");

    <?php if ($isEditMode && $editPPerm) { ?>
    $("#staticc-btn-panel > button").removeClass("active");
    $("#staticc-page-edit-btn").addClass("active");
    $("#staticc-panel > div").css("display", "none");
    $("#staticc-page-edit-div").show();
    <?php } ?>

    $("div#staticc-btn-panel > button, button#staticc-page-create-btn").on("click", function() {
        var data = $(this).data("div");
        $("div#staticc-panel > div").hide();
        $("div#" + data).show();
        $("div#staticc-btn-panel").children("button.active").removeClass("active");
        $("button#staticc-page-create-btn").removeClass("active");
        $(this).addClass("active");
    });

    $("button#staticc-search-byname-btn").on("click", function() {
        $("input#staticc-search-type").val("name");
        $("input#staticc-search-input").prop("placeholder", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.page_name")?>");
        $(this).parent("div").children("button").removeClass("active");
        $(this).addClass("active");
    });

    $("button#staticc-search-byauthor-btn").on("click", function() {
        $("input#staticc-search-type").val("author");
        $("input#staticc-search-input").prop("placeholder", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.pages_managment.author_nickname")?>");
        $(this).parent("div").children("button").removeClass("active");
        $(this).addClass("active");
    });

    <?php if ($editSContentPerm) { ?>
    function ShowSCErrorBox(type, message){
        var div = $("div#staticc-content-error-div");
        var span = $("span#staticc-content-error-span");
        switch (type){
            case 1:
            case "okey":
            case "ok":
            case "true":
            case "success":
            case true:
                $(div).text("");
                $(div).prop("class", "alert alert-success");
                $(span).prop("class", "glyphicon glyphicon-ok");
                $(div).append($(span));
                $(div).append(" " + message);
                $(div).show();
                break;
            case 0:
            case "error":
            case "failed":
            case "fail":
            case false:
                $(div).text("");
                $(div).prop("class", "alert alert-danger");
                $(span).prop("class", "glyphicon glyphicon-remove");
                $(div).append($(span));
                $(div).append(" " + message);
                $(div).show();
                break;
            default:
                return false;
        }
        $('html, body').animate({
            scrollTop: $(div).offset().top-100
        }, 1000);
    }
    function HideSCErrorBox(){
        $("div#staticc-content-error-div").hide("slow");
    }

    $("div#staticc-content-btn-panel > button").on("click", function(){
        var data = $(this).data("subpanel-id");
        $("div#staticc-content-container > div").hide();
        $("div#" + data).show();
        $(this).parent("div").children("button").removeClass("active");
        $(this).addClass("active");
    });
    ///////////////////////////////////////////////////////////////////////////
    /// Banner algorythm
    ///////////////////////////////////////////////////////////////////////////
    //First small banner actions
    $("a#staticc-smbanner-first-save").on("click", function(){
        var dataInfo = "savefsb&link=" + $("input#staticc-firstsm-html-input").val();
        $.ajax({
            url: "adminpanel/scripts/ajax/bannersajax.php",
            type: "POST",
            data: dataInfo,
            success: function(data){
                if ($.isNumeric(data) || data === "okey") {
                    ShowSCErrorBox("okey", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.first_banner_saved")?>");
                    $("input#staticc-firstsm-html-input").data("fsbid", data);
                }
                else if (data === "failed")
                    ShowSCErrorBox("fail", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.first_banner_failed")?>");
                else
                    ShowSCErrorBox("fail", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.first_banner_no_html")?>");
            }
        });
    });
    $("a#staticc-smbanner-first-remove").on("click", function(){
        var dataInfo = "removefsb&id=" + $("input#staticc-firstsm-html-input").data("fsbid");
        $.ajax({
            url: "adminpanel/scripts/ajax/bannersajax.php",
            type: "POST",
            data: dataInfo,
            success: function(data){
                if (data === "okey")
                    ShowSCErrorBox("okey", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.first_banner_removed")?>");
                else if (data === "failed")
                    ShowSCErrorBox("fail", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.first_banner_removed_fail")?>");
            }
        });
    });
    $("a#staticc-smbanner-first-clear").on("click", function(){
        $("input#staticc-firstsm-html-input").val("");
    });

    //Second small banner actions
    $("a#staticc-smbanner-second-save").on("click", function(){
        var dataInfo = "savessb&link=" + $("input#staticc-secondsm-html-input").val();
        $.ajax({
            url: "adminpanel/scripts/ajax/bannersajax.php",
            type: "POST",
            data: dataInfo,
            success: function(data){
                if ($.isNumeric(data) || data === "okey") {
                    ShowSCErrorBox("okey", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.second_banner_success")?>");
                    $("input#staticc-secondsm-html-input").data("ssbid", data);
                }
                else if (data === "failed")
                    ShowSCErrorBox("fail", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.second_banner_failed")?>");
                else
                    ShowSCErrorBox("fail", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.second_banner_no_html")?>");
            }
        });
    });
    $("a#staticc-smbanner-second-remove").on("click", function(){
        var dataInfo = "removessb&id=" + $("input#staticc-secondsm-html-input").data("ssbid");
        $.ajax({
            url: "adminpanel/scripts/ajax/bannersajax.php",
            type: "POST",
            data: dataInfo,
            success: function(data){
                if (data === "okey")
                    ShowSCErrorBox("okey", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.second_banner_removed")?>");
                else if (data === "failed")
                    ShowSCErrorBox("fail", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.second_banner_removed_fail")?>");
            }
        });
    });
    $("a#staticc-smbanner-second-clear").on("click", function(){
        $("input#staticc-secondsm-html-input").val("");
    });

    //Big banner edit or create.
    $("button#staticc-create-banner-send-btn").on("click", function() {
        if ($("input#staticc-banner-current-id").val() == ""){
            $.ajax({
                type: "POST",
                url : "adminpanel/scripts/ajax/bannersajax.php",
                data: "addbbaner&banner-name=" + $("input#staticc-create-banner-name-input").val() +
                    "&banner-content=" + $("input#staticc-create-banner-link-input").val() +
                    "&banner-visibility=" + (($("input#staticc-create-banner-visibility-input").is(":checked")) ? 1 : 0),
                success: function (data){
                    if (data === "failed")
                        ShowSCErrorBox("error", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.failed_create_banner")?>");
                    else if (data === "nns")
                        ShowSCErrorBox("error", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.no_banner_name")?>");
                    else if (data === "cns")
                        ShowSCErrorBox("error", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.no_banner_html")?>");
                    else if ($.isNumeric(data)){
                        var button = document.createElement("button");
                        if ($("input#staticc-create-banner-visibility-input").is(":checked"))
                            $(button).prop("class", "btn btn-success");
                        else
                            $(button).prop("class", "btn btn-danger");
                        $(button).text($("input#staticc-create-banner-name-input").val());
                        $(button).prop("type", "button");
                        $(button).attr("data-banner-id", data);
                        $("div#staticc-banner-btns").append(button);
                        ShowSCErrorBox("success", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.banner")?> \"" + $("input#staticc-create-banner-name-input").val() + "\"<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.created_success")?>");
                        $("span#staticc-banners-counter").val($("span#staticc-banners-counter").val()+1);
                        $("button#staticc-create-banner-cancel-btn").click();
                    }
                }
            });
        } else {
            var clicked = $("div#staticc-banner-btns button[data-banner-id=" + $("input#staticc-banner-current-id").val() + "]");
            $.ajax({
                type: "POST",
                url : "adminpanel/scripts/ajax/bannersajax.php",
                data: "editbbaner&banner-id=" + $("input#staticc-banner-current-id").val() +
                    "&banner-name=" + $("input#staticc-create-banner-name-input").val() +
                    "&banner-content=" + $("input#staticc-create-banner-link-input").val() +
                    "&banner-visibility=" + (($("input#staticc-create-banner-visibility-input").is(":checked")) ? 1 : 0),
                success: function (data){
                    if (data === "failed")
                        ShowSCErrorBox("error", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.removed_failed")?>");
                    else if (data === "nns")
                        ShowSCErrorBox("error", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.no_banner_name")?>");
                    else if (data === "cns")
                        ShowSCErrorBox("error", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.no_banner_html")?>");
                    else if (data === "okey"){
                        ShowSCErrorBox("success", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.banner")?> \"" + $("input#staticc-create-banner-name-input").val() + "\" <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.saved_success")?>");
                        if ($("input#staticc-create-banner-visibility-input").is(":checked"))
                            $(clicked).prop("class", "btn btn-success");
                        else
                            $(clicked).prop("class", "btn btn-danger");
                        $(clicked).text($("input#staticc-create-banner-name-input").val());
                        $("button#staticc-create-banner-cancel-btn").click();
                    }
                }
            });
        }
    });

    //Big banner cancel form.
    $("button#staticc-create-banner-cancel-btn").on("click", function() {
        $(this).parent("div").parent("div").children("input[type=text]").val("");
        $(this).parent("div").parent("div").children("input[type=checkbox]").prop("checked", false);
        $(this).parent("div").parent("div").hide("slow");
    });

    //Click on buttons in side frame. Here is handler "Create banner".
    $("div#staticc-banner-btns > button").on("click", function() {
        $("div#staticc-create-banner-div").show("slow");
        var span = $("button#staticc-create-banner-send-btn > span");
        $("button#staticc-create-banner-send-btn").val("");
        if ($(this).data("banner-id") === undefined){
            $("button#staticc-remove-banner-send-btn").hide("slow");
            $("button#staticc-create-banner-send-btn").html("");
            $("button#staticc-create-banner-send-btn").append($(span));
            $("button#staticc-create-banner-send-btn").append(" <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.create_banner")?>");
            $("div#staticc-create-banner-div > input[type=text]").val("");
            $("div#staticc-create-banner-div > input[type=checkbox]").prop("checked", false);
        } else {
            $("button#staticc-remove-banner-send-btn").show("slow");
            $("input#staticc-banner-current-id").val($(this).data("banner-id"));
            $("button#staticc-create-banner-send-btn").html("");
            $("button#staticc-create-banner-send-btn").append($(span));
            $("button#staticc-create-banner-send-btn").append(" <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.edit_banner")?>");
            $("input#staticc-create-banner-name-input").val($(this).text());
            if ($(this).hasClass("btn-success"))
                $("input#staticc-create-banner-visibility-input").prop("checked", true);
            else
                $("input#staticc-create-banner-visibility-input").prop("checked", false);
            $.ajax({
                type: "POST",
                url : "adminpanel/scripts/ajax/bannersajax.php",
                data: "getbbanner&banner-id=" + $("input#staticc-banner-current-id").val(),
                success: function(data){
                    if (data !== "failed") {
                        $("input#staticc-create-banner-link-input").val(data);
                        HideSCErrorBox();
                    } else {
                        ShowSCErrorBox("failed", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.failed_get_html_banner")?>");
                    }
                }
            });
        }
    });

    $("button#staticc-remove-banner-send-btn").on("click", function() {
        $.ajax({
            type: "POST",
            url: "adminpanel/scripts/ajax/bannersajax.php",
            data: "removebbanner&banner-id=" + $("input#staticc-banner-current-id").val(),
            success: function (data){
                if (data === "okey"){
                    ShowSCErrorBox("okey", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.banner")?> \"" + $("input#staticc-create-banner-name-input").val() + "\" <?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.removed_success")?>");
                    $("div#staticc-banner-btns > button[data-banner-id=" + $("input#staticc-banner-current-id").val() + "]").remove();
                    $("span#staticc-banners-counter").val($("span#staticc-banners-counter").val()-1);
                    $("button#staticc-create-banner-cancel-btn").click();
                }
                else if (data === "failed"){
                    ShowSCErrorBox("failed", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.removed_failed")?>");
                }
                else if (data === "bne"){
                    ShowSCErrorBox("failed", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.banner_not_exist")?>");
                }
            }
        });
    });

    ///////////////////////////////////////////////////////////////////////////
    /// Panel algorythm
    ///////////////////////////////////////////////////////////////////////////
    function HideSidePanels(){
        $("div#staticc-left-panel-div").hide();
        $("div#staticc-right-panel-div").hide();
        $("div#staticc-panel-editor-div").hide();
    }
    function EditorClear(){
        $("div#staticc-panel-editor-div input[type=text]").val("");
        $("div#staticc-panel-editor-div input[type=checkbox]").prop("checked", false);
        $("div#staticc-panel-editor-div textarea").val("");
        $("div#staticc-panel-editor-div select").val("left");
        $("div.side-block-header-left").html("");
        $("div.side-block-header-right").html("");
        $("div.side-block-header-left").parent("div").children("div:last-child").html("");
        $("div.side-block-header-right").parent("div").children("div:last-child").html("");
    }
    function RemovePanel(idPanel){
        $.ajax({
            type: "POST",
            url: "adminpanel/scripts/ajax/panelsajax.php",
            data: "deletepanel&panel-id=" + idPanel,
            success: function (data){
                if (data === "pne"){
                    ShowSCErrorBox("failed", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.panel_not_exist")?>");
                }
                else if (data === "failed"){
                    ShowSCErrorBox("failed", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.failed_remove_panel")?>");
                } else if (data === "okey"){
                    ShowSCErrorBox("okey", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.panel_removed_success")?>");
                    HideSidePanels();
                    $("select#staticc-panels-selector > option[value=" + idPanel+ "]").remove();
                    $("select#staticc-panels-selector").val("none");
                }

            }
        });
    }
    HideSidePanels();

    $("select#staticc-panels-selector").on("change", function(){
        if ($(this).val() === "none"){
            HideSidePanels();
            EditorClear();
            HideSCErrorBox();
        } else {
            $("button#staticc-panel-editor-remove-btn").show("slow");
            $("div#staticc-panel-editor-div").show();
            $.ajax({
                type: "POST",
                url: "adminpanel/scripts/ajax/panelsajax.php",
                data: "getpanel&panel-id=" + $("select#staticc-panels-selector").val(),
                success: function(data) {
                    if (data === "pne"){
                        ShowSCErrorBox("failed", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.panel_not_exist2")?>");
                    } else if (data === "failed"){
                        ShowSCErrorBox("failed", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.failed_get_info_panel")?>")
                    } else {
                        if (data !== undefined) {
                            $("span#staticc-panel-editor-send-btn-content").text("<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.save_changes")?>");
                            data = $.parseJSON(data);
                            var side = (data.type == "leftside") ? "left" : "right";
                            $("div#staticc-panel-editor-div").show();
                            $("input#staticc-panel-editor-title").val(data.name);
                            $("div.side-block-header-" + side).html(data.name);
                            $("div.side-block-header-" + side).parent("div").children("div:last-child").html(data.content);
                            $("textarea#staticc-panel-editor-content").val(data.content);
                            $("input[type=checkbox]#staticc-panel-editor-visibility").prop("checked", (data.visibility == 0) ? false : true);
                            $("select#staticc-panel-editor-side").val(side);
                            if (side == "left") {
                                $("div#staticc-left-panel-div").show();
                                $("div#staticc-right-panel-div").hide();
                            } else {
                                $("div#staticc-left-panel-div").hide();
                                $("div#staticc-right-panel-div").show();
                            }
                        }
                    }
                }
            });
        }
    });

    $("#staticc-panels-add").on("click", function() {
        EditorClear();
        HideSidePanels();
        $("#staticc-panels-selector").val("none");
        $("div#staticc-panel-editor-div").show();
        $("div#staticc-left-panel-div").show();
        $("span#staticc-panel-editor-send-btn-content").text("<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.create_panel")?>");
        $("button#staticc-panel-editor-remove-btn").hide("slow");
    });
    $("#staticc-panels-remove").on("click", function() {
        RemovePanel($("select#staticc-panels-selector").val());
    });
    $("input#staticc-panel-editor-title").on("keyup", function (){
        if ($("select#staticc-panel-editor-side").val() == "left"){
            $("div.side-block-header-left").text($(this).val());
        } else {
            $("div.side-block-header-right").text($(this).val());
        }
    });
    $("textarea#staticc-panel-editor-content").on("keyup", function (){
        if ($("select#staticc-panel-editor-side").val() == "left"){
            $("div.side-block-header-left").parent("div").children("div:last-child").html($(this).val());
        } else {
            $("div.side-block-header-right").parent("div").children("div:last-child").html($(this).val());
        }
    });
    $("select#staticc-panel-editor-side").on("change", function(){
        if ($(this).val() == "left") {
            $("div#staticc-left-panel-div").show();
            $("div.side-block-header-left").text($("input[type=text]#staticc-panel-editor-title").val());
            $("div.side-block-header-left").parent("div").children("div:last-child").html($("textarea#staticc-panel-editor-content").val());
            $("div#staticc-right-panel-div").hide();
        } else {
            $("div#staticc-left-panel-div").hide();
            $("div#staticc-right-panel-div").show();
            $("div.side-block-header-right").text($("input[type=text]#staticc-panel-editor-title").val());
            $("div.side-block-header-right").parent("div").children("div:last-child").html($("textarea#staticc-panel-editor-content").val());
        }
    });
    $("button#staticc-panel-editor-send-btn").on("click", function() {
        if ($("select#staticc-panels-selector").val() !== "none"){
            $.ajax({
                type: "POST",
                url: "adminpanel/scripts/ajax/panelsajax.php",
                data: "editpanel&panel-id=" + $("select#staticc-panels-selector").val() +
                    "&panel-name=" + $("input#staticc-panel-editor-title").val() +
                    "&panel-content=" + $("textarea#staticc-panel-editor-content").val() +
                    "&panel-visibility=" + (($("input#staticc-panel-editor-visibility").is(":checked")) ? 1 : 0) +
                    "&panel-side=" + $("select#staticc-panel-editor-side").val(),
                success: function(data){
                    if (data === "pne"){
                        ShowSCErrorBox("failed", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.panel_not_exist")?>");
                    } else if(data === "failed"){
                        ShowSCErrorBox("failed", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.failed_save_changes")?>");
                    } else if (data === "okey"){
                        ShowSCErrorBox("okey", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.changes_saved_success")?>");
                        HideSidePanels();
                        $("select#staticc-panels-selector > option[value=" + $("select#staticc-panels-selector").val() + "]").html("[" + side +"] " + $("input#staticc-panel-editor-title").val());
                        $("select#staticc-panels-selector").val("none");
                    }
                }
            });
        } else {
            $.ajax({
                type: "POST",
                url: "adminpanel/scripts/ajax/panelsajax.php",
                data: "addpanel&panel-name=" + $("input#staticc-panel-editor-title").val() +
                    "&panel-content=" + $("textarea#staticc-panel-editor-content").val() +
                    "&panel-visibility=" + (($("input#staticc-panel-editor-visibility").is(":checked")) ? 1 : 0) +
                    "&panel-side=" + $("select#staticc-panel-editor-side").val(),
                success: function(data){
                    if(data === "failed"){
                        ShowSCErrorBox("failed", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.create_panel_failed")?>");
                    } else if ($.isNumeric(data)){
                        ShowSCErrorBox("okey", "<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.panel")?> \"" + $("input#staticc-panel-editor-title").val() + "\"<?=\Engine\LanguageManager::GetTranslation("staticc_panel.static_editor.js.has_been_created")?>");
                        var newOption = "<option value=\"" + data + "\">["+ side + "] " + $("input#staticc-panel-editor-title").val() + "</option>";
                        $("select#staticc-panels-selector").append(newOption);
                        HideSidePanels();
                    }
                }
            })
        }
    });
    $("button#staticc-panel-editor-erase-btn").on("click", function(){
        EditorClear();
        HideSidePanels();
    });
    $("button#staticc-panel-editor-remove-btn").on("click", function() {
        RemovePanel($("select#staticc-panels-selector").val());
    });
    <?php } ?>

    function popup(){
        var counter = $("table#staticc-pages-table > tbody input[type=checkbox]:checked").length;
        if (counter > 0 ) {
            $("button#staticc-search-remove-btn").prop("disabled", false);
            $("div#staticc-selected-div").show();
            $("div#staticc-selected-div > span").html(counter);
            var idForDeleting = "";
            $("table#staticc-pages-table > tbody input[type=checkbox]:checked").each(function() {
                idForDeleting += $(this).data("spi") + ",";
            });
            idForDeleting = idForDeleting.substring(0, idForDeleting.length-1);
            $("input#staticc-page-delete").val(idForDeleting);
        } else {
            $("button#staticc-search-remove-btn").prop("disabled", true);
            $("div#staticc-selected-div").hide();
            $("div#staticc-selected-div > span").html(counter);
            $("input#staticc-page-delete").val("");
        }
    }

    $("#staticc-table-select-all-checkbox").on("change", function() {
        if ($(this).prop("checked") == true) {
            $("table#staticc-pages-table > tbody input[type=checkbox]").prop("checked", true);
            popup();
        } else {
            $("table#staticc-pages-table > tbody input[type=checkbox]").prop("checked", false);
            popup();
        }
    });

    $("table#staticc-pages-table > tbody input[type=checkbox]").on("change", function(){
        popup();
    });

    <?php if (isset($_REQUEST["reqtype"])){
    switch ($_REQUEST["reqtype"]){
    case 1:
    ?>$("div#staticc-btn-panel > button:nth-child(1)").click();
    <?php break;
    case 2:
    ?>$("div#staticc-btn-panel > button:nth-child(2)").click();
    <?php break;
    case 3:
    ?>$("div#staticc-btn-panel > button:nth-child(3)").click();
    <?php break;
    }
    } ?>

    function insertBBCode(openTag, notNeedClose){
        if ($("#staticc-page-create-div").css("display") == "block") {
            var texter = document.getElementById("staticc-page-create-textarea");
        }
        if ($("#staticc-page-edit-div").css("display") == "block") {
            var texter = document.getElementById("staticc-page-edit-textarea");
        }

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
    }

    $('button[name=bb_b]').click(function(){
        insertBBCode('b', false);
    });
    $('button[name=bb_s]').click(function(){
        insertBBCode('s', false);
    });
    $('button[name=bb_u]').click(function(){
        insertBBCode('u', false);
    });
    $('button[name=bb_i]').click(function(){
        insertBBCode('i', false);
    });
    $('button[name=bb_hr]').click(function(){
        insertBBCode('hr', null);
    });
    $('button[name=bb_ol]').click(function(){
        insertBBCode('ol', false);
    });
    $('button[name=bb_item]').click(function(){
        insertBBCode('*', null);
    });
    $('button[name=bb_a]').click(function(){
        insertBBCode('link', false);
    });
    $('button[name=bb_img]').click(function(){
        insertBBCode('img=', null);
    });
    $('button[name=bb_youtube]').click(function(){
        insertBBCode('youtube=', null);
    });
    $('select[name=bb_size]').on("change",function(){
        insertBBCode('size='+this.options[this.selectedIndex].value, 'size');
    });
    $('select[name=bb_color]').on("change",function(){
        insertBBCode('color='+this.options[this.selectedIndex].value, 'color');
    });
</script>

