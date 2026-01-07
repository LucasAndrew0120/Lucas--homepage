这是一个非常棒的个人主页设计！它采用了现代的**玻璃拟态（Glassmorphism）**风格，结合了实时监控、天气、一言（Hitokoto）等功能。

根据你提供的代码，我为你撰写了一份详细的 `README.md`。它涵盖了项目简介、功能亮点、快速部署以及自定义指南。

---

# 📝 Lucas 的忘忧斋 - 个人导航页

这是一个基于 **HTML/CSS/JS** 开发的简约而不失精致的个人导航主页。采用响应式设计，完美适配 PC 和移动端，并深度集成了系统状态监控与多种实用组件。
<p align="center">
  <img src="https://img.shields.io/badge/Server-1Panel-orange?style=flat-square" alt="1Panel">
  <img src="https://img.shields.io/badge/Web-OpenResty-009900?style=flat-square&logo=nginx" alt="OpenResty">
  <img src="https://img.shields.io/badge/Crafted%20with-Gemini%20AI-4285f4?style=flat-square&logo=google-gemini&logoColor=white" alt="Gemini AI">
  <img src="https://img.shields.io/badge/Stats-Vercount-0078d7?style=flat-square" alt="Vercount">
</p>

## ✨ 功能亮点

* 🧊 **玻璃拟态设计**：基于高性能的高斯模糊背景（Backdrop Filter）与深色调视觉，呈现通透的质感。
* 📊 **实时系统监控**：通过可视化进度条展示 CPU、内存、磁盘负载及服务器续费倒计时。
* 🌡️ **智能天气组件**：集成高德地图 IP 定位与和风天气 API，自动识别城市并显示实时气温与天气状况。
* 📜 **动态一言**：自动抓取 Hitokoto 接口，每次刷新都能邂逅一段不一样的文字。
* 🔗 **快捷入口**：网格化管理的常用导航链接，支持悬停动效。
* 📅 **运行时间统计**：自动计算网站已稳定运行的天数。
* 📱 **响应式布局**：完美适配不同尺寸的屏幕设备。

---

## 🚀 快速开始

### 1. 克隆仓库

```bash
git clone https://github.com/LucasAndrew0120/Lucas--html-of-web.git

```

### 2. 配置 API Key

为了让天气功能正常工作，请在 `index.html` 的 `<script>` 部分替换为你自己的 API 密钥：

* **和风天气 (qweatherKey)**: [控制台申请](https://dev.qweather.com/)
* **高德地图 (amapKey)**: [控制台申请](https://console.amap.com/)

### 3. 系统监控后端 (可选)

前端代码中通过 `fetch('status.php')` 获取服务器状态。你需要一个简单的 PHP 环境来运行配套的 `status.php`（或自行实现类似的 JSON 接口）。

---

## 🛠️ 文件结构说明

```text
├── index.html      # 主页面代码（样式、结构、逻辑合一）
├── status.php      # (需自备) 接口：返回服务器 CPU/RAM/磁盘/带宽等 JSON 数据
└── ...

```

---

## 🎨 自定义指南

* **更换背景图**：修改 `body` 选择器下的 `background: url('...')` 地址。
* **修改个人信息**：定位到 `.profile` 类，修改头像链接、名字及简介（Bio）。
* **调整配色**：修改 `:root` 变量中的 `--accent-color`（强调色）和 `--glass-bg`（背景透明度）。
* **更新导航链接**：在 `.link-grid` 容器内添加或修改 `<a>` 标签。

---

## 🛡️ 开源协议

本项目遵循 **MIT** 开源协议。你可以自由地使用、修改及分发。

---

## 🤝 致谢

* **AI 支持**：感谢 Gemini AI 协助编写核心代码逻辑。
* **字体支持**：[霞鹜文楷 (LXGW WenKai)](https://github.com/lxgw/LxgwWenKai)。
* **图标库**：Font Awesome。
* **API 提供**：和风天气、高德地图、Hitokoto、Vercount。

---

**👋 喜欢这个项目吗？如果对你有帮助，请给一个 Star！**

---
