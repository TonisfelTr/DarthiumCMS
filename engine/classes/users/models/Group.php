<?php

namespace Users\Models;

use Engine\DataKeeper;

class Group {
    private int $gId;
    private string $gName;
    private string $gColor;
    private string $gDescript;
    private array $gPerms;

    public function __construct($groupId) {
        $result = DataKeeper::Get("tt_groups", ["*"], ["id" => $groupId])[0];
        $this->gId = $groupId;
        $this->gName = $result["name"];
        $this->gColor = $result["color"];
        $this->gDescript = $result["descript"];
        $this->gPerms = [
            'enterpanel'             => $result["enterpanel"],
            'change_engine_settings' => $result["change_engine_settings"],
            'offline_visiter'        => $result["offline_visiter"],
            'rules_edit'             => $result["rules_edit"],
            'change_template_design' => $result["change_template_design"],

            /***********************************************************
             * Group permissions.                                      *
             ***********************************************************/

            'change_perms' => $result["change_perms"],
            'group_create' => $result["group_create"],
            'group_delete' => $result["group_delete"],
            'group_change' => $result["group_change"],

            /************************************************************
             * User permissions.                                        *
             ************************************************************/

            'change_another_profiles' => $result["change_another_profiles"],
            'change_user_group'       => $result["change_user_group"],
            'user_add'                => $result["user_add"],
            'user_remove'             => $result["user_remove"],
            'user_see_foreign'        => $result["user_see_foreign"],
            'user_signs'              => $result["user_signs"],
            'change_profile'          => $result["change_profile"],
            'user_ban'                => $result["user_ban"],
            'user_unban'              => $result["user_unban"],
            'user_banip'              => $result["user_banip"],
            'user_unbanip'            => $result["user_unbanip"],

            /*************************************************************
             * Reports permissions                                       *
             *************************************************************/

            'report_create'              => $result["report_create"],
            'report_foreign_remove'      => $result["report_foreign_remove"],
            'report_talking'             => $result["report_talking"],
            'report_remove'              => $result["report_remove"],
            'report_edit'                => $result["report_edit"],
            'report_foreign_edit'        => $result["report_foreign_remove"],
            'report_answer_edit'         => $result["report_answer_edit"],
            'report_foreign_answer_edit' => $result["report_foreign_answer_edit"],
            'report_close'               => $result["report_close"],

            /*************************************************************
             * Uploading permissions                                     *
             *************************************************************/

            'upload_add'            => $result["upload_add"],
            'upload_delete'         => $result["upload_delete"],
            'upload_delete_foreign' => $result["upload_delete_foreign"],
            'upload_see_all'        => $result["upload_see_all"],

            /*************************************************************
             * Categories permissions                                    *
             *************************************************************/

            'category_create'        => $result["category_create"],
            'category_delete'        => $result["category_delete"],
            'category_edit'          => $result["category_edit"],
            'category_see_unpublic'  => $result["category_see_unpublic"],
            'category_params_ignore' => $result["category_params_ignore"],

            /*************************************************************
             * Topics permissions                                        *
             *************************************************************/

            'topic_create'         => $result["topic_create"],
            'topic_edit'           => $result["topic_edit"],
            'topic_foreign_edit'   => $result["topic_foreign_edit"],
            'topic_delete'         => $result["topic_delete"],
            'topic_foreign_delete' => $result["topic_foreign_delete"],
            'topic_manage'         => $result["topic_manage"],

            /*************************************************************
             * Comments permissions                                      *
             *************************************************************/

            'comment_create'         => $result["comment_create"],
            'comment_edit'           => $result["comment_edit"],
            'comment_foreign_edit'   => $result["comment_foreign_edit"],
            'comment_delete'         => $result["comment_delete"],
            'comment_foreign_delete' => $result["comment_foreign_delete"],

            /**************************************************************
             * Permissions manage with static content              *
             **************************************************************/

            'sc_create_pages' => $result["sc_create_pages"],
            'sc_edit_pages'   => $result["sc_edit_pages"],
            'sc_remove_pages' => $result["sc_remove_pages"],
            'sc_design_edit'  => $result["sc_design_edit"],

            /**************************************************************
             * Other                                                      *
             **************************************************************/

            'bmail_sende'     => $result["bmail_sende"],
            'bmail_sends'     => $result["bmail_sends"],
            'logs_see'        => $result["logs_see"],
            'plugins_control' => $result["plugins_control"]
        ];

        return $this;
    }

    public function getPermission($permValue) : bool {
        return (bool)$this->gPerms[$permValue];
    }

    public function getName() : string {
        return $this->gName;
    }

    public function getColor() : string {
        return $this->gColor;
    }

    public function getDescript() : string {
        return $this->gDescript;
    }

    public function getId() : string {
        return $this->gId;
    }
}