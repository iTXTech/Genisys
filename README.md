# Genisys (创世纪) - Advanced Minecraft: Pocket Edition Server

Chat on Gitter: [![Gitter](https://img.shields.io/gitter/room/iTXTech/Genisys.svg)](https://gitter.im/iTXTech/Genisys?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)  
You can also join `#genisys` on freenode IRC.

Special thanks to [JetBrains](https://www.jetbrains.com) for providing free edition of PHPStorm.

### Build status
Jenkins: [![Jenkins](https://img.shields.io/jenkins/s/http/jenkins.mcper.cn/Genisys-master.svg)](https://jenkins.mcper.cn/job/Genisys-master/)  
Travis-CI: [![Travis-CI](https://img.shields.io/travis/iTXTech/Genisys/master.svg)](https://travis-ci.org/iTXTech/Genisys)  
GitLab CI: [![GitLab CI](https://gitlab.com/itxtech/genisys/badges/master/build.svg)](https://gitlab.com/itxtech/genisys/builds)

### Downloads
You can get prebuilt phar from [Jenkins](https://jenkins.mcper.cn/job/Genisys-master/) or [GitLab](https://gitlab.com/itxtech/genisys/builds).

### Installation
If you are on Linux, use Docker to install Genisys.

[![Docker Pulls](https://img.shields.io/docker/pulls/itxtech/docker-env-genisys.svg)](https://hub.docker.com/r/itxtech/docker-env-genisys/)  
See [wiki](https://github.com/iTXTech/Genisys/wiki/Use-Docker-to-run-Genisys) for more details.

For other platform, see below.

## Contents

* [English Version](#english-version)
* [中文版本](#中文版本)

## English Version

* One Core to rule anything
* This core is an unofficial version of PocketMine-MP modified by DREAM STUDIO and iTX Technologies LLC.
* Genisys is only a fork of PocketMine-MP and all original codes are written by PocketMine Team.
* [Download php7 for Genisys](https://github.com/iTXTech/PHP-Genisys/)
* [Download Genisys Installer for Windows](https://raw.githubusercontent.com/iTXTech/Genisys-Installer/master/setup.exe)
* Feel free to create a Pull Request or open an Issue. English and Chinese are both welcome. Use English to communicate with more people.

### Acknowledgements
* Some features are merged from **@boybook**'s **FCCore**
* Skull, FlowerPot are based on ImagicalMine's work
* AIs are based on **@Zzm317**'s amazing MyOwnWorld.
* Painting and Brewing Stand are translated from Nukkit Project
* Furnace was fixed by **@MagicDroidX**
* Rail and Powered Rails were written by **@happy163**
* Nether door was written by **JJLeo**
* Base food system is based on **Katana**
* Base weather system was written by **@Zzm317** and rewritten by **@PeratX**
* **@FENGberd**'s encouragement
* Our leaders are **@ishitatsuyuki** and **@jasonczc**

### License
Most codes are made by PocketMine team and licensed under GPLv3. Some AI is proprietary, copy is prohibited.

### Official Development Documentation
[Genisys Official Development Documentation Page](http://docs.mcper.cn/en-US/)

### Features
* Performance optimization (Let 100+ players join in a server)
* Bug fix in PocketMine-MP
* Weather
* Experience
* More Effects
* Redstone (Button, Lever, Pressure Plate, Redstone Wire, Redstone Torch and more)
* Nether (Red sky!)
* Rail & Powered Rail
* Minecart (doesn't follow rails)
* Boat
* More Doors
* Potions
* Splash Potions
* Anvil
* Better Crafting
* Better Inventory
* More Items
* Hunger (Based on Katana)
* AI (Based on MOW)
* More commands
  - bancid
  - bancidbyname
  - banipbyname
  - ms
  - extractplugin
  - makeplugin
  - pardoncid
  - weather
  - extractphar
  - loadplugin
  - lvdat
  - biome
  - xp
  - setblock
  - fill
  - summon
* FolderPluginLoader
* Monster Spawner
* Item Frame
* Dispenser and Dropper
* Colorful Sheep
* Multiple types of Boat, Villager and Rabbit
* Enchantment
* Brewing
* Enchantment effects
* NOTICE: Please edit **genisys.yml** to enable all the features, including Redstone, MobAI, Nether and so on.

### To-Do List
* Improve Potions
* Improve Redstone
* Fishing
* New AI for all creatures
* LevelDB support for Windows

### Servers
(Follows are the test servers built by us. In fact other servers used Genisys may be more professional than ours.)

#### Beer MC (A mini-game server)
Address: beermc.com  
Port: 19132

## 中文版本

* 一个核心统治一切。
* 此内核为 PocketMine-MP 的非官方版，由 轻梦工作室 与 iTX Tech 联合优化。
* 创世纪 仅为 PocketMine-MP 项目的分支，PocketMine-MP 所有原始代码均由 PocketMine 小组编写。
* [点击获取 php7](https://github.com/iTXTech/PHP-Genisys/tree/master/php7)
* [点击获取 Genisys Installer for Windows](https://raw.githubusercontent.com/iTXTech/Genisys-Installer/master/setup.exe)
* 欢迎创建 Pull Request。请使用中文或者英文进行交流（但为了交流方便，请尽量使用英文进行交流）。

### 鸣谢
* 一些功能来自 **@boybook** 的 **FCCore**
* 头颅、花盆的相关代码由 ImagicalMine 编写；
* 生物 AI 的相关代码基于 **@Zzm317**  令人惊奇的 MyOwnWorld 编写；
* 画、酿造台的相关代码从 Nukkit 项目重写；
* 熔炉的相关代码及其 Bug 由 **@MagicDroidX** 编写与修复；
* 铁轨、充能铁轨的相关代码由 **@happy163** 编写；
* 地狱门的相关代码由 **JJLeo** 编写；
* 饥饿系统的相关代码基于 **Katana** 的代码编写；
* 基本天气系统的相关代码由 **@Zzm317** 编写，由 **@PeratX** 重写；
* 感谢 **@FENGberd** 的支持与鼓励；
* 我们的项目负责人为 **@ishitatsuyuki** 及 **@jasonczc**。

### 开发者文档
[点击这里进入开发者文档](http://docs.mcper.cn/zh-CN/)

### 特性
* 性能提升（允许 100+ 的玩家加入服务器）
* 修复 PocketMine-MP 的 Bug
* 天气系统
* 经验系统
* 更多的（药水）效果
* 红石系统（按钮、拉杆、压力板、红石（线）、红石火把，更多待添加）
* 地狱（红色的天空！）
* 铁轨、充能铁轨
* 矿车（暂时还不能在轨道上运行）
* 船
* 更多的门
* 药水
* 喷溅型药水
* 铁毡
* 更好的合成
* 更好的物品栏
* 更多的物品
* 饥饿系统（基于 Katana 的代码）
* 生物 AI（基于 MOW 的代码）
* 更多的指令
  - bancid（按设备编号或玩家 ID）
  - banip（按 IP 或玩家 ID）
  - ms
  - DevTools 相关指令（打包与解包插件）
  - pardoncid
  - weather
  - loadplugin
  - lvdat
  - xp
  - setblock
  - fill
  - summon
* 文件夹插件加载器
* 刷怪箱
* 物品展示柜
* 发射器和投掷器
* 五彩缤纷的羊
* 不同种类的船，村民和兔子
* 原版附魔
* 酿造
* 附魔效果
* 注意: 请编辑 **genisys.yml** 来启用红石、生物AI和地狱等功能。

### 计划表
* 完善 药水
* 完善 红石系统
* 加入 钓鱼
* 用于所有生物的新 AI
* Windows 的 LevelDB 支持


### 服务器
（以下是我们个人搭建的服务器，供测试参观。事实上，其他许多使用 Genisys 搭建的服务器以及其维护水平可能比我们更专业、高效。）

#### BeerMc 小游戏
地址: beermc.com  
端口: 19132
