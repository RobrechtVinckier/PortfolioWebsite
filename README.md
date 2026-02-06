# PortfolioWebsite

## Manual deploy command (run on cPanel SSH shell)

```bash
cd /home/robrskoh/portfolio && git fetch origin && git checkout production && git reset --hard origin/production && rsync -av --delete --exclude '.well-known' --exclude '.git' /home/robrskoh/portfolio/ /home/robrskoh/public_html/
```
