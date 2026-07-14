![首页截图](https://pic1.imgdb.cn/item/698809b442b1cbeca1faa8cf.png)

<h2 align="center">
  Lucas的忘忧斋——现代化的静态个人主页
</h2>

## 功能介绍
- ☑️基础的个人信息（昵称 签名 社交矩阵）
- ☑️集成和风天气和高德地图API，展示用户所在地天气
- ☑️接入Busuanzi和Umami访客统计（Busuanzi已使用国内接口）
- ☑️接入一言CDN（支持来源出处显示）
- ☑️接入Ech0说说
- ☑️服务器状态监测（支持 Komari 面板接入或本地 status.php）
- ☑️快捷卡片，服务分流
- ☑️Github贡献图
- ☑️自定义页脚（Shield徽章 签名 网站上线时间等等）
- ☑️看板布局管理（一言/说说/监控/快捷入口/GitHub图 自由开关）
- ☑️API 调试日志（浏览器控制台查看各接口获取状态）
- ☑️字体大小可配置（名字/简介/一言/页面缩放）

## 目录结构

```
------homepage/
  ¦-------index.html
  ¦-------status.php
  ¦-------config.example.js
  ¦-------github_contributions.php
  ¦-------404.html
```


## 部署办法
- fork 本项目
- 进入 **config.example.js**，根据注释填入必要数据
- 将 **config.example.js** 重命名为 **config.js**
- 将上文中五个文件放入任意支持静态网页部署的平台（Github Page、Cloudflare Page、Vercel、Server-OpenResty、EdgeOne Page）

## 未来计划
- ✅ 完善 config.js，争取核心代码无外部链接
- ✅ 添加设置页，允许用户自由管理功能分区
- ✅ 字体大小可配置化
- ✅ 接入 Komari 面板实现跨平台系统监控
- 允许前端修改 config.js
- Docker 环境打包

## 补充说明
- 本作品核心代码由AI开发完成，我可能无力处理部分问题
- 若无PHP环境，Github贡献图将切换备用方案

## 参考资料
- [無名の主页](https://www.imsyy.top/)(获取功能灵感)
- 一言文档
- Ech0API文档
- 和风天气和高德文档