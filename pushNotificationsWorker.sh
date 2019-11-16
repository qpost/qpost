#!/bin/bash
while true; do
  php bin/console qpost:push-notifications-worker
done
