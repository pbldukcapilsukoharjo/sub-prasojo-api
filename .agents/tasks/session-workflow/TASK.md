---
name: session-workflow
description: Main workflow for notebook, plan, and logs
---

Workflow:
1. If prompt requires analysis:
   - Run analyst

2. If prompt requires execution/planning:
   - Run planner

3. Always:
   - Run logger

Generated files:
- output/[timestamp]notebook.md
- output/[timestamp]plan.md
- output/[timestamp]log.md