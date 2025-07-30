# FontStory
一个字体展示和管理平台，支持字体在线预览、分包上传、后台管理等功能。

## 界面预览

![](ui_screenshots/1_FontStory.png)
![](ui_screenshots/2_FontStory.png)
![](ui_screenshots/3_FontStory.png)
![](ui_screenshots/4_FontStory.png)
![](ui_screenshots/5_FontStory.png)
![](ui_screenshots/6_FontStory.png)
![](ui_screenshots/7_FontStory.png)

## 特性
- 🎨 支持字体在线预览和动态调整大小
- 📱 响应式设计，完美适配移动端
- 🌓 深色/浅色主题无缝切换
- ⚡️ 字体分包加载，极速浏览体验
- 🔒 完整的后台管理系统
- 📊 详细的系统日志记录
- 🛠 SEO 优化设置支持

## 安装部署

### 系统要求
- PHP 7.4+
- SQLite 3
- Nginx 1.22+ / Apache
- 写入权限：database/, fonts/, logs/

### 推荐环境
- 宝塔面板
- PHP 7.4.33
- Nginx 1.22.1

### 安装步骤
1. 上传文件到网站目录

2. 设置目录权限
```bash
chmod -R 755 .
chmod -R 777 database fonts logs
```

3. 初始化数据库
- 访问 admin/init_db.php 进行初始化、

4. 登录后台
- 地址：admin/login.php
- 默认账号：admin
- 默认密码：admin123

5. 上传字体文件
- 支持 .ttf, .otf, .woff, .woff2 格式
- 建议文件大小不超过 10MB

## 使用说明

### 字体管理
- 支持批量上传字体文件
- 自动生成字体预览
- 支持字体分类管理
- 支持字体搜索功能

### 主题切换
- 支持深色/浅色主题
- 主题设置自动保存
- 响应系统主题偏好

### 性能优化
- 字体文件分包加载
- 图片懒加载
- CSS/JS 压缩
- 浏览器缓存优化

## 技术栈
- **前端**：HTML5, CSS3, JavaScript (ES6+)
- **后端**：PHP 7.4+
- **数据库**：SQLite 3
- **服务器**：Nginx/Apache

## 许可证
MIT License