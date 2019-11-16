#!/bin/bash
while true; do
  sudo php bin/console qpost:push-notifications-worker
done
