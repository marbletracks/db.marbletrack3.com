---
name: deploy
description: Deploy to DreamHost server via git push + ssh pull
disable-model-invocation: false
allowed-tools: Bash
---

Deploy the current branch to the DreamHost server.

Steps:
1. Push the current branch to origin (set upstream if needed)
2. Deploy: `ssh db_mt3_deploy $BRANCH`

Where `$BRANCH` is the current git branch name.
