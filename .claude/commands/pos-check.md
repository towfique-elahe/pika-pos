---
description: Run the full Pika POS verification loop — lint, static analysis, build and a load check
allowed-tools: Bash(npm run:*), Bash(composer run:*), Bash(studio wp:*), Read
---

Verify the current state of the plugin. Run every step even if an early one
fails, then report the whole picture at once.

1. `composer run lint`
2. `composer run analyse`
3. `npm run lint:js`
4. `npm run build`
5. `cd /Users/towfiqueelahe/Studio/icby-polki && studio wp plugin list --status=active --format=csv`
6. `cd /Users/towfiqueelahe/Studio/icby-polki && studio wp eval 'echo defined( "PIKA_POS_VERSION" ) ? PIKA_POS_VERSION : "NOT LOADED";'`

If the plugin has tests, also run `composer run test` and `npm run test:e2e`.

Report: what passed, what failed with the actual error output, and the single
next action for each failure. Do not fix anything unless asked — this command
reports.
