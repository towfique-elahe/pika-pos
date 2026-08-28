---
description: Add a new Pika POS REST endpoint following the plugin's conventions
argument-hint: <route-name> <what it should do>
allowed-tools: Read, Edit, Write, Grep, Glob, Bash(npm run:*), Bash(studio wp:*)
---

Add a REST endpoint for: $ARGUMENTS

If controllers already exist under `includes/rest/`, read one first and mirror
it rather than inventing a second shape.

1. Create `includes/rest/class-rest-{name}-controller.php` extending
   `WP_REST_Controller` (or the plugin's own base controller once one exists),
   in the `pika-pos/v1` namespace.
2. Require and register it wherever `rest_api_init` is wired up.
3. Every route gets a `permission_callback` that checks a real capability. Never
   `__return_true` — these routes touch customer records and money.
4. Every argument gets a `type`, a `description` and a `sanitize_callback`.
   Undeclared args are not validated at all.
5. Business logic goes in a domain class under `includes/`, not in the
   controller. Anything that writes a sale goes through the single
   order-creating class.
6. Add the client-side call to the module that owns `apiFetch`, not to a
   component.

Then verify: `composer run lint`, `composer run analyse`, and exercise the route
with `studio wp eval` or the Playwright MCP server. Report the route, its
permission model and how you tested it.
