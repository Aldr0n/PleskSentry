# 🚨 Plesk Sentry
Small, lightweight PHP based tool to keep your Plesk server safe from pesky bruteforcers by scanning logfiles and detecting suspicious activities. Aiming to be bit more flexible than fail2ban.

## What it does
- 📝 Scans Plesk server logfiles for violations
- 🤖 Can be scheduled via cron for regular scanns
- 📊 Provides logging/reports via cron notification
- 🚷 Detects and bans bruteforce attempts in a configurable manner

## Requirements
- PHP >= 8.2
- A Plesk server (obviously 😉)
- That's it!

## Setup Guide
1. Clone the repository to your server (location of your choice):
   ```bash
   git clone [https://github.com/your-username/plesk-sentry.git](https://github.com/Aldr0n/PleskSentry.git)
   cd plesk-sentry
   ```

2. Set proper permissions:
   ```bash
   chmod +x scripts/*.php
   chmod +x scripts/*.sh
   ```

3. Configure the scan trigger:
   ```bash
   # Add cron job via plesk ui (here it runs every 10 minutes)
   Crontab: */10 * * * *
   Arguments: --log=/var/log/syslog (example)
   Notify: Every time (optional, set notification to "Every time" to get reports of banned ips)
   ```

## Future Ideas
- 📧 Custom Email notifications/reports for critical events
- 🌐 API integration for external monitoring
- 🧮 More flexible and configurable rulesets/presets
- 🚀 Webhooks

## License
MIT - Feel free to use it, modify it, share it!

## Contributing
Found a bug? Want to add a feature? PRs are welcome! 🎉

---
Made with ❤️ for safer servers everywhere
