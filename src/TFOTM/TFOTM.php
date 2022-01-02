<?php

namespace TFOTM;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;


class TFOTM extends PluginBase implements Listener{

public $MV = 0;

public function onEnable(){
	global $MV;
	$MV = rand(1, 1000);
	if(!file_exists($this->getDataFolder())){
    mkdir($this->getDataFolder(), 0744, true);
    $this->config = new Config($this->getDataFolder()."Date.json", Config::JSON);
	if($this->config->exists("@SERVER-NUM")){
   		//ある
	}else{
    	$this->config->set("@SERVER-NUM", "0");
    	$this->config->save();
    	$this->config->set("@SERVER-B", "0");
    	$this->config->save();
    	$this->config->set("@SERVER-S", "0");
    	$this->config->save();
    }
	}
    $this->config = new Config($this->getDataFolder()."Date.json", Config::JSON);
	$this->getServer()->getPluginManager()->registerEvents($this, $this);
	$this->PocketMoney = $this->getServer()->getPluginManager()->getPlugin("PocketMoney");
	$this->getServer()->getScheduler()->scheduleRepeatingTask(new MV($this), 1200);
	$this->getLogger()->info(TextFormat::GOLD."TFOTMシステムが有効化されました");
}
	
public function onCommand(CommandSender $sender, Command $command, $label, array $args){
	if($command->getName() == "bas"){
		$subCommand = strtolower(array_shift($args));
		global $MV;
		$PlayerName = $sender->getName();
		switch ($subCommand){

		case "":
		case "help":
			$sender->sendMessage("§bTFOTMプラグインコマンド一覧とその使い方");
			$sender->sendMessage(" -TFOTMプラグインとは何か§6>§f/bas about");
			$sender->sendMessage(" -TFOTMのサーバー統計を確認する§6>§f/bas stats");
			$sender->sendMessage(" -売買券の時価を確認する§6>§f/bas mv");
			$sender->sendMessage(" -売買券の所持数を確認する§6>§f/bas check");
			$sender->sendMessage(" -売買券を購入する§6>§f/bas buy");
			$sender->sendMessage(" -売買券を売却する§6>§f/bas sell");
		break;

		case "about":
			$sender->sendMessage("§e～§bTFOTM§fプラグインとは何か§e～");
			$sender->sendMessage("TFOTMは「The fate of the money」の略で日本語に訳すと「お金の運命」です");
			$sender->sendMessage("このプラグインは1分毎に「売買券」と呼ばれる株券なようなものの時価が1PM～1000PMの間で決定されます");
			$sender->sendMessage("売買権の時価が安い時に券を購入し、時価が高い時に売買権を売却すれば儲けることが出来ます");
			$sender->sendMessage("逆に、売買権を購入した時の時価よりも、売却するときの時価が低い場合、それは損になります");
			$sender->sendMessage("「得」にお金が使われるのか、または「損」に使われるのかは「時価」次第なのでこんなプラグイン名が付きました");
			$sender->sendMessage("解説終わり");
		break;

		case "stats":
			$NumOld = $this->config->get("@SERVER-NUM");
			$Num = intval($NumOld);
			$bOld = $this->config->get("@SERVER-B");
			$B = intval($bOld);
			$sOld = $this->config->get("@SERVER-S");
			$S = intval($sOld);

			$sender->sendMessage("===§bTFOTMサーバー統計§f===");
			$sender->sendMessage(" -今までに購入された売買券の枚数： ".$Num."枚");
			$sender->sendMessage(" -売買券購入による収入の合計： ".$B."PM");
			$sender->sendMessage(" -売買券売却による支出の合計： ".$S."PM");
		break;

		case "mv":
			$sender->sendMessage("§bTFOTM売買券§f 現在の時価は§b".$MV."PM§fです");
		break;

		case "buy":
			$Money = $this->PocketMoney->getMoney($PlayerName);
			if($Money < $MV){
				$sender->sendMessage("§cお金が不足しているため売買券を購入できません");
			}else{
				$NumOld = $this->config->get($PlayerName);
				$Num = intval($NumOld);
				$Num++;
				$this->config->set($PlayerName, $Num);
				$this->config->save();

				$NumOld = $this->config->get("@SERVER-NUM");
				$Num = intval($NumOld);
				$Num++;
				$this->config->set("@SERVER-NUM", $Num);
				$this->config->save();

				$bOld = $this->config->get("@SERVER-B");
				$B = intval($bOld);
				$BC = $B + $MV;
				$this->config->set("@SERVER-B", $BC);
				$this->config->save();

				$this->PocketMoney->grantMoney($PlayerName, -$MV);
				$sender->sendMessage("§a".$MV."PM§fで§6売買券§fを購入しました！");
			}
		break;

		case "sell":
			$sOld = $this->config->get($PlayerName);
			$S = intval($sOld);
			if($S == 0){
				$sender->sendMessage("§c貴方は売買券を所持していません");
			}else{
				$NumOld = $this->config->get($PlayerName);
				$Num = intval($NumOld);
				$Num--;
				$this->config->set($PlayerName, $Num);
				$this->config->save();

				$sOld = $this->config->get("@SERVER-S");
				$S = intval($sOld);
				$SC = $S + $MV;
				$this->config->set("@SERVER-S", $SC);
				$this->config->save();

				$this->PocketMoney->grantMoney($PlayerName, $MV);
				$sender->sendMessage("§a".$MV."PM§fで§6売買券§fを売却しました！");
			}
		break;

		case "check":
			$DateOLD = $this->config->get($PlayerName);
			$Date = intval($DateOLD);
			$sender->sendMessage("貴方は現在§6".$Date."枚§fの§b売買券§fを所持しています");
		break;

		}
	}
}

public function onJoin(PlayerJoinEvent $event){
	$PlayerName = $event->getPlayer()->getName();
	if($this->config->exists($PlayerName)){
    	//ある
	}else{
    	$this->config->set($PlayerName, 0);
		$this->config->save();
	}
}
}

class MV extends PluginTask{
	public function __construct(PluginBase $owner){
		parent::__construct($owner);
	}
	
	public function onRun($tick){
		global $MV;
		$MV = rand(1, 1000);
	}
}