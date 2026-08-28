---
description: Show recent WordPress and PHP errors for this site
allowed-tools: Bash(studio wp:*), Bash(tail:*), Bash(cat:*), Read
---

Show what the site has been complaining about.

```bash
tail -80 /Users/towfiqueelahe/Studio/icby-polki/wp-content/debug.log
```

If the file does not exist, enable logging first:

```bash
cd /Users/towfiqueelahe/Studio/icby-polki && studio config set --debug-log
```

Filter for anything mentioning `pika` or `pika-pos` and summarise: what is
erroring, where, and whether it is ours or another plugin's. Ignore deprecation
notices from WooCommerce or core unless they name a Pika POS file.
