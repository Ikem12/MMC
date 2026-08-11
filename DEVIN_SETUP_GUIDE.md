# 🤖 Devin Installation & Setup Guide
## AEP Legal Platform — Getting Started on Your Device

---

## ✅ STEP 1 — Complete the Devin Installation

1. On the **"Select Additional Tasks"** screen, check ALL boxes:
   - ☑️ Create a desktop icon
   - ☑️ Add "Open with Devin" action to Windows Explorer **file** context menu
   - ☑️ Add "Open with Devin" action to Windows Explorer **directory** context menu
   - ☑️ Register Devin as an editor for supported file types
   - ☑️ Add to PATH (requires shell restart)

2. Click **"Next"**
3. Click **"Install"**
4. Click **"Finish"**
5. **Restart your computer** *(required for PATH to work)*

---

## ✅ STEP 2 — Install XAMPP (PHP Engine)

Your AEP Legal Platform is built in **PHP** — you need XAMPP to run it.

1. Go to 👉 https://www.apachefriends.org
2. Click **"Download XAMPP for Windows"**
3. Run the installer
4. Install to default location: `C:\xampp`
5. When asked which components — keep defaults:
   - ☑️ Apache
   - ☑️ PHP
   - ☑️ SQLite (or MySQL)
6. Click **Next** → **Install** → **Finish**

---

## ✅ STEP 3 — Download Your AEP Legal Platform from GitHub

1. Go to 👉 https://github.com/Ikem12/MMC
2. Click the green **"<> Code"** button
3. Click **"Download ZIP"**
4. Save the ZIP to your **Downloads** folder
5. Right-click the ZIP → **"Extract All"**
6. Extract to: `C:\xampp\htdocs\MMC`

---

## ✅ STEP 4 — Open the Project in Devin

1. Go to `C:\xampp\htdocs\MMC`
2. **Right-click** the MMC folder
3. Click **"Open with Devin"**
4. Your full AEP Legal Platform opens in Devin! ✅

---

## ✅ STEP 5 — Start Apache (PHP Server)

1. Open **XAMPP Control Panel** (from desktop or Start Menu)
2. Click **"Start"** next to **Apache**
3. The status turns **green** ✅

---

## ✅ STEP 6 — Set Up the Database

1. Open your browser (Chrome/Edge)
2. Go to:
```
http://localhost/MMC/setup_db.php
```
3. This creates ALL your database tables automatically
4. You should see: **"Database setup complete!"** ✅

---

## ✅ STEP 7 — Login to Your Platform

1. Go to:
```
http://localhost/MMC/login.php
```
2. Register a new account
3. Login
4. You are now on the **AEP Legal Platform Dashboard!** 🎉

---

## 🔧 Troubleshooting

| Problem | Solution |
|---|---|
| Apache won't start | Check port 80 is free. Change port to 8080 in XAMPP |
| Page not found | Make sure files are in `C:\xampp\htdocs\MMC` |
| Database error | Run `setup_db.php` first before using the platform |
| PHP error | Make sure XAMPP Apache is running |
| Devin not opening | Restart computer after installation |

---

## 📁 Your Platform URLs (after setup)

| Page | URL |
|---|---|
| Login | http://localhost/MMC/login.php |
| Dashboard | http://localhost/MMC/dashboard.php |
| Immigration | http://localhost/MMC/immigration_list.php |
| Criminal Law | http://localhost/MMC/criminal_list.php |
| Tort Law | http://localhost/MMC/tort_list.php |
| Oil & Gas | http://localhost/MMC/oil_gas_list.php |
| Company Law | http://localhost/MMC/company_list.php |
| Admin Law | http://localhost/MMC/admin_law_list.php |

---

## 🆓 Free Tools Used

| Tool | Purpose | Download |
|---|---|---|
| **Devin** | AI coding assistant | Already installing |
| **XAMPP** | PHP server engine | https://www.apachefriends.org |
| **GitHub** | File storage & backup | https://github.com/Ikem12/MMC |
| **Codeium** | Free AI code helper | https://codeium.com |

---

## 🎉 You're All Set!

Your **AEP Legal Platform** is a fully working legal case management system.
All files are safely stored at: **https://github.com/Ikem12/MMC**

> Built with ❤️ by AEP Legal Consultancy
