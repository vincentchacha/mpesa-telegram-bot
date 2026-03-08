# ![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue) M-Pesa Telegram Bot ![License](https://img.shields.io/badge/License-MIT-green)

A Telegram bot for initiating **Lipa Na M-Pesa (STK Push)** payments via Telegram. Built with **PHP**, **Guzzle**, and **cURL**, this bot provides a secure and easy payment experience directly in Telegram.

---

## Table of Contents

1. [Features](#features)
2. [Architecture](#architecture)
3. [Demo](#demo)
4. [Requirements](#requirements)
5. [Setup](#setup)
6. [Usage](#usage)
7. [Sandbox vs Live](#sandbox-vs-live)
8. [Security](#security)
9. [Contributing](#contributing)
10. [Support & Contact](#support--contact)
11. [License](#license)

---

## Features

- 💳 Initiate **M-Pesa STK Push** payments directly from Telegram
- 📱 Accepts multiple phone formats (`+2547XXXXXXXX`, `2547XXXXXXXX`, `07XXXXXXXX`)
- 🔄 Automatic phone number normalization
- 🛡️ Input validation and sanitization
- 🤖 Telegram keyboard commands (`Help`, `About`)
- ⚡ Quick status messages during transactions
- 📂 Modular and scalable PHP structure

---

## Architecture

**Tech Stack:**

- PHP 7.4+
- Guzzle HTTP client for Telegram API
- cURL for Safaricom M-Pesa API
- Telegram Webhooks for real-time updates
- Environment variables for storing credentials

**Flow:**

1. User sends a message (`pay 0712345678 50`)
2. Telegram forwards message via webhook
3. Bot validates input and formats phone number
4. Bot sends STK Push request to Safaricom API
5. Bot responds to user with transaction status

---

## Demo

🤖 **Try the live bot:** [t.me/TelePesaBot](https://t.me/TelePesaBot)


Example command in Telegram:

```
pay 0712345678 50
```

Response:

```
⏳ Initiating M-Pesa STK Push...
✅ STK Push sent. Check your phone and enter your M-Pesa PIN.
```

---

## Requirements

- PHP 7.4+
- Composer
- HTTPS-enabled server
- Telegram Bot Token
- Safaricom Daraja API credentials

---

## Setup

### 1. Register a Telegram Bot

Open Telegram and contact **BotFather**:

```
/newbot
```

- Choose a **name** and **username** ending with `Bot`
- Copy the **API token**

---

### 2. Configure Project

Clone or upload the project:

```
/mpesa-telegram-bot
├── index.php
├── botpesa.php
├── vendor/
└── composer.json
```

Set your credentials in `.env` (or as server environment variables):

```env
consumer_key=YOUR_CONSUMER_KEY
consumer_secret=YOUR_CONSUMER_SECRET
application_status=sandbox   # or live
```

Update `index.php` with your Telegram token:

```php
$apiKey = 'YOUR_TELEGRAM_BOT_TOKEN';
```

---

### 3. Upload to HTTPS Server

Deploy to a server with a valid SSL certificate, for example:

```
https://yourdomain.com/index.php
```

---

### 4. Register Webhook

```
https://api.telegram.org/botYOUR_TOKEN/setWebhook?url=https://yourdomain.com/index.php
```

Verify the webhook:

```
https://api.telegram.org/botYOUR_TOKEN/getWebhookInfo
```

---

## Usage

### Commands

| Command                | Description                |
|------------------------|----------------------------|
| `/start`               | Show welcome message       |
| `Help`                 | Display usage instructions |
| `About`                | Show bot description       |
| `pay <phone> <amount>` | Initiate M-Pesa payment    |

**Accepted phone formats:** `+254712345678`, `254712345678`, `0712345678`

All formats are automatically normalized to `2547XXXXXXXX`.

### Example

```
pay 0712345678 50
```

Bot responds:

```
⏳ Initiating M-Pesa STK Push...
✅ STK Push sent. Check your phone and enter your M-Pesa PIN.
```

---

## Sandbox vs Live

- **Sandbox** is the default mode for testing — responses may occasionally be delayed or fail.
- For production, request Live Daraja API credentials from Safaricom and update your config:

```env
application_status=live
```

---

## Security

- **Never** share your Telegram bot token or M-Pesa credentials publicly
- All inputs are sanitized to prevent errors or misuse
- HTTPS is required for Telegram webhooks
- Recommended: log all transactions and callbacks securely

---

## Contributing

1. Fork the repository
2. Create a new branch for your feature or fix
3. Submit a pull request with a clear description

---

## Support & Contact

For custom bot development for business, e-commerce, education, or automation:

- 📧 Email: [vinukoech@gmail.com](mailto:vinukoech@gmail.com)

---

## License

This project is licensed under the [MIT License](LICENSE).
