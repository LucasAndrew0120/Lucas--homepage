![首页截图](https://pic1.imgdb.cn/item/698809b442b1cbeca1faa8cf.png)

<p align="center">
  Lucas的忘忧斋——现代化的静态个人主页
</p>

## 功能介绍
- 基础的个人信息（昵称 签名 社交矩阵）
- 集成和风天气和高德地图API，展示用户所在地天气
- 接入Busuanzi和Umami访客统计（Busuanzi已使用国内接口）
- 接入一言CDN
- 接入Ech0说说
- 服务器状态监测
- 快捷卡片，服务分流
- Github贡献图
- 自定义页脚（Shield徽章 签名 网站上线时间等等）

## 目录结构

```
------homepage
        ¦-------index.html ##主页面代码
		¦-------status.php ##服务器状态监测，需要PHP环境
		¦-------config.js ##个人配置（各项图片，APItoken，快捷卡片）
		¦-------github_contributions.php ##Github贡献图生成
		¦-------404.html ##404页面
```


## 部署办法
- fork本项目
- 进入**example.js**,根据注释填入必要数据
- 将**example.js**,重命名为**config.js**
- 将上文中五个文件放入任意支持静态网页部署的平台(Github Page,Cloudflare Page,Vercel,Server-OpenResty,EdgeOne Page)

## 未来计划
✅ 完善config.js，争取核心代码无外部链接

✅ 添加设置页，允许用户自由管理功能分区

❌ 允许前端修改config.js
 
❌ Docker环境打包

## 补充说明
- 本作品核心代码由AI开发完成，我可能无力处理部分问题
- 若无PHP环境，Github贡献图将切换备用方案

## 当前Bug
- 

## 参考资料
- 一言文档
- Ech0API文档
- 和风天气和高德文档







