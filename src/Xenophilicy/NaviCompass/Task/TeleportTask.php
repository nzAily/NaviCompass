<?php
# MADE BY:
#  __    __                                          __        __  __  __
# /  |  /  |                                        /  |      /  |/  |/  |
# $$ |  $$ |  ______   _______    ______    ______  $$ |____  $$/ $$ |$$/   _______  __    __
# $$  \/$$/  /      \ /       \  /      \  /      \ $$      \ /  |$$ |/  | /       |/  |  /  |
#  $$  $$<  /$$$$$$  |$$$$$$$  |/$$$$$$  |/$$$$$$  |$$$$$$$  |$$ |$$ |$$ |/$$$$$$$/ $$ |  $$ |
#   $$$$  \ $$    $$ |$$ |  $$ |$$ |  $$ |$$ |  $$ |$$ |  $$ |$$ |$$ |$$ |$$ |      $$ |  $$ |
#  $$ /$$  |$$$$$$$$/ $$ |  $$ |$$ \__$$ |$$ |__$$ |$$ |  $$ |$$ |$$ |$$ |$$ \_____ $$ \__$$ |
# $$ |  $$ |$$       |$$ |  $$ |$$    $$/ $$    $$/ $$ |  $$ |$$ |$$ |$$ |$$       |$$    $$ |
# $$/   $$/  $$$$$$$/ $$/   $$/  $$$$$$/  $$$$$$$/  $$/   $$/ $$/ $$/ $$/  $$$$$$$/  $$$$$$$ |
#                                         $$ |                                      /  \__$$ |
#                                         $$ |                                      $$    $$/
#                                         $$/                                        $$$$$$/

namespace Xenophilicy\NaviCompass\Task;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use Xenophilicy\NaviCompass\NaviCompass;

/**
 * Class TeleportTask
 * @package Xenophilicy\NaviCompass\Task
 */
class TeleportTask extends Task {

	private NaviCompass $plugin;
	private string $cmdString;
	private Player $player;
	private bool $waterdog;

	/**
	 * TeleportTask constructor.
	 * @param NaviCompass $plugin
	 * @param string $cmdString
	 * @param Player $player
	 * @param bool $waterdog
	 */
	public function __construct(NaviCompass $plugin, string $cmdString, Player $player, bool $waterdog = false) {
		$this->plugin = $plugin;
		$this->cmdString = $cmdString;
		$this->player = $player;
		$this->waterdog = $waterdog;
	}

	public function onRun(): void {
		if(!$this->player->isConnected()) {
			$this->plugin->getLogger()->warning("TeleportTask: Player {$this->player->getName()} is not connected, teleport aborted.");
			return;
		}

		if($this->waterdog) {
			if($this->player instanceof Player && $this->player->isConnected()) {
				$pk = TransferPacket::create($this->cmdString, 19132, false);
				$this->player->getNetworkSession()->sendDataPacket($pk);
			} else {
				$this->plugin->getLogger()->warning("TeleportTask: Player {$this->player->getName()} is not online, cannot transfer.");
			}
			return;
		}

		if(strtolower(NaviCompass::$settings["World-CMD-Mode"]) == "player") {
			if($this->player instanceof Player && $this->player->isConnected()) {
				$this->plugin->getServer()->getCommandMap()->dispatch($this->player, $this->cmdString);
			} else {
				$this->plugin->getLogger()->warning("TeleportTask: Player {$this->player->getName()} is not online, cannot execute command.");
			}
		} else if(strtolower(NaviCompass::$settings["World-CMD-Mode"]) == "console") {
			$this->plugin->getServer()->getCommandMap()->dispatch(
				new ConsoleCommandSender($this->plugin->getServer(), $this->plugin->getServer()->getLanguage()),
				$this->cmdString
			);
		}
	}

}

