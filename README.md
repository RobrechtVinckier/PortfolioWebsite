# PortfolioWebsite

## Manual deploy command (run on cPanel SSH shell)

```bash
cd /home/robrskoh/portfolio && git checkout production && git pull && rsync -av --delete --exclude '.well-known' --exclude '.git' /home/robrskoh/portfolio/ /home/robrskoh/public_html/
```
