# Genisys (创世纪)

### Advanced Minecraft: Pocket Edition Server

Chat on Mattermost: [Join](https://mattermost.itxtech.org/itxtechnologies/channels/genisys)  
Chat on Gitter: [![Gitter](https://img.shields.io/gitter/room/iTXTech/Genisys.svg)](https://gitter.im/iTXTech/Genisys?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)  
IRC: #genisys at freenode

Special thanks to [JetBrains](https://www.jetbrains.com) for providing free license of PHPStorm.

### Build status
Travis-CI: [![Travis-CI](https://img.shields.io/travis/iTXTech/Genisys/master.svg)](https://travis-ci.org/iTXTech/Genisys)  
GitLab CI: [![GitLab CI](https://gitlab.com/itxtech/genisys/badges/master/build.svg)](https://gitlab.com/itxtech/genisys/pipelines?scope=branches)

### Downloads
You can get a prebuilt phar from [GitLab](https://gitlab.com/itxtech/genisys/pipelines?scope=branches).

**The master branch is the only officially supported branch.**
_All other branches are in testing and may be unstable. Do not use builds from other branches unless you are sure you understand the risks._

### Installation
Installation instructions can be found in the [wiki](https://github.com/iTXTech/Genisys/wiki).

## Contents

* [English Version](#english-version)
* [中文版本](#中文版本)

## English Version

* One Core to rule anything
* This core is an unofficial version of PocketMine-MP modified by DREAM STUDIO and iTX Technologies LLC.
* Genisys is only a fork of PocketMine-MP and all original code was written by the [PocketMine Team](https://github.com/PocketMine).
* Feel free to create a Pull Request or open an Issue. English and Chinese are both welcome. Communication in English is recommended.

### Acknowledgements
* Some features are merged from **@boybook**'s **FCCore**
* Skull, FlowerPot are based on ImagicalMine's work
* AIs are based on **@Zzm317**'s amazing MyOwnWorld.
* Painting and Brewing Stand are translated from [Nukkit Project](https://github.com/Nukkit/Nukkit)
* Furnace was fixed by **@MagicDroidX**
* Rail and Powered Rails were written by **@happy163**
* Nether door was written by **JJLeo**
* Base food system is based on **Katana**
* Base weather system was written by **@Zzm317** and rewritten by **@PeratX**
* **@FENGberd**'s encouragement
* Our leaders are **@ishitatsuyuki** and **@jasonczc**

### License
Most of the code in this repository was written by the PocketMine team and is licensed under GPLv3.

**NOTE:** Some AI is based on **proprietary code** from **@Zzm317**'s MyOwnWorld (used by permission of the author). Copying of such code is prohibited without the original author's approval.

### Official Development Documentation
[Genisys Official Development Documentation Page](http://docs.mcper.cn/en-US/)

### Features
* Weather
* Experience
* Basic redstone functionality (not all components are available or implemented currently.)
* Nether
* Rails
* More Effects
* Potions, Splash Potions and Brewing
* Better Crafting
* Hunger (Based on Katana)
* AI (Based on MOW)
* FolderPluginLoader
* Monster Spawner
* Item Frame
* Colourful Sheep
* Multiple types of Boat, Villager and Rabbit
* Enchantment effects

NOTE: Please edit **genisys.yml** to enable the desired features, such as Redstone, MobAI, Nether, etc.

### To-Do List
* Improve Redstone
* Fishing
* New AI for all creatures
* Rewrite World Generator (Generate mobs)
* LevelDB support for Windows

### Servers
The following are the test servers built by us. Keep in mind that the following are test servers.

#### Beer MC (A mini-game server)
Address: beermc.com  
Port: 19132

## 中文版本

* 一个核心统治一切。
* 此内核为 PocketMine-MP 的非官方版，由 轻梦工作室 与 iTX Tech 联合优化。
* 创世纪 仅为 PocketMine-MP 项目的分支，PocketMine-MP 所有原始代码均由 PocketMine 小组编写。
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
* 完善 红石系统
* 加入 钓鱼
* 用于所有生物的新 AI
* 重写世界生成器（生成生物）
* Windows 的 LevelDB 支持


### 服务器
（以下是我们个人搭建的服务器，供测试参观。事实上，其他许多使用 Genisys 搭建的服务器以及其维护水平可能比我们更专业、高效。）

#### BeerMc 小游戏
地址: beermc.com  
端口: 19132
