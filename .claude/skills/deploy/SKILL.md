---
name: deploy
description: Deploy to DreamHost server with ./deploy_to_dh.sh (git push + ssh pull)
disable-model-invocation: false
allowed-tools: Bash
---

Deploy the current branch to the DreamHost server.

Run `./deploy_to_dh.sh` from the repo root. It pushes the current branch to
origin (setting upstream) and pulls it on the server over ssh.
