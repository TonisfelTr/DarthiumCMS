<?php

namespace Users;

use Engine\DataKeeper;
use Engine\Engine;
use Users\UserAgent;

class Notificator {
    private $userId;
    private $notificationsList = [];
    private $notificationsCount = 0;
    private $notificationsUnreadCount = 0;

    public function __construct($userId) {
        $this->userId = $userId;
        $notifications = DataKeeper::MakeQuery("SELECT * FROM `tt_notifications` WHERE `toUid` = ? ORDER BY `createTime` DESC", [$this->userId], true);

        foreach ($notifications as $notification) {
            $this->notificationsList[] = [
                "id"         => $notification["id"],
                "createTime" => $notification["createTime"],
                "fromUid"    => $notification["fromUid"],
                "type"       => $notification["type"],
                "isRead"     => $notification["isRead"],
                "subject"    => $notification["subject"]
            ];
            $this->notificationsCount++;
            if (!$notification["isRead"]) {
                $this->notificationsUnreadCount++;
            }
        }
    }

    public function getNotifies() {
        return $this->notificationsList;
    }

    public function getNotifiesCount() {
        return $this->notificationsCount;
    }

    public function getNotificationsUnreadCount() {
        return $this->notificationsUnreadCount;
    }

    /** Create a notification.
     * It has the notification code - encrypting notification message.
     * Code list:
     * 1. Added to report discussing. +
     * 2. Have offered to be friends. +
     * 3. Profile change by administrator. +
     * 4. Deleted from report discussing. +
     * 5. New answer in report discussing. +
     * 6. New answer in own topic. +
     * 7. Have liked a topic. +
     * 8. Have moved a topic. +
     * 9. Have deleted a topic. +
     * 10. Have changed text of report. +
     * 11. Have deleted report. +
     * 12. Have edited a topic. +
     * 13. Have changed status of topic. +
     * 14. Somebody has been signed up indicated referrer. +
     * 15. Have closed report. +
     * 16. Your answer in report discussion has been deleted. +
     * 17. Answer in report discussion has been changed. +
     * 18. Foreign added to discusse. +
     * 19. Foreign removed from discusse. +
     * 20. Closed foreign report. +
     * 21. Mentioned in topic. +
     * 22. Mentioned in comment. +
     *
     * @param $notificationCode integer Notification code.
     * @param $fromUid integer User ID by creating the notification.
     * @param $subject integer ID of subject of action.
     * @return bool|int|string
     */
    public function createNotify($notificationCode, $fromUid, int $subject = 0) {
        UserAgent::ClearNotifications();

        return DataKeeper::InsertTo("tt_notifications", ["toUid"      => $this->userId,
            "type"       => $notificationCode,
            "fromUid"    => $fromUid,
            "createTime" => Engine::GetSiteTime(),
            "isRead"     => 0,
            "subject"    => $subject]);
    }

    public function setRead($id) {
        return DataKeeper::Update("tt_notifications", ["isRead" => 1], ["id" => $id]);
    }

    public function getUserId() {
        return $this->userId;
    }
}