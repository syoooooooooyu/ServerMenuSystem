<?php

namespace Meru\ServerMenuSystem;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\form\Form;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

/** これ使う(現段階のコミットじゃまだ) */
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Main extends PluginBase implements Listener {

    /**
     * @var Config
     */
    private $Config;

    public function onEnable() {
        $this->getLogger()->notice('読み込みました。　現在の実行バージョン：' . $this->getDescription()->getVersion());
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->Config = new Config($this->getDataFolder() . "ServerMenuConfig.yml", Config::YAML, array());
        $this->Notice = new Config($this->getDataFolder() . "ServerMenuConfig-notice.yml", Config::YAML, array(
            'Title' => '§a===お知らせ / Information===',
            'Info' => '現在お知らせはありません。',
            'button' => '§c閉じる / プレイに戻る'
        ));
    }

    public function onJoin(PlayerJoinEvent $playerJoinEvent) {
        $player = $playerJoinEvent->getPlayer();
        $player_name = $player->getName();

        $playerJoinEvent->setJoinMessage("§7{$player_name}がログインしました。");

        if($player->isOp()) {
            $playerJoinEvent->setJoinMessage("§7権限者：{$player_name}がログインしました。");
        }

        $player->sendForm(new JoinNoticeForm($this));
    }

    public function onQuit(PlayerQuitEvent $playerQuitEvent): bool {
        $player = $playerQuitEvent->getPlayer();
        $player_name = $player->getName();
        $quit_reason = $playerQuitEvent->getQuitReason();
        /** 退出の原因(理由)を取得 */

        if($quit_reason === 'client disconnect') {
            $playerQuitEvent->setQuitMessage("§7{$player_name}がログアウトしました。(Reason：正常な切断)");
            if($player->isOp()) {
                $playerQuitEvent->setQuitMessage("§7権限者：{$player_name}がログアウトしました。(Reason：正常な切断)");
            }
            return true;
        }
        if($quit_reason === 'timeout') {
            $playerQuitEvent->setQuitMessage("§7{$player_name}がログアウトしました。(Reason：Timeout)");
            if($player->isOp()) {
                $playerQuitEvent->setQuitMessage("§7権限者：{$player_name}がログアウトしました。(Reason：Timeout)");
            }
            return true;
        }
        if($quit_reason === 'Internal server error') {
            $playerQuitEvent->setQuitMessage("§7{$player_name}がログアウトしました。(Reason：Internal server error)");
            if($player->isOp()) {
                $playerQuitEvent->setQuitMessage("§7権限者：{$player_name}がログアウトしました。(Reason：Internal server error)");
            }
            return true;
        }
        return true;
    }
}


class JoinNoticeForm implements Form {

    public function __construct(Main $main) {
        $this->Main = $main;
    }

    public function handleResponse(Player $player, $data): void {
        if($data === null) {
            return;
        }
    }

    public function jsonSerialize() {
        $title = $this->Main->Notice->get('Title');
        $content = $this->Main->Notice->get('Info');
        $button = $this->Main->Notice->get('button');
        return [
            'type' => 'form',
            'title' => $title,
            'content' => $content,
            'buttons' => [
                [
                    'text' => $button
                ]
            ],
        ];
    }
}
