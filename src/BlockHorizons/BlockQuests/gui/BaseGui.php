<?php

namespace BlockHorizons\BlockQuests\gui;

use BlockHorizons\BlockQuests\BlockQuests;
use BlockHorizons\BlockQuests\quests\Quest;
use pocketmine\item\Item;
use pocketmine\Player;

abstract class BaseGui {

	/** @var BlockQuests */
	protected $plugin;

	/** @var string */
	protected $initMessage = "";
	/** @var string */
	protected $finishMessage = "";

	/** @var Player */
	protected $player;
	/** @var Item[] */
	protected $previousContents = [];
	/** @var Item[] */
	protected $defaults = [];
	/** @var int */
	protected $page = 1;

	/** @var Quest */
	private $quest;

	public function __construct(BlockQuests $plugin, Player $player) {
		$this->plugin = $plugin;
		$this->player = $player;
	}

	/**
	 * @return BlockQuests
	 */
	public function getPlugin(): BlockQuests {
		return $this->plugin;
	}

	/**
	 * @return string
	 */
	public function getInitializeMessage(): string {
		return $this->initMessage;
	}

	/**
	 * @return string
	 */
	public function getFinalizeMessage(): string {
		return $this->finishMessage;
	}

	/**
	 * @return Player
	 */
	public function getPlayer(): Player {
		return $this->player;
	}

	/**
	 * @return int
	 */
	public function getPage(): int {
		return $this->page;
	}

	protected function sendInitial() {
		$this->previousContents = $this->player->getInventory()->getContents();
		for($i = 0; $i < $this->player->getInventory()->gethotBarSize(); $i++) {
			$this->player->getInventory()->clear($i);
		}
		foreach($this->defaults["static"] as $slot => $item) {
			$this->player->getInventory()->setItem($slot, $item);
		}
		foreach($this->defaults["dynamic"][0] as $slot => $item) {
			$this->player->getInventory()->setItem($slot, $item);
		}
	}

	/**
	 * @param int $pageNumber
	 *
	 * @return bool
	 */
	public function goToPage(int $pageNumber): bool {
		if($pageNumber < 1 || $pageNumber > (count($this->defaults["dynamic"]) + 1)) {
			return false;
		}
		for($i = 4; $i < 8; $i++) {
			$this->player->getInventory()->clear($i);
		}
		foreach($this->defaults["dynamic"][$pageNumber - 1] as $slot => $item) {
			$this->player->getInventory()->setItem($slot, $item);
		}
		$this->page = $pageNumber;
		return true;
	}

	public function openGui() {
		$this->sendInitial();
		$this->player->sendMessage($this->initMessage);
		$this->getPlugin()->getGuiHandler()->setUsingGui($this->player, true, $this);
	}

	/**
	 * @param bool $cancelled
	 */
	public function closeGui(bool $cancelled = true) {
		$this->player->getInventory()->setContents($this->previousContents);

		if(!$cancelled && isset($this->quest)) {
			$this->quest->store();
		}
	}

	/**
	 * @param Item  $item
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function callBackGuiItem(Item $item, $value): bool {
		if(!isset($this->quest)) {
			$this->quest = new Quest();
		}
		if($item->getNamedTag()->bqGuiInputMode->getValue() === "") {
			return false;
		}
		$this->quest->{$item->getNamedTag()->bqGuiInputMode->getValue()} = $value;
		return true;
	}
}