# License Watch gateway

`/license` proxies a private Node/Playwright worker. The existing portfolio stays static. The gateway returns a clear 503 setup page until connected; it does not pretend that monitoring is running.

Namecheap shared hosting advertises Node.js, but its AUP prohibits standalone unattended daemons and cron intervals under five minutes. The user confirmed on 21 October 2026 that this worker is allowed on the account. Chromium compatibility, the private PHP gateway and duplicate-worker supervision were tested successfully on that host. The five-minute restart watchdog is installed. A same-machine worker can use the loopback gateway origin `http://127.0.0.1:47831`; an external worker needs HTTPS.

1. Deploy the checker worker package to an approved always-on host. Keep its source and persistent `data` outside `public_html`. The package includes Docker and native systemd examples.
2. For an external worker, give it an HTTPS endpoint. The installed same-host worker uses the private loopback origin. Set `LICENSE_PUBLIC_URL=https://robrechtvinckier.com/license/` and a random `LICENSE_GATEWAY_KEY` of at least 32 characters. Never put the gateway key in the URL.
3. Copy `license-gateway.example.php` to `/home/robrskoh/license-gateway.php`, outside this repo and `public_html`. Set the worker origin and the same gateway secret. The installed origin is `http://127.0.0.1:47831`. Restrict the file to your hosting account.
4. Deploy this repo's `license/` directory to `public_html/license/` through the existing Git deployment. PHP 8 with cURL and rewrite support is required. The backend source/data are deliberately absent from this repo.
5. Visit `/license/`; sign in with the configured username/password. Defaults are `123` / `123` as requested. The owner explicitly approved publishing those credentials. They can be changed through the private worker environment.
6. Export a full JSON backup from the laptop checker; import it on the worker while paused. An import never carries itsme cookies, bot tokens, running state or auto-book authorization. Sign into itsme anew using Browser assistance. Enable the schedule or Run → Until stopped afterwards.
7. Use Connections & data to enter the bot token privately and send a test DM. The destination defaults to the user's Discord ID, `436225343850807319`. The phone must allow notifications for this bot DM.

No live credentials, cookies or candidate data belong in this repository. The private gateway config and worker volume survive public-site updates because they live outside the checked-out/public directories. When redeploying the portfolio, preserve existing unrelated files and `.well-known`; don't blindly delete the hosting account's contents.

Official references:
- https://www.namecheap.com/legal/hosting/aup/
- https://www.namecheap.com/support/knowledgebase/article.aspx/10047/2182/how-to-work-with-nodejs-app/
- https://docs.discord.com/developers/resources/user#create-dm
