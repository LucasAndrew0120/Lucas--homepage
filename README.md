![首页截图](https://pic1.imgdb.cn/item/698809b442b1cbeca1faa8cf.png)

<h2 align="center">
  Lucas的忘忧斋——纯静态个人主页
</h2>

无需 PHP、Node 或任何服务端程序，上传三个静态文件即可运行。所有动态数据（天气、一言、说说、服务器状态、GitHub 贡献图）均由浏览器直接请求公开 API 获取。

## 功能介绍
- ☑️基础的个人信息（昵称 签名 社交矩阵）
- ☑️集成和风天气和高德地图API，展示用户所在地天气
- ☑️接入Busuanzi和Umami访客统计（Busuanzi已使用国内接口）
- ☑️接入一言CDN（支持来源出处显示）
- ☑️接入Ech0说说
- ☑️服务器状态监测（浏览器直连 Komari `/api`，无需服务端反代）
- ☑️快捷卡片，服务分流
- ☑️GitHub 贡献图（前端请求公共 API + localStorage 缓存，浏览器内生成 SVG）
- ☑️自定义页脚（Shield徽章 签名 网站上线时间等等）
- ☑️看板布局管理（一言/说说/监控/快捷入口/GitHub图 自由开关）
- ☑️API 调试日志（浏览器控制台查看各接口获取状态）
- ☑️字体大小可配置（名字/简介/一言/页面缩放）
- ☑️移动端适配（贡献图支持点按查看提交数）

## 目录结构

```
homepage/
├── index.html          # 页面结构 + 样式 + 全部脚本（单文件）
├── config.js           # 个人配置（含 API Key，已被 .gitignore 忽略，请勿提交）
├── config.example.js   # 配置模板（仅 main 分支提供）
├── 404.html            # 404 页面
└── LICENSE
```

## 快速开始

```bash
git clone https://github.com/LucasAndrew0120/Lucas--homepage.git
cd Lucas--homepage
cp config.example.js config.js     # 然后按注释填写
```

本地预览（不能用 `file://` 直接打开，`fetch` 会被浏览器拦截）：

```bash
py -3 -m http.server 8000     # Windows
python3 -m http.server 8000   # macOS / Linux
```

打开 http://localhost:8000 即可。

## 部署办法
- fork 本项目
- 进入 **config.example.js**，根据注释填入必要数据
- 将 **config.example.js** 重命名为 **config.js**
- 只需上传 **index.html**、**404.html**、**config.js** 三个文件到任意静态托管平台（GitHub Pages、Cloudflare Pages、Vercel、Netlify、EdgeOne、1Panel/OpenResty 站点根目录等）
- 1Panel 站点目录通常为 `/opt/1panel/www/sites/<你的域名>/index`
- 若使用 Komari 系统监控：**必须**在 Komari 后台把本站域名加入允许跨域列表（见下方说明），否则状态面板不会显示数据

## 配置项说明

`config.js` 中最常用的几个字段：

| 字段 | 说明 |
| --- | --- |
| `github_username` / `github_url` | GitHub 用户名与主页（`github_url` 留空时自动用用户名拼接） |
| `email_url` | 邮箱链接，如 `mailto:you@example.com` |
| `qweatherKey` / `amapKey` | 和风天气 / 高德地图 Key，缺一不可，否则天气模块不加载 |
| `echoApiUrl` | Ech0 说说 API 地址 |
| `komariApiUrl` | Komari 站点地址，如 `https://server.example.com`（**不要**写到 `/api` 结尾） |
| `komariNodeUuid` | 要展示的节点 UUID，在 Komari 后台节点详情中查看 |
| `umami_domain` / `umami_id` | Umami 统计域名与 Website ID |
| `up_date` | 站点上线日期，用于页脚「已在西洲停留 N 个昼夜」 |
| `fontSize_*` | 名字/简介/一言字号与整页缩放 |

## 外部依赖与数据来源

| 功能 | 接口 | 是否需 Key |
| --- | --- | --- |
| IP 定位 | `restapi.amap.com/v3/ip` | 需要 `amapKey` |
| 城市查询 / 实时天气 | `geoapi.qweather.com`、`devapi.qweather.com` | 需要 `qweatherKey` |
| 服务器状态 / 续费倒计时 | Komari `/api/recent/<uuid>`、`/api/nodes` | 不需要（需配 CORS） |
| GitHub 贡献图 | `github-contributions-api.jogruber.de/v4/<user>?y=last` | 不需要 |
| 贡献图备用图片 | `ghchart.rshah.org/<user>` | 不需要 |
| 一言 | `v1.hitokoto.cn` | 不需要 |
| 说说 | 自建 Ech0 实例 | 视部署而定 |
| 访问统计 | Umami / Busuanzi | Umami 需 Website ID |

## Komari 接入说明

Komari 的 CORS 中间件**只对 `/api` 开头的路径生效**，因此：

1. 前端代码会自动补 `/api` 前缀，`config.js` 里的 `komariApiUrl` 只写站点根地址即可
2. 在 Komari 后台设置 `cors_allowed_origins`（逗号分隔），填入**完整 origin**，例如：
   ```
   https://lris625.top,https://www.lris625.top
   ```
   注意不要带结尾斜杠、不要带路径；`http://127.0.0.1:8000` 这类本地调试地址需要单独加进去
3. 未配置或配置错误时，状态面板显示 `--`，控制台会打印 CORS 提示
4. 访问探针域名若走了代理（Clash/TUN 等），可能出现连接被本地接管的情况，排查时先关闭代理

## 常见问题

- **状态栏全部是 `--`，控制台报 `blocked by CORS policy`**：`cors_allowed_origins` 没加当前访问域名，或写成了 `lris625.top` 这种不带协议的形式。
- **贡献图不更新**：数据缓存 2 小时。要强制刷新，在控制台执行
  `localStorage.removeItem('gh_contrib_v1_<你的GitHub用户名>')` 后刷新页面。
- **贡献图变成了备用图片**：公共统计接口不可用，已自动回退到 `ghchart.rshah.org`。
- **社交图标点了没反应**：`config.js` 缺少 `github_url` / `email_url`。GitHub 链接会根据 `github_username` 自动回退，邮箱需手动填写。
- **直接用 `file://` 打开页面一片空白**：浏览器禁止本地文件发起跨域请求，请用本地 HTTP 服务预览或直接部署。

## 未来计划
- ✅ 完善 config.js，争取核心代码无外部链接
- ✅ 添加设置页，允许用户自由管理功能分区
- ✅ 字体大小可配置化
- ✅ 接入 Komari 面板实现跨平台系统监控
- ✅ 移除全部 PHP，实现纯静态部署
- 允许前端修改 config.js
- Docker 环境打包

## 补充说明
- 本作品核心代码由AI开发完成，我可能无力处理部分问题
- 2026.10 起项目已完全移除 PHP（`status.php`、`komari_proxy.php`、`github_contributions.php`），任何静态托管平台都能直接运行
- GitHub 贡献图在浏览器内绘制，接口异常时自动回退到备用图片

## 参考资料
- [無名の主页](https://www.imsyy.top/)(获取功能灵感)
- 一言文档
- Ech0API文档
- 和风天气和高德文档
- Komari 监控面板
